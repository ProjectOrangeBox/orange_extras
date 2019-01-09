<?php
/**

CREATE TABLE `simple_q` (
  `created` datetime NOT NULL DEFAULT current_timestamp(),
  `updated` datetime DEFAULT NULL,
  `handler` char(32) CHARACTER SET latin1 NOT NULL DEFAULT '',
  `status` tinyint(3) unsigned NOT NULL DEFAULT 0,
  `token` char(40) CHARACTER SET latin1 DEFAULT NULL,
  `payload` longblob NOT NULL,
  KEY `idx_token` (`token`) USING BTREE,
  KEY `idx_status` (`status`) USING BTREE,
  KEY `idx_updated` (`updated`) USING BTREE,
  KEY `idx_handler` (`handler`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8

 */
class Simple_q extends CI_Model {
	protected $table = 'simple_q';
	protected $status = ['new'=>10,'tagged'=>20,'processed'=>30,'error'=>40];
	protected $db;
	protected $clean_up_days;
	protected $retag_hours;
	protected $token_length;
	protected $token_hash;
	protected $default_handler = false;

	public function __construct()
	{
		$config = config('simple_q');

		$this->clean_up_days = (!isset($config['clean up days'])) ? 	7 : (int)$config['clean up days'];
		$this->retag_hours = (!isset($config['requeue tagged hours'])) ? 	1 : (int)$config['requeue tagged hours'];

		$this->token_length = (!isset($config['token length'])) ? 	40 : (int)$config['token length'];
		$this->token_hash = (!isset($config['token hash'])) ? 	'sha1' : $config['token hash'];

		$database_group = (!isset($config['database group'])) ? 	'default' : $config['database group'];

		$this->db = $this->load->database($database_group, true);

		$this->cleanup();
	}

	public function handler($handler)
	{
		$this->default_handler = $handler;

		return $this;
	}

	public function add($data,$handler=null)
	{
		return $this->db->insert($this->table,['created'=>date('Y-m-d H:i:s'),'status'=>$this->status['new'],'payload'=>$this->encode($data),'handler'=>$this->get_handler($handler),'token'=>null]);
	}

	public function next($handler=null)
	{
		$token = hash($this->token_hash,uniqid('',true));

		$this->db->set(['token'=>$token,'status'=>$this->status['tagged'],'updated'=>date('Y-m-d H:i:s')])->where(['token is null'=>null,'handler'=>$this->get_handler($handler)])->limit(1)->update($this->table);

		if ($success = (bool)$this->db->affected_rows()) {
			$dbr = $this->db->limit(1)->where(['token'=>$token])->get($this->table)->row();

			$dbr->payload = $this->decode($dbr);

			$success = $dbr;
		}

		return $success;
	}

	public function processed($record)
	{
		return $this->change_status($record,'processed');
	}

	public function requeue($record)
	{
		return $this->change_status($record,'new',true);
	}

	public function error($record)
	{
		return $this->change_status($record,'error');
	}

	public function cleanup()
	{
		if ($this->retag_hours > 0) {
			$this->db->set(['token'=>null,'status'=>$this->status['new'],'updated'=>date('Y-m-d H:i:s')])->where(['updated < now() - interval '.(int)$this->retag_hours.' hour'=>null,'status'=>$this->status['tagged']])->update($this->table);
		}

		if ($this->clean_up_days > 0) {
			$this->db->where(['updated < now() - interval '.(int)$this->clean_up_days.' day'=>null,'status'=>$this->status['processed']])->delete($this->table);
		}

		return $this;
	}

	/* protected */

	protected function change_status($record,$status,$clean_token=false)
	{
		$data = ['status'=>$this->status[$status],'updated'=>date('Y-m-d H:i:s')];

		if ($clean_token) {
			$data['token'] = null;
		}

		$this->db->limit(1)->update($this->table,$data,['token'=>$this->get_token($record)]);

		return $this;
	}

	protected function get_token($record)
	{
		if (is_array($record)) {
			if (isset($record['token'])) {
				return $record['token'];
			}
		}

		if (is_object($record)) {
			if (!empty($record->token)) {
				return $record->token;
			}
		}

		if (is_string($record)) {
			if (strlen($record) == $this->token_length) {
				return $record;
			}
		}

		throw new Exception('Could not get Simple Q token.');
	}

	protected function encode($data)
	{
		$payload = new stdClass;

		if (is_object($data)) {
			$payload->type = 'object';
		} elseif(is_scalar($data)) {
			$payload->type = 'scalar';
		} elseif(is_array($data)) {
			$payload->type = 'array';
		}	else {
			throw new Exception('Could not encode Simple Q data.');
		}

		$payload->data = $data;
		$payload->checksum = $this->create_checksum($data);

		return json_encode($payload,JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_UNESCAPED_UNICODE);
	}

	protected function decode($record)
	{
		$payload_record = json_decode($record->payload,false);

		switch ($payload_record->type) {
			case 'object':
				$data = $payload_record->data;
			break;
			case 'array':
				$data = (array)$payload_record->data;
			break;
			case 'scalar':
				$data = $payload_record->data;
			break;
			default:
				throw new Exception('Could not determine Simple Q data type.');
		}

		if (!$this->check_checksum($payload_record->checksum,$data)) {
			throw new Exception('Simple Q data checksum failed.');
		}

		return $data;
	}

	protected function create_checksum($payload)
	{
		return md5(json_encode($payload,JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_UNESCAPED_UNICODE));
	}

	protected function check_checksum($checksum,$payload)
	{
		return ($this->create_checksum($payload) == $checksum);
	}

	protected function get_handler($handler)
	{
		if ($handler === null) {

			if (!$this->default_handler) {
				throw new Exception('Simple Q default handler not set.');
			}

			$handler = $this->default_handler;
		}

		return md5($handler);
	}

} /* end class */

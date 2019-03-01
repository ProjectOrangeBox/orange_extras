<?php
/**

CREATE TABLE `simple_q` (
	`created` datetime NOT NULL DEFAULT current_timestamp(),
	`updated` datetime DEFAULT NULL,
	`queue` char(32) CHARACTER SET latin1 NOT NULL DEFAULT '',
	`status` tinyint(3) unsigned NOT NULL DEFAULT 0,
	`token` char(40) CHARACTER SET latin1 DEFAULT NULL,
	`payload` longblob NOT NULL,
	KEY `idx_token` (`token`) USING BTREE,
	KEY `idx_status` (`status`) USING BTREE,
	KEY `idx_updated` (`updated`) USING BTREE,
	KEY `idx_queue` (`queue`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8

 */
class Simple_q extends CI_Model
{
	protected $table = 'simple_q';
	protected $status_map = ['new'=>10,'tagged'=>20,'processed'=>30,'error'=>40];
	protected $status_map_flipped;
	protected $db;
	protected $clean_up_hours;
	protected $retag_hours;
	protected $token_hash;
	protected $token_length;
	protected $default_queue = false;
	protected $json_options = JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_UNESCAPED_UNICODE | JSON_NUMERIC_CHECK | JSON_PRESERVE_ZERO_FRACTION;

	public function __construct()
	{
		include 'Simple_q_record.php';

		$this->status_map_flipped = array_flip($this->status_map);

		$config = config('simple_q');

		$this->clean_up_hours = (!isset($config['clean up hours'])) ? 	168 : (int)$config['clean up hours']; /* 7 days */
		$this->retag_hours = (!isset($config['requeue tagged hours'])) ? 	1 : (int)$config['requeue tagged hours']; /* 1 hour */

		$this->token_hash = (!isset($config['token hash'])) ? 	'sha1' : $config['token hash']; /* sha1 */
		$this->token_length = (!isset($config['token length'])) ? 	40 : (int)$config['token length']; /* sha1 length */

		$database_group = (!isset($config['database group'])) ? 	'default' : $config['database group'];

		$this->db = $this->load->database($database_group, true);

		$garbage_collection_percent = (!isset($config['garbage collection percent'])) ? 	50 : $config['garbage collection percent'];

		if (mt_rand(0, 99) < $garbage_collection_percent) {
			$this->cleanup();
		}
	}

	public function queue($queue)
	{
		$this->default_queue = $queue;

		return $this;
	}

	public function push($data, $queue=null)
	{
		return $this->db->insert($this->table, ['created'=>date('Y-m-d H:i:s'),'status'=>$this->status_map['new'],'payload'=>$this->encode($data),'queue'=>$this->get_queue($queue),'token'=>null]);
	}

	public function pull($queue=null)
	{
		$token = hash($this->token_hash, uniqid('', true));

		$this->db->set(['token'=>$token,'status'=>$this->status_map['tagged'],'updated'=>date('Y-m-d H:i:s')])->where(['status'=>$this->status_map['new'],'token is null'=>null,'queue'=>$this->get_queue($queue)])->limit(1)->update($this->table);

		if ($success = (bool)$this->db->affected_rows()) {
			$record = $this->db->limit(1)->where(['token'=>$token])->get($this->table)->row();

			$record->status_raw = $record->status;
			$record->status = $this->status_map_flipped[$record->status];
			$record->payload = $this->decode($record);

			$success = new Simple_q_record($record);
		}

		return $success;
	}

	public function cleanup()
	{
		if ($this->retag_hours > 0) {
			$this->db->set(['token'=>null,'status'=>$this->status_map['new'],'updated'=>date('Y-m-d H:i:s')])->where(['updated < now() - interval '.(int)$this->retag_hours.' hour'=>null,'status'=>$this->status_map['tagged']])->update($this->table);
		}

		if ($this->clean_up_hours > 0) {
			$this->db->where(['updated < now() - interval '.(int)$this->clean_up_hours.' hour'=>null,'status'=>$this->status_map['processed']])->delete($this->table);
		}

		return $this;
	}

	/* internally used by simple q record */
	public function update($token, $status)
	{
		if (!array_key_exists($status, $this->status_map)) {
			throw new \Exception('Unknown Simple Q record status "'.$status.'".');
		}

		return $this->db->limit(1)->update($this->table, ['token'=>null,'updated'=>date('Y-m-d H:i:s'),'status'=>$this->status_map[$status]], ['token'=>$token]);
	}

	/* protected */

	protected function encode($data)
	{
		$payload = new stdClass;

		if (is_object($data)) {
			$payload->type = 'object';
		} elseif (is_scalar($data)) {
			$payload->type = 'scalar';
		} elseif (is_array($data)) {
			$payload->type = 'array';
		} else {
			throw new \Exception('Could not encode Simple Q data.');
		}

		$payload->data = $data;
		$payload->checksum = $this->create_checksum($data);

		return json_encode($payload, $this->json_options);
	}

	protected function decode($record)
	{
		$payload_record = json_decode($record->payload, false);

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
				throw new \Exception('Could not determine Simple Q data type.');
		}

		if (!$this->check_checksum($payload_record->checksum, $data)) {
			throw new \Exception('Simple Q data checksum failed.');
		}

		return $data;
	}

	protected function create_checksum($payload)
	{
		return crc32(json_encode($payload, $this->json_options));
	}

	protected function check_checksum($checksum, $payload)
	{
		return ($this->create_checksum($payload) == $checksum);
	}

	protected function get_queue($queue)
	{
		if ($queue === null) {
			if (!$this->default_queue) {
				throw new \Exception('Simple Q default queue not set.');
			}

			$queue = $this->default_queue;
		}

		return md5($queue);
	}
} /* end class */

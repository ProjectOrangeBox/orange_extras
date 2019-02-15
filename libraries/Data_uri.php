<?php

class Data_uri
{
	public $public_root;
	public $public_folder = '';
	public $just_path = false;
	public $replace_files = false;
	public $md5_filename = false;
	public $image_file_types = ['png'=>'png','jpg'=>'jpg','jpeg'=>'jpg','gif'=>'gif'];
	public $file_types = [];

	public function __construct()
	{
		$this->public_root = site_url('{www}');
	}

	public function public_folder($public_folder)
	{
		$this->public_folder = trim($public_folder);

		$dir = $this->get_absolute_path();

		/* make public folder if it doesn't exist */
		if (!is_dir($dir)) {
			mkdir($dir, 0777, true);
		}

		return $this;
	}

	public function get_absolute_path()
	{
		return rtrim($this->public_root, '/').'/'.trim($this->public_folder, '/');
	}

	public function just_path($just_path=true)
	{
		$this->just_path = (bool)$just_path;

		return $this;
	}

	public function replace_files($replace_files=true)
	{
		$this->replace_files = (bool)$replace_files;

		return $this;
	}

	public function md5_filename($md5_filename=true)
	{
		$this->md5_filename = (bool)$md5_filename;

		return $this;
	}

	public function file_types($types_ary=[])
	{
		$this->file_types = $types_ary;

		return $this;
	}

	/* used by the filter */
	public function extract_data_img(&$html, $just_path=null)
	{
		if ($just_path) {
			$this->just_path = $just_path;
		}

		/* have any file types been assigned yet? */
		if (!count($this->file_types)) {
			/* no - let's use the image defaults */
			$this->file_types($this->image_file_types);
		}

		/* <img src="data:image/png;base64,iVBORw0KGgoAAA...AAElFTkSuQmCC" data-filename="command_line_cli.png" style="width: 25%;"> */

		/* extract all the images into a array */
		if (preg_match_all('#<img ([^>]+)>#', $html, $matches)) {
			foreach ($matches[1] as $img) {
				/* get src */
				if (!preg_match('#src="([^"]+)"#', $img, $match)) {
					show_error('could not determine image src');
				}

				$src = $match[1];

				/* get raw base64 */
				$raw = substr($src, strpos($src, ',')+1);

				/* get file type from the data:image signature */
				if (!preg_match('#data:image/([^;]+);#', $src, $match)) {
					/* if we can't find it then maybe it's a regular image src */

					break; /* jump out of this loop */
				}

				if (!array_key_exists($match[1], $this->file_types)) {
					show_error('could not determine file extension');
				}

				$ext = $this->file_types[$match[1]];

				/* get data-filename if present if not make filename a md5 of image */
				if (!preg_match('#data-filename="([^"]+)"#', $img, $match) || $this->md5_filename) {
					$filename = md5($src);
				} else {
					/* get only the filename not the extension we determine that by the data:image sig. */
					$filename = pathinfo($match[1], PATHINFO_FILENAME);
				}

				/* do some basic filename cleaning */
				$filename = $this->clean_filename($filename, $ext);

				/* create our absolute path */
				$absolute_image_path = $this->get_absolute_path().'/'.$filename;

				if (!$this->replace_files) {
					$absolute_image_path = $this->get_next_filename($absolute_image_path, $raw);
				}

				/* write to the file system */
				$ifp = fopen($absolute_image_path, 'wb');
				fwrite($ifp, base64_decode($raw));
				fclose($ifp);

				/* what is the www path? */
				$www_image_path = str_replace($this->public_root, '', $absolute_image_path);

				/* replace the data uri with a file path */
				$html = str_replace($src, $www_image_path, $html);

				/* strip data-filename if it exists */
				$html = preg_replace('# *data-filename="[^"]+" *#', ' ', $html);

				/*
				if they want the path of the newly saved image then return it and bail
				of course this only works if there is only 1 image attached
				*/
				if ($this->just_path) {
					return (preg_match('#src="([^"]+)"#', $html, $match)) ? $match[1] : false;
				}
			}
		}

		return $html;
	}

	public function extract_data_file(&$html, $save_filename=null)
	{
		if (file_exists($this->public_root.$html)) {
			return;
		}

		/* get raw base64 */
		$type = substr($html, 0, strpos($html, ','));

		/* data:application/pdf;base64 */
		list($data, $encoding) = explode(';', $type);

		$data = substr($data, 5);

		$mime = explode('/', $data);

		if (!array_key_exists($mime[1], $this->file_types)) {
			show_error('could not determine file extension');
		}

		$ext = $this->file_types[$mime[1]];

		$raw = substr($html, strpos($html, ',') + 1);

		if (!$save_filename) {
			$save_filename = md5($raw);
		}

		$filename = pathinfo($save_filename, PATHINFO_FILENAME);

		/* do some basic filename cleaning */
		$filename = $this->clean_filename($filename, $ext);

		/* create our absolute path */
		$absolute_image_path = $this->get_absolute_path().'/'.$filename;

		if (!$this->replace_files) {
			$absolute_image_path = $this->get_next_filename($absolute_image_path, $raw);
		}

		/* write to the file system */
		$ifp = fopen($absolute_image_path, 'wb');
		fwrite($ifp, base64_decode($raw));
		fclose($ifp);

		$html = str_replace($this->public_root, '', $absolute_image_path);

		/* what is the www path? */
		return $html;
	}

	public function get_next_filename($filename, $raw)
	{
		/* does this file already exist? */
		if (!file_exists($filename)) {
			/* ok then just return it no other processing needed */
			return $filename;
		}

		/* is this the exact same file already on the system? */
		if (md5(base64_decode($raw)) == md5_file($filename)) {
			/* yes it's the same file */
			return $filename;
		}

		/* it not the same file and the filename already exists get a new filename */
		$parts = pathinfo($filename);

		for ($i = 0; $i < 9999; $i++) {
			$new_filename = $parts['dirname'].'/'.$parts['filename'].'-'.$i.'.'.$parts['extension'];

			/* does this file already exist? */
			if (!file_exists($new_filename)) {
				/* nope! return it ASAP */
				return $new_filename;
			}
		}

		/* fall back */
		return $parts['dirname'].'/'.$parts['filename'].'-'.md5($raw).$parts['extension'];
	}

	public function clean_filename($str, $ext)
	{
		$str = strip_tags($str);
		$str = preg_replace('/[\r\n\t ]+/', ' ', $str);
		$str = preg_replace('/[\"\*\/\:\<\>\?\'\|]+/', ' ', $str);
		$str = strtolower($str);
		$str = html_entity_decode($str, ENT_QUOTES, "utf-8");
		$str = htmlentities($str, ENT_QUOTES, "utf-8");
		$str = preg_replace("/(&)([a-z])([a-z]+;)/i", '$2', $str);
		$str = str_replace(' ', '-', $str);
		$str = rawurlencode($str);
		$str = str_replace('%', '-', $str);

		return $str.'.'.$ext;
	}
} /* end class */

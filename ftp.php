<?php

// Original: http://php-archive.net/php/ftp-transmission/
class FtpTransmission {
	private $conn;

	//Connection process
	function connect($host, $user, $pass, $port=21, $timeout=90){
		try {
			$this->conn = ftp_connect($host, $port, $timeout);
			if(!$this->conn) throw new Exception('FTP CONNECTION FAILED');
			$result = ftp_login($this->conn, $user, $pass);
			if(!$result) throw new Exception('AUTHENTICATION FAILED');
			var_dump($result);
			ftp_pasv($this->conn, true);
		} catch (Exception $e){
			echo $e->getMessage();
			return false;
		}

		return true;
	}

	//Disconnection process
	function close(){
		return ftp_close($this->conn);
	}

	//PASV Mode
	function set_pasv($mode=true){
		return ftp_pasv($this->conn, $mode);
	}

	//Upload
	function upload($file, $to, $mode='auto'){
		if($mode == 'auto') $mode = $this->detect_mode($file);
		$to = (preg_match('/(\\\\|\/)$/', $to)) ? $to . basename($file) : $to . '/' . basename($file);
		$result = ftp_put($this->conn, $to, $file, $mode);
		return $result;
	}

	//Download
	function download($file, $to, $mode='auto'){
		if($mode == 'auto') $mode = $this->detect_mode($file);
		$to = (preg_match('/(\\\\|\/)$/', $to)) ? $to . basename($file) : $to . '/' . basename($file);
		$result = ftp_get($this->conn, $to, $file, $mode, 0);
		return $result;
	}

	//Show File list (Simple)
	function nlist($dir){
		return ftp_nlist($this->conn, $dir);
	}


	//Show File list (With extra information)
	function rawlist($dir, $win=false){
		$list = array();
		$raw = ftp_rawlist($this->conn, $dir);

		$root = $dir;

		if($win){
			foreach($raw as $value){
				$parse = preg_split("/ +/", $value, -1, PREG_SPLIT_NO_EMPTY);

				$date = DateTime::createFromFormat('m-d-y h:iA', $parse[0] . ' ' . $parse[1]);

				$list[] = array(
						'perms'  => null,
						'number' => null,
						'owner'  => null,
						'group'  => null,
						'size'   => ($parse[2] == '<DIR>') ? null : $parse[2],
						'month'  => $date->format('M'),
						'day'    => $date->format('j'),
						'time'   => $date->format('H:i'),
						'name'   => $parse[3],
						'type'   => ($parse[2] == '<DIR>') ? 'directory' : 'file'
				);
			}
		} else {
			foreach($raw as $value){
				$parse = preg_split("/ +/", $value, 9, PREG_SPLIT_NO_EMPTY);

				$list[] = array(
						'perms'  => $parse[0],
						'number' => $parse[1],
						'owner'  => $parse[2],
						'group'  => $parse[3],
						'size'   => (preg_match('/^d/', $parse[0])) ? null : $parse[0],
						'month'  => $parse[5],
						'day'    => $parse[6],
						'time'   => $parse[7],
						'name'   => $parse[8],
						'type'   => (preg_match('/^d/', $parse[0])) ? 'directory' : 'file'
				);
			}
		}
		return $list;
	}

	function current_directory(){
		return ftp_pwd($this->conn);
	}

	//Delete File
	function delete_file($file){
		return ftp_delete($this->conn, $file);
	}

	//Create folder
	function make_directory($dir){
		return ftp_mkdir($this->conn, $dir);
	}

	//Delete folder
	function remove_directory($dir){
		return ftp_rmdir($this->conn, $dir);
	}

	//Detect transfer mode
	function detect_mode($file){
		$pathinfo = pathinfo($file);
		$extension = isset($pathinfo['extension']) ? strtolower($pathinfo['extension']) : '';

		//Extension list transferred in ASCII
		$ascii = array(
				'asp', 'cgi', 'css', 'csv', 'html', 'htm', 'ini', 'js', 'jsp',
				'log', 'php', 'py', 'pl', 'svg', 'tpl', 'txt', 'xml', 'htaccess', ''
		);

		if( in_array($extension, $ascii)){
			return FTP_ASCII;
		} else {
			return FTP_BINARY;
		}
	}
}
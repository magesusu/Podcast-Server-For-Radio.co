<?php

require "settings.php";
require "ftp.php";
require "mp3file.php";

//一時間までの実行を許可
ini_set("max_execution_time",3600);

// //FTPクライアント呼び出し
// $ftp = new FtpTransmission();

// //ファイルのダウンロード
// if($ftp->connect(HOST, USER, PASS)){
// 	$cue = getDownloadList("/",$ftp,SAVEDIR);
// 	var_dump($cue);
// 	foreach ($cue as $from){
// 		$ftp->download($from,SAVEDIR);
// 	}
// 	$ftp->close();
// }

//ファイルからポッドキャスト用リストを作成
static $medialist = [];
if ($dir = opendir(SAVEDIR)) {
	while (($file = readdir($dir)) !== false) {
		if ($file != "." && $file != "..") {
			echo "$file\n";
			//ファイル名からデータをパース
			$local = (preg_match('/(\\\\|\/)$/', SAVEDIR)) ? SAVEDIR . basename($file) : SAVEDIR . '/' . basename($file);
			$names = pathinfo($local);

			//MP3ファイル名から詳細情報を取得
			$mp3file = new MP3File($local);

			$data = [];
			$data['member'] = getDJCode($names["filename"]);

			$data['title'] = (getTitleByDJC($data['member'])) ? getTitleByDJC($data['member']) ." ". preg_replace('/[^0-9]/', '', $names['filename']): $names['filename'];
			$data['filesize'] = filesize($local);
			$data['duration'] = MP3File::formatTime($mp3file -> getDuration(true));
			$data['artist'] = getArtistNameByDJC($data['member']);
			$data['picture'] = getPictureUrlByDJC($data['member']);
			$data['date'] = date(DateTime::RFC822, filemtime($local));
			$medialist[] = $data;
		}
	}
	var_dump($medialist);
	closedir($dir);
}

//xmlとして保存


function getDJCode($title){
	$member = 0;
	if(stripos($title,'steve') !== false){
		$member += 1;
	}
	if(stripos($title,'daisuke') !== false){
		$member += 2;
	}
	if(stripos($title,'tox') !== false){
		$member += 4;
	}
	if(stripos($title,'hiro') !== false){
		$member += 8;
	}
	return $member;
}
function getTitleByDJC($member){
	switch($member){
		case 1:
			return 'Steve Rock ＆ Pop Show';
			break;
		case 2:
			return 'Daisuke Hip Hop Show';
			break;
		case 4:
			return 'Tox Reggae Show';
			break;
		case 8:
			return 'Hiro Chill Out Show';
			break;
		case 3:
			return 'Steve ＆ Daisuke Show';
			break;
		default:
			return false;
	}
}
function getPictureUrlByDJC($member){
	return 'https://i1.wp.com/ssr990.com/wp-content/uploads/2016/12/itunes_logo.jpg?ssl=1';
}
function getArtistNameByDJC($member){
	switch($member){
		case 1:
			return 'DJ Steve';
		break;
		case 2:
			return 'DJ Daisuke';
		break;
		case 4:
			return 'DJ Tox';
		break;
		case 8:
			return 'DJ Hiro';
		break;
		case 3:
			return 'Steve ＆ Daisuke';
		break;
		default:
			if($member > 0){
				return 'Various Artists';
			}else{
				return 'Unknown Artist';
			}
	}
}


function getDownloadList($path,$client,$store){
	$dirInfo = $client->rawlist($path);
	static $dlCue = array();
	foreach ($dirInfo as $fileInfo){
		echo $fileInfo['name']."(".$fileInfo['type'].")\n";
		if($fileInfo['type'] == 'directory'){
			//ディレクトリなら再帰
			getDownloadList($client->current_directory().$fileInfo['name'],$client,$store);
		}else{
			//ファイルの場合は、未ダウンロードか調査してキューに登録
			if(!file_exists($store.$fileInfo['name'])){
				$dlCue[] = $path."/".$fileInfo['name'];
			}
		}
	}
	return $dlCue;
}
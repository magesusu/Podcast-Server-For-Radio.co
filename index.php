<?php

require "settings.php";
require "ftp.php";
require "mp3file.php";

//一時間までの実行を許可
//ini_set("max_execution_time",3600);
ini_set("max_execution_time",60);
echo "*****Podcast Add Tools*****\n";

//FTPクライアント呼び出し
$ftp = new FtpTransmission();

//ファイルのダウンロード
if($ftp->connect(HOST, USER, PASS)){
	$cue = getDownloadList("/",$ftp,SAVEDIR);
	foreach ($cue as $from){
		echo "[DL:Start]".$from."\n";
		$ftp->download($from,SAVEDIR);
		echo "[DL:End]".$from."\n";
	}
	$ftp->close();
}

//ファイルからポッドキャスト用リストを作成
static $medialist = [];
if ($dir = opendir(SAVEDIR)) {
	while (($file = readdir($dir)) !== false) {
		if ($file != "." && $file != "..") {
			echo "[BUILD]".$file."\n";
			//ファイル名からデータをパース
			$local = (preg_match('/(\\\\|\/)$/', SAVEDIR)) ? SAVEDIR . basename($file) : SAVEDIR . '/' . basename($file);
			$names = pathinfo($local);

			//MP3ファイル名から詳細情報を取得
			$mp3file = new MP3File($local);

			$data = [];
			$data['member'] = getDJCode($names["filename"]);
			$data['url'] = PREURL.rawurlencode($names['basename']);
			echo $data['url'];
			$data['title'] = (getTitleByDJC($data['member'])) ? getTitleByDJC($data['member']) ." ". preg_replace('/[^0-9]/', '', $names['filename']): $names['filename'];
			$data['filesize'] = filesize($local);
			$data['duration'] = MP3File::formatTime($mp3file -> getDuration(true));
			$data['artist'] = getArtistNameByDJC($data['member']);
			$data['picture'] = getPictureUrlByDJC($data['member']);
			$data['date'] = date("D, d M Y H:i:s O", filemtime($local));
			$medialist[] = $data;
		}
	}
	closedir($dir);
}

//xmlとして保存
$head = fopen(HEADER, 'r');
$foot = fopen(FOOTER, 'r');

file_put_contents(RSSPATH, $head);
$currentTime = new DateTime();
file_put_contents(RSSPATH, "<lastBuildDate>".$currentTime->format( "D, d M Y H:i:s O" )."</lastBuildDate>\n", FILE_APPEND | LOCK_EX);
foreach($medialist as $info){
	echo "[PUT]".htmlspecialchars($info['title'])."\n";
	file_put_contents(RSSPATH, "\t<item>\n", FILE_APPEND | LOCK_EX);
	file_put_contents(RSSPATH, "\t\t<title>".htmlspecialchars($info['title'])."</title>\n", FILE_APPEND | LOCK_EX);
	file_put_contents(RSSPATH, "\t\t<description>".htmlspecialchars($info['artist'])."</description>\n", FILE_APPEND | LOCK_EX);
	file_put_contents(RSSPATH, "\t\t<itunes:subtitle>".htmlspecialchars($info['title'])." - ".htmlspecialchars($info['artist'])."</itunes:subtitle>\n", FILE_APPEND | LOCK_EX);
	file_put_contents(RSSPATH, "\t\t<itunes:summary>".htmlspecialchars($info['title'])." - ".htmlspecialchars($info['artist'])."</itunes:summary>\n", FILE_APPEND | LOCK_EX);
	file_put_contents(RSSPATH, "\t\t<itunes:image href='".htmlspecialchars($info['picture'])."' />\n", FILE_APPEND | LOCK_EX);
	file_put_contents(RSSPATH, "\t\t<itunes:duration>".htmlspecialchars($info['duration'])."</itunes:duration>\n", FILE_APPEND | LOCK_EX);
	file_put_contents(RSSPATH, "\t\t<pubDate>".htmlspecialchars($info['date'])."</pubDate>\n", FILE_APPEND | LOCK_EX);
	file_put_contents(RSSPATH, "\t\t<enclosure url=\"".htmlspecialchars($info['url'])."\" length='".htmlspecialchars($info['filesize'])."' type='audio/mpeg'/>\n", FILE_APPEND | LOCK_EX);
	file_put_contents(RSSPATH, "\t\t<guid>".htmlspecialchars($info['url'])."</guid>\n", FILE_APPEND | LOCK_EX);
	file_put_contents(RSSPATH, "\t</item>\n", FILE_APPEND | LOCK_EX);

}
file_put_contents(RSSPATH, $foot, FILE_APPEND | LOCK_EX);


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
	switch($member){
		case 1:
			return 'https://images.radio.co/album_art/s71a30a170/1877488.100.1481286826.jpg';
			break;
		case 2:
			return 'https://images.radio.co/album_art/s71a30a170/1799865.100.1481214860.jpg';
			break;
		case 4:
			return 'https://images.radio.co/album_art/s71a30a170/1799866.100.1481214817.jpg';
			break;
		case 8:
			return 'https://images.radio.co/album_art/s71a30a170/2465351.100.1488520391.jpg';
			break;
		case 3:
			return 'https://images.radio.co/album_art/s71a30a170/2243204.100.1485440667.jpg';
			break;
		default:
			if($member > 0){
				return 'https://images.radio.co/album_art/s71a30a170/2337073.100.1486796031.jpg';
			}else{
				return 'https://i1.wp.com/ssr990.com/wp-content/uploads/2016/12/itunes_logo.jpg?ssl=1';;
			}
	}
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
				echo "[ADD]".$fileInfo['name']."\n";
			}
		}
	}
	return $dlCue;
}
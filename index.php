<?php

require "settings.php";
require "ftp.php";
require "mp3file.php";
require 'cologin.php';

//Allow up to one hour of execution
//ini_set("max_execution_time",3600);
ini_set("max_execution_time",60);
echo "*****Podcast Add Tools*****\n";

//Call FTP client
$ftp = new FtpTransmission();

// //Download files
// if($ftp->connect(HOST, USER, PASS)){
// 	$cue = getDownloadList("/",$ftp,SAVEDIR);
// 	foreach ($cue as $from){
// 		echo "[DL:Start]".$from."\n";
// 		$ftp->download($from,SAVEDIR);
// 		echo "[DL:End]".$from."\n";
// 	}
// 	$ftp->close();
// }

//Create a podcast list from the files
static $medialist = [];
$actuallInfo = parseTrack();
if ($dir = opendir(SAVEDIR)) {
	while (($file = readdir($dir)) !== false) {
		if ($file != "." && $file != "..") {
			echo "[BUILD]".$file."\n";
			//Extract file name from file path
			$local = (preg_match('/(\\\\|\/)$/', SAVEDIR)) ? SAVEDIR . basename($file) : SAVEDIR . '/' . basename($file);
			$names = pathinfo($local);
			echo $names['basename'];
			try{
				if(!$actuallInfo) throw new Exception('Bad Json');
				//Search JSON that matches file name
				global $title;
				foreach($actuallInfo['tracks'] as $info){
					//echo "JSON:".basename($info['file_path'])."\n"."LOCAL:".$names['basename']."\n";
					if(strcmp(basename($info['file_path']),$names['basename']) == 0){
						$title = $info['title'];
						foreach($info['tags'] as $tagId){
							$title .= " ".$tagId;
						}
						echo "SAME:".$title."\n";
						if($title == NULL || $title == "") throw new Exception('Bad Json (Processing title)');
						$data = [];
						//Don't need it
						//$data['member'] = getDJCode($names["filename"]);
						$data['url'] = PREURL.rawurlencode($names['basename']);
						//Or Radio.co Server (Expire for one hour !USE IT AT YOUR OWN LISK!)
						//$data['url'] = $actuallInfo['preview_url'];
						$data['title'] = $title;
						$data['filesize'] = filesize($local);
						$data['duration'] = MP3File::formatTime($info['duration']);
						$data['artist'] = $info['artist'];
						$data['picture'] = $info['artwork']['large_url'];
						$data['date'] = date("D, d M Y H:i:s O", filemtime($local));
						$medialist[] = $data;
						break;
					}
				}
			}catch (Exception $e){
				echo 'Switch To Fail-safe Mode. Due to: '.$e->getMessage()."\n";

				//Get details only from the title
				$mp3file = new MP3File($local);

				$data = [];
				$data['member'] = getDJCode($names["filename"]);
				$data['url'] = PREURL.rawurlencode($names['basename']);
				$data['title'] = (getTitleByDJC($data['member'])) ? getTitleByDJC($data['member']) ." ". preg_replace('/[^0-9]/', '', $names['filename']): $names['filename'];
				$data['filesize'] = filesize($local);
				$data['duration'] = MP3File::formatTime($mp3file -> getDuration(true));
				$data['artist'] = getArtistNameByDJC($data['member']);
				$data['picture'] = getPictureUrlByDJC($data['member']);
				$data['date'] = date("D, d M Y H:i:s O", filemtime($local));
				$medialist[] = $data;
			}
		}
	}
	closedir($dir);
}

//Save as a xml file
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
			//If the character string is a directory, recurs with that name
			getDownloadList($client->current_directory().$fileInfo['name'],$client,$store);
		}else{
			//If the character string is a file and has not been downloaded, it is added to the download queue
			if(!file_exists($store.$fileInfo['name'])){
				$dlCue[] = $path."/".$fileInfo['name'];
				echo "[ADD]".$fileInfo['name']."\n";
			}
		}
	}
	return $dlCue;
}
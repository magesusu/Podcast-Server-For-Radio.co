<?php

require "settings.php";
require "ftp.php";

//更新用の設定
function updateOutput(){
	flush();
	ob_end_flush();
	ob_start();
}

ob_start();

//FTPクライアント呼び出し
$ftp = new FtpTransmission();

//ファイルのダウンロード
if($ftp->connect($host, $user, $pass)){
	$cue = getDownloadList("/",$ftp,$saveDir);
	var_dump($cue);
	$ftp->close();
}
//ファイルからリストを作成


//xmlとして保存



function getDownloadList($path,$client,$store){
	$dirInfo = $client->rawlist($path);
	static $dlCue = array();
	foreach ($dirInfo as $fileInfo){
		updateOutput();
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

function bulkDownload(){

}

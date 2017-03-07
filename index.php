<?php

require "settings.php";
require "ftp.php";

//一時間までの実行を許可
ini_set("max_execution_time",3600);

//FTPクライアント呼び出し
$ftp = new FtpTransmission();

//ファイルのダウンロード
if($ftp->connect(HOST, USER, PASS)){
	$cue = getDownloadList("/",$ftp,SAVEDIR);
	var_dump($cue);
	foreach ($cue as $from){
		$ftp->download($from,SAVEDIR);
	}
	$ftp->close();
}
//ファイルからポッドキャスト用リストを作成


//xmlとして保存



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

function bulkDownload(){

}

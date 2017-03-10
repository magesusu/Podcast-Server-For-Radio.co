<?php

require 'settings.php';

//Original: http://mio-koduki.blogspot.jp/2012/08/phpcurl-phpcurlgoogle.html
//TODO: 名前をgetにし、タグから計算した名前・写真や音楽のプレビューに関するデータが入った配列を返すように改善

function parseTrack(){
	$json = getRadioCoTrackInformation();
	//タグ情報を文字に書き換える
	if($json){
		foreach($json['tracks'] as &$info){
			//tagsの名前を検索する
			foreach($info['tags'] as &$tagId){
				foreach($json['tags'] as $tag){
					if($tag['id'] == $tagId){
						$tagId = $tag['name'];
						var_dump($tagId);
						break;
					}
				}
			}
		}
	}
	return $json;
}

function getRadioCoTrackInformation(){

	//URLを指定する
	$url='https://studio.radio.co/login';
	//POST用のデータを作っておく
	$data=array
	(
			//ID部分（適宜置き換え）
			'_username'=>COUSER,
			//パスワード部分（適宜置き換え）
			'_password'=>COPASS,
			//ログインを維持するかのチェックボックス部分
			//'_remember_me'=>'on',
	);
	//テンポラリファイルを作成する
	$cookie=tempnam(sys_get_temp_dir(),'cookie_');
	//cURLを初期化して使用可能にする
	$curl=curl_init();
	//オプションにURLを設定する
	curl_setopt($curl,CURLOPT_URL,$url);
	//文字列で結果を返させる
	curl_setopt($curl,CURLOPT_RETURNTRANSFER,true);
	//クッキーを書き込むファイルを指定
	curl_setopt($curl,CURLOPT_COOKIEJAR,$cookie);
	//SSL警告を無視
	curl_setopt($curl,CURLOPT_SSL_VERIFYPEER, false);
	//URLにアクセスし、結果を文字列として返す
	$html=curl_exec($curl);
	//cURLのリソースを解放する
	curl_close($curl);
	//Document初期化
	$dom=new DOMDocument();
	//html文字列を読み込む（htmlに誤りがある場合エラーが出るので@をつける）
	$dom->loadHTML($html);
	//XPath初期化
	$xpath=new DOMXPath($dom);
	//inputのtypeがhiddenの要素をとってくる
	$node=$xpath->query('//input[@type="hidden"]');
	foreach($node as $v)
	{
		//POST用のデータに追加する
		$data[$v->getAttribute('name')]=$v->getAttribute('value');
	}
	//cURLを初期化して使用可能にする
	$curl=curl_init();
	//オプションにURLを設定する
	curl_setopt($curl,CURLOPT_URL,'https://studio.radio.co/login_check');
	//メソッドをPOSTに設定
	curl_setopt ($curl,CURLOPT_POST,true);
	//文字列で結果を返させる
	curl_setopt($curl,CURLOPT_RETURNTRANSFER,true);
	//POSTデータ設定
	curl_setopt($curl,CURLOPT_POSTFIELDS,$data);
	//クッキーを読み込むファイルを指定
	curl_setopt($curl,CURLOPT_COOKIEFILE,$cookie);
	//クッキーを書き込むファイルを指定
	curl_setopt($curl,CURLOPT_COOKIEJAR,$cookie);
	//Locationをたどる
	curl_setopt($curl,CURLOPT_FOLLOWLOCATION,true);
	//SSL警告を無視
	curl_setopt($curl,CURLOPT_SSL_VERIFYPEER, false);
	//URLにアクセスし、結果を表示させる
	curl_exec($curl);
	//cURLのリソースを解放する
	curl_close($curl);

	//cURLを初期化して使用可能にする
	$curl=curl_init();
	//オプションにURLを設定する
	curl_setopt($curl,CURLOPT_URL,'https://studio.radio.co/api/v1/stations/s71a30a170/tracks');
	//文字列で結果を返させる
	curl_setopt($curl,CURLOPT_RETURNTRANSFER,true);
	//クッキーを読み込むファイルを指定
	curl_setopt($curl,CURLOPT_COOKIEFILE,$cookie);
	//SSL警告を無視
	curl_setopt($curl,CURLOPT_SSL_VERIFYPEER, false);
	//URLにアクセスし、結果を表示させる
	$json = json_decode(curl_exec($curl),true);
	//cURLのリソースを解放する
	curl_close($curl);

	//テンポラリファイルを削除
	unlink($cookie);

	return $json;
}
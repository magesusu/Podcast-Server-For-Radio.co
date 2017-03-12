<?php

function parseTrack(){
	$json = getRadioCoTrackInformation();
	//Change ID to name
	if($json){
		foreach($json['tracks'] as &$info){
			foreach($info['tags'] as &$tagId){
				foreach($json['tags'] as $tag){
					if($tag['id'] == $tagId){
						$tagId = $tag['name'];
						break;
					}
				}
			}
		}
	}
	return $json;
}

//Original: http://mio-koduki.blogspot.jp/2012/08/phpcurl-phpcurlgoogle.html
function getRadioCoTrackInformation(){

	//URL
	$url='https://studio.radio.co/login';
	//POST Data
	$data=array
	(
			'_username'=>COUSER,
			'_password'=>COPASS,
			//'_remember_me'=>'on',
	);
	//Create temp file
	$cookie=tempnam(sys_get_temp_dir(),'cookie_');
	//Enble cURL
	$curl=curl_init();
	//Add URL option
	curl_setopt($curl,CURLOPT_URL,$url);
	//Receive result as a string
	curl_setopt($curl,CURLOPT_RETURNTRANSFER,true);
	//Decide where to store the cookie
	curl_setopt($curl,CURLOPT_COOKIEJAR,$cookie);
	//Ignore security warnings (SSL)
	curl_setopt($curl,CURLOPT_SSL_VERIFYPEER, false);
	//Acquire data
	$html=curl_exec($curl);
	//Free cURL resources
	curl_close($curl);
	//Init Document
	$dom=new DOMDocument();
	//Read HTML strings
	$dom->loadHTML($html);
	//Init XPath
	$xpath=new DOMXPath($dom);
	//Find hidden elements from html
	$node=$xpath->query('//input[@type="hidden"]');
	foreach($node as $v)
	{
		//Add this elements to the POST request
		$data[$v->getAttribute('name')]=$v->getAttribute('value');
	}
	//Enble cURL
	$curl=curl_init();
	//Add URL option
	curl_setopt($curl,CURLOPT_URL,'https://studio.radio.co/login_check');
	//Set method to POST
	curl_setopt ($curl,CURLOPT_POST,true);
	//Receive result as a string
	curl_setopt($curl,CURLOPT_RETURNTRANSFER,true);
	//Set POST data
	curl_setopt($curl,CURLOPT_POSTFIELDS,$data);
	//Decide where to read the cookie
	curl_setopt($curl,CURLOPT_COOKIEFILE,$cookie);
	//Decide where to store the cookie
	curl_setopt($curl,CURLOPT_COOKIEJAR,$cookie);
	//Follow Location
	curl_setopt($curl,CURLOPT_FOLLOWLOCATION,true);
	//Ignore security warnings (SSL)
	curl_setopt($curl,CURLOPT_SSL_VERIFYPEER, false);
	//Acquire data
	curl_exec($curl);
	//Free cURL resources
	curl_close($curl);

	//Enble cURL
	$curl=curl_init();
	//Add URL option
	curl_setopt($curl,CURLOPT_URL,'https://studio.radio.co/api/v1/stations/s71a30a170/tracks');
	//Receive result as a string
	curl_setopt($curl,CURLOPT_RETURNTRANSFER,true);
	//Decide where to read the cookie
	curl_setopt($curl,CURLOPT_COOKIEFILE,$cookie);
	//Ignore security warnings (SSL)
	curl_setopt($curl,CURLOPT_SSL_VERIFYPEER, false);
	//Acquire data
	$json = json_decode(curl_exec($curl),true);
	//Free cURL resources
	curl_close($curl);

	//Delete temp file
	unlink($cookie);

	return $json;
}
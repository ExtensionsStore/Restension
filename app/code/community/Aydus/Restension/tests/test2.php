<?php

/**
 * Get request token
 */

$realm="http://local.appleeye.com/";
$url = $realm."oauth/initiate";
$oauth_callback="http://local.appleeye.com/customer/account";
$oauth_consumer_key="c85c38ee549b2012eb43674330280b9a";
$oauth_nonce = substr(md5(uniqid('nonce_', true)),0,16);
$oauth_signature_method="HMAC-SHA1";
$oauth_timestamp=time();
$oauth_version="1.0";
$oauth_method="POST";

$params = array(
    "oauth_callback" => $oauth_callback,//this is url encoded
    "oauth_consumer_key" => $oauth_consumer_key,
    "oauth_nonce" => $oauth_nonce,
    "oauth_signature_method" => $oauth_signature_method,
    "oauth_timestamp" => $oauth_timestamp,
    "oauth_version" => $oauth_version,
);

$data = http_build_query($params);
//var_dump($data);exit();

$send_data=$oauth_method."&".urlencode($url)."&".urlencode($data);//oauth_callback double encoded
echo $send_data."\n\n";
$algo="sha1";
$key="4bfc7828e36107974f2baa38d8bc2385&"; //consumer secret & token secret //Both are used in generate signature

$sign=hash_hmac($algo,$send_data,$key,1); // consumer key and token secrat used here
$fin_sign=base64_encode($sign);

$header = "Authorization: OAuth realm=\"$realm\",";
    
foreach ($params as $key=>$value){
    $header .=  $key.'="'.$value."\", ";
}

$header .= "oauth_signature=\"".$fin_sign.'"';

echo $header."\n\n"; 
exit();
$curl = curl_init();

curl_setopt($curl,CURLOPT_HTTPHEADER,array($header));

curl_setopt ($curl, CURLOPT_URL,$url);
$xml = curl_exec($curl);

var_dump($xml);
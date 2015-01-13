<?php

$realm = "http://ee-1.14.local/";
$endpointUrl = $realm."oauth/initiate";
$oauthCallback = "http://ee-1.14.local/";
$oauthConsumerKey = "aa9aa3b12de0789f3991d2f73134c6fd";
$oauthConsumerSecret = "6de6220db74a253d155493caf135a3a3";
$oauthNonce = substr(md5(uniqid('oauth_nonce_', true)),0,16);
$oauthSignatureMethod = "HMAC-SHA1";
$oauthTimestamp = time();
$oauthVersion = "1.0";
$oauthMethod = "POST";

$params = array(
    "oauth_callback" => $oauthCallback,
    "oauth_consumer_key" => $oauthConsumerKey,
    "oauth_nonce" => $oauthNonce,
    "oauth_signature_method" => $oauthSignatureMethod,
    "oauth_timestamp" => $oauthTimestamp,
    "oauth_version" => $oauthVersion,
);

$data = http_build_query($params);

echo $data."\n\n";

$encodedData = $oauthMethod."&".urlencode($endpointUrl)."&".urlencode($data);
$key = $oauthConsumerSecret."&"; 
$signature = hash_hmac("sha1", $encodedData, $key, 1); 
$oauthSignature = base64_encode($signature);

$header = "Authorization: OAuth realm=\"$realm\",";
foreach ($params as $key=>$value){
    $header .=  $key.'="'.$value."\",";
}
$header .= "oauth_signature=\"".$oauthSignature.'"';

echo $header."\n\n"; 

exit();
$curl = curl_init();

curl_setopt($curl, CURLOPT_POST, true);
curl_setopt($curl, CURLOPT_HTTPHEADER, array($header));
curl_setopt($curl, CURLOPT_URL, $endpointUrl);

curl_exec($curl);


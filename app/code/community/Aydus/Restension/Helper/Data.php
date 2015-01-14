<?php

/**
 * Restension helper
 *
 * @category   Aydus
 * @package    Aydus_Restension
 * @author     Aydus Consulting <davidt@aydus.com>
 */

class Aydus_Restension_Helper_Data extends Mage_Core_Helper_Abstract
{
	/**
	 * 
	 * @param string $key
	 * @param string $secret
	 * @param string $callbackUrl
	 * @param string $endpoint
	 * @param string $oauthToken
	 * @param string $oauthVerifier
	 * @return string
	 */
	public function getOauth1Header($key, $secret, $callbackUrl, $endpoint, $oauthToken=NULL, $oauthVerifier=NULL) 
	{
	    $realm = $callbackUrl;
	    $endpointUrl = $realm . $endpoint;
	    $oauthCallback = $callbackUrl;
	    $oauthConsumerKey = $key;
	    $oauthConsumerSecret = $secret;
	    $oauthNonce = substr(md5(uniqid('oauth_nonce_', true)), 0, 16);
	    $oauthSignatureMethod = "HMAC-SHA1";
	    $oauthTimestamp = time();
	    $oauthVersion = "1.0";
	    $oauthMethod = "POST";
	
	    $params = array();
	    $params["oauth_callback"] = $oauthCallback;
	    $params["oauth_consumer_key"] = $oauthConsumerKey;
	    $params["oauth_nonce"] = $oauthNonce;
	    $params["oauth_signature_method"] = $oauthSignatureMethod;
	    $params["oauth_timestamp"] = $oauthTimestamp;
	    $params["oauth_version"] = $oauthVersion;
	
	    $data = http_build_query($params);
	
	    $encodedData = $oauthMethod . "&" . urlencode($endpointUrl) . "&" . urlencode($data);
	    $key = $oauthConsumerSecret . "&";
	    $signature = hash_hmac("sha1", $encodedData, $key, 1);
	    $oauthSignature = base64_encode($signature);
	
	    $header = "Authorization: OAuth realm=\"$realm\",";
	    foreach ($params as $key => $value) {
	        $header .= $key . '="' . $value . "\",";
	    }
	    $header .= "oauth_signature=\"" . $oauthSignature . '"';
	
	    return $header;
	}	

}
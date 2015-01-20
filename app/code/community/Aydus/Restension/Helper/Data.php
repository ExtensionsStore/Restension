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
	
	/**
	 * 
	 * @param Mage_Oauth_Model_Consumer|int $consumer
	 * 
	 * @return int
	 */
	public function getConsumerStoreId($consumer)
	{
		$storeId = Mage::app()->getDefaultStoreView()->getId();
		
		if (is_object($consumer)){
		    $consumerModel = $consumer;
		} else {
			$consumerModel = Mage::getModel('oauth/consumer')->load((int)$consumer);
		}
		
		if ($consumerModel && $consumerModel->getId()){
			
    		$resource = Mage::getSingleton('core/resource');
    		$read = $resource->getConnection('core_read');
    		$prefix = Mage::getConfig()->getTablePrefix();
    		$table = $prefix.'aydus_restension_consumer';
    		$consumerId = $consumerModel->getId();
    		
    		$storeId = $read->fetchOne("SELECT store_id FROM $table WHERE consumer_id = '$consumerId'");
    	}
		
		return $storeId;
	}
	
	/**
	 * http://stackoverflow.com/questions/10589889/returning-header-as-array-using-curl
	 * 
	 * @param string $headerContent
	 * @return array
	 */
    public function getHeadersArray($headerContent)
    {
        $headers = array();
    
        // Split the string on every "double" new line.
        $arrRequests = explode("\r\n\r\n", $headerContent);
    
        // Loop of response headers. The "count() -1" is to 
        //avoid an empty row for the extra line break before the body of the response.
        for ($index = 0; $index < count($arrRequests) -1; $index++) {
    
            foreach (explode("\r\n", $arrRequests[$index]) as $i => $line)
            {
                if ($i === 0)
                    $headers[$index]['http_code'] = $line;
                else
                {
                    list ($key, $value) = explode(': ', $line);
                    $headers[$index][$key] = $value;
                }
            }
        }
    
        return $headers;
    }

}
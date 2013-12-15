<?php

class XmlrpcClient {
	private $username;
	private $password;
	private $blogId;
	private $endpoint;
	private $xmlrpcRequest;
	
	public function __construct($username, $password, $blogId, $endpoint) {
		$this->username = $username;
		$this->password = $password;
		$this->blogId = $blogId;
		$this->endpoint = $endpoint;
		
	}
	
	public function createRequest($requestName, $requestParams) {
		$this->xmlrpcRequest = xmlrpc_encode_request($requestName, $requestParams, array("encoding" => "UTF-8"));
		return $this->xmlrpcRequest;
	}
	
	public function sendRequest() {
	    $ch = curl_init();
	    $curlOptsArray = array(
	    	CURLOPT_POSTFIELDS		=> $this->xmlrpcRequest,
	    	CURLOPT_URL				=> $this->endpoint,
	    	CURLOPT_RETURNTRANSFER	=> TRUE,
	    	CURLOPT_TIMEOUT			=> TRUE,
	    );
	    	    
	    curl_setopt_array($ch, $curlOptsArray);
	    $response = curl_exec($ch);
	    curl_close($ch);
	    return $response;
	}
	
	public function createPostParams($postTitle, $postContent, $postType, $customFields, $taxonomies) {
		$content = array(
    		'post_title'	=> $postTitle,
    		'post_content'	=> $postContent,
    		'post_type'		=> $postType,
    		'post_status'	=> 'draft',
    		'custom_fields'	=> $customFields,
    		'terms_names'	=> $taxonomies,
		);
		$postParams = array($this->blogId,$this->username,$this->password,$content,true);
		return $postParams;
		
		
	}

}

?>


<?php
require_once '../ajax/xmlrpc-client-class.php';

/**
 *
 */
class TestXmlrpcClient extends PHPUnit_Framework_TestCase {

	private $xmlrpcClient;
	
	private $username;
	private $password;
	private $blogId;
	private $endpoint;
	
	private $requestName;
	private $requestParams;

	public function setUp() {
		$this->username = "rpcxml";
		$this->password = "oT5VcsoF";
		$this->blogId = 0;
		$this->endpoint = "http://localhost/development/wordpress/xmlrpc.php";
		$this->xmlrpcClient = new XmlrpcClient($this->username, $this->password, $this->blogId, $this->endpoint);
		
    	$this->requestName = "demo.sayHello";
    	$this->requestParams = array(
    		"title"		=> "hello",
    		"content"	=> "Xmlrpc content."
    	);

	}
	
    public function testConstruct() {
	    $this->assertInstanceOf("XmlrpcClient", $this->xmlrpcClient, $message = "Tried asserting that \$this->xmlrpcClient is an instance of XmlrpcClient.");
/*
 * Made all these properties private.
	    $this->assertEquals($this->username, $this->xmlrpcClient->username);
	    $this->assertEquals($this->password, $this->xmlrpcClient->password);
	    $this->assertEquals($this->endpoint, $this->xmlrpcClient->endpoint);
*/
    }
    
    public function testCreateRequest() {
	    $xmlrpcRequest = $this->xmlrpcClient->createRequest($this->requestName, $this->requestParams);
	    //echo "xmlrpcRequest: " . $xmlrpcRequest . PHP_EOL;
	    
	    $decodedRequest = xmlrpc_decode_request($xmlrpcRequest, $this->requestName);
	    //echo "decodedRequest: " . print_r($decodedRequest, TRUE) . PHP_EOL;
	    
		foreach ($this->requestParams as $key => $value) {
			$this->assertEquals($this->requestParams[$key], $decodedRequest[0][$key]);
		}
    }
    
    public function testSendRequest() {
	    $xmlrpcRequest = $this->xmlrpcClient->createRequest($this->requestName, $this->requestParams);

	    $response = $this->xmlrpcClient->sendRequest();
	    //echo "RESPONSE: " . $response . PHP_EOL;
	    $decodedResponse = xmlrpc_decode_request($response, $requestName);
	    $this->assertEquals($decodedResponse, "Hello!");
    }
    
/*
    public function testGetPost() {
    	$postType = "tmt-coupon-posts";
    	$filter = array(
    		'post_type'	=> $postType,
    	);
	    $postParams = array(
	    	$this->blogId,
	    	$this->username,
	    	$this->password,
	    	$filter,
	    	array(
	    		"post_title",
	    		"custom_fields",
	    	),
	    	true
	    );
	    $xmlrpcRequest = $this->xmlrpcClient->createRequest("wp.getPosts", $postParams);
	    $response = $this->xmlrpcClient->sendRequest();
		echo "GET POSTS RESPONSE: " . $response . PHP_EOL;

    }
*/

    public function testCreatePost() {
    	$encoding='UTF-8';
    	$postTitle = htmlentities("Xmlrpc Post Title",ENT_NOQUOTES,$encoding);
    	$postContent = "Xmlrpc post body!";
    	$postType = "tmt-coupon-posts";
    	$customFields = array(
    		array(
    			"key" 	=> "code",
    			"value"	=> "abcdef",
    		),
    		array(
    			"key"	=> "expires",
    			"value"	=> "20131231" // YYYYMMDD
    		),
    		array(
    			"key"	=>	"url",
    			"value"	=> "http://merchant.com",
    		),
    		array(
    			"key"	=> "offer_id",
    			"value"	=> "1234567890",
    		),
    	);
    	
    	$taxonomies = array(
    		"product_type"	=> array(
    			"shoes",
    			"clothing",
    		),
    		"merchant"	=> array(
    			"merchant-1",
    		),
    	);

	    $postParams = $this->xmlrpcClient->createPostParams($postTitle, $postContent, $postType, $customFields, $taxonomies);
	    $xmlrpcRequest = $this->xmlrpcClient->createRequest("wp.newPost", $postParams);
	    $response = $this->xmlrpcClient->sendRequest();
		echo "NEW POST RESPONSE: " . $response . PHP_EOL;
    }

	public function testHandleError() {
		// http://codex.wordpress.org/XML-RPC_WordPress_API/Posts#Errors_2
/*
<methodResponse>
  <fault>
    <value>
      <struct>
        <member>
          <name>faultCode</name>
          <value><int>400</int></value>
        </member>
        <member>
          <name>faultString</name>
          <value><string>Insufficient arguments passed to this XML-RPC method.</string></value>
        </member>
      </struct>
    </value>
  </fault>
</methodResponse>
*/
	}

}
?>
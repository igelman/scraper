<?php
require_once '/Users/alantest/Developer/src/wordpress-tests/bootstrap.php';
//require_once '/Volumes/Macintosh HD/Library/Server/Web/Data/Sites/Default/development/wordpress/wp-content/plugins/OFFtmt-deal-posts/tmt-deal-posts-class.php';
require_once '../ajax/post-to-wordpress-class.php';

/**
 * The class API needs to be rethought, since 
 *  we need to implement commands as XMLRPC call,
 *  not the native API calls like wp_insert_post
 */
class TestPostToWordpress extends WP_UnitTestCase {

	private $postToWordpress;
	private $title;
	private $offerId;

	public function setUp() {
        parent::setUp();
        $this->postToWordpress = new PostToWordpress();
        $title = "Test Post";
        $offerId = "abcdefg";
	}

    public function testConstruct() {
	    $this->assertInstanceOf("PostToWordpress", $this->postToWordpress, $message = "Tried asserting that \$this->postToWordpress is an instance of PostToWordpress.");
    }
    
    public function testAuth() {
	    // https://developer.wordpress.com/docs/oauth2/
	    // https://developer.wordpress.com/docs/api/
    }
    
    public function testCreatePost() {
    	
    	$postId = $this->postToWordpress->createPost($this->title);
    	$this->assertTrue($postId > 0); // post exists
    	
    	$postObject = get_post($postId);
    	$customArray = get_post_custom($postId);

    	$this->assertTrue( $postObject->post_title == $this->title); // title exists
	    $this->assertTrue( $postObject->post_status == "draft"); // status is draft
	    $this->assertTrue( $postObject->post_type == "tmt-coupon-posts"); // post type is coupon
    }
    
    
    public function testAddCustomFields() {
    	$postId = $this->postToWordpress->createPost($this->title);
    	$addCustomFieldResult = $this->postToWordpress->addCustomField("offer_id", $this->offerId);
	    $this->assertTrue( (boolean) $addCustomFieldResult, "Assert addCustomField returned TRUE");
	    
	    $this->assertEquals($this->offerId, get_post_meta( $postId, "offer_id", TRUE ), "Assert correct offerId is stored");
    }

    public function testPostExists() {
	    // if post with RMN ID already exists, TRUE
    }


}

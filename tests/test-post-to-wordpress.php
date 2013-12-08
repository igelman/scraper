<?php
require_once '/Users/alantest/Developer/src/wordpress-tests/bootstrap.php';
//require_once '/Volumes/Macintosh HD/Library/Server/Web/Data/Sites/Default/development/wordpress/wp-content/plugins/OFFtmt-deal-posts/tmt-deal-posts-class.php';
require_once '../ajax/post-to-wordpress-class.php';

/**
 * TMT Deal Posts Tests
 */
class TestPostToWordpress extends WP_UnitTestCase {

	public function setUp() {
        parent::setUp();
        $this->postToWordpress = new PostToWordpress();
	}

    public function testConstruct() {
	    $this->assertInstanceOf("PostToWordpress", $this->postToWordpress, $message = "Tried asserting that \$this->postToWordpress is an instance of PostToWordpress.");
    }
    
    public function testCreatePost() {
    	$title = "Test Post";
    	$postId = $this->postToWordpress->createPost($title);
    	$this->assertTrue($postId > 0); // post exists
    	
    	$postObject = get_post($postId);
    	$customArray = get_post_custom($postId);

    	$this->assertTrue( $postObject->post_title == $title); // title exists
	    $this->assertTrue( /* get_post_status( $postId ) */ $postObject->post_status == "draft"); // status is NOT published
	    $this->assertTrue( $postObject->post_type == "tmt-coupon-posts"); // post type is coupon
    }
    
    
    public function testAddCustomFields() {
	    // coupon code, etc
    }

    public function testPostExists() {
	    // if post with RMN ID already exists, TRUE
    }


}

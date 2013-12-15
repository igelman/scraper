<?php

class PostToWordpress {
	private $postId;
	private $title;
	
	public function createPost($title) {
		$this->title = $title;
		$postElements = $this->setPostElements();
		// $this->postId = wp_insert_post( $postElements );
		/**
		*
		* This method needs to return XMLRPC parameters
		* Then have another method to construct the XMLRPC call
		* And finally a method to cURL the call.
		*
		* There are a couple plug-ins that provide a JSON API
		*  which may be worth implementing, but maybe not since XMLRPC is the native implementation
		**/
		return $this->postId;
	}
	
	public function addCustomField($meta_key, $meta_value) {
		return add_post_meta($this->postId, $meta_key, $meta_value, FALSE);
	}
	
	private function setPostElements() {
		return array(
			'post_title'	=> $this->title,
			'post_type'		=> "tmt-coupon-posts",
			'post_status'	=> "draft",
		);
	}
}

?>
<?php

class PostToWordpress {
	private $postId;
	private $title;
	
	public function createPost($title) {
		$this->title = $title;
		$postElements = $this->setPostElements();
		$this->postId = wp_insert_post( $postElements );
		return $this->postId;
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
<?php

class SemaphoreManager {
	private $dirname;
	private $basename;
	private $content;
	private $path;
	
	public function __construct($dirname, $basename, $content = "") {
		$this->dirname = $dirname;
		$this->basename = $basename;
		$this->path = $this->dirname . $this->basename;
		$this->content = $content;
	}
	
	public function getPath() {
		return $this->path;
	}
	
	public function getContent() {
		return $this->content;
	}
	
	public function setContent($content) {
		$this->content = $content;
	}
	
	public function createSemaphore() {
		return file_put_contents($this->path, $this->content);
	}
	
	public function removeSemaphore() {
		if ( $this->semaphoreExists() ) {
			return unlink($this->path);	
		}
		return TRUE;
	}
	
	public function semaphoreExists() {
		return file_exists($this->path);
	}
	
	public function readSemaphore() {
		if ( $this->semaphoreExists() ) {
			return file_get_contents($this->path);
		}
		return FALSE;
	}
	
	public function sendSemaphoreContents($recipient, $subject) {
		if ( $this->semaphoreExists() ) {
			return mail($recipient, $subject, $this->readSemaphore());
		}
		return FALSE;
		
	}
}

?>
<?php

class ArrayQueuer {
	
	private $fullArray;
	private $remainingArray;
	private $maxLength;
	
	public function __construct($fullArray, $maxLength) {
		$this->fullArray = $fullArray;
		$this->remainingArray = $this->fullArray;
		$this->maxLength = $maxLength;
	}
	
	public function getRemainingArray() {
		return $this->remainingArray;
	}
	
	public function getNextSet() {
		for ($i = 0; $i <= ($this->maxLength - 1); $i++) {
			if (count($this->remainingArray) > 0) {
				$nextArray[$i] = array_shift($this->remainingArray);
			}
		}
		return $nextArray;
	}
	
	public function getSets() {
		$cacheRemainingArray = $this->remainingArray;
		$sets = array();
		while(count($this->remainingArray) > 0) {
			$sets[] = $this->getNextSet();
		}
		$this->remainingArray = $cacheRemainingArray;
		return $sets;
	}
	
}

?>
<?php
require_once("../ajax/client-select-parsed-content-class.php");

class TestClientSelectParsedContent extends PHPUnit_Framework_TestCase {

	private $offset = 5;
	private $maxRecords = 1;
	private $url = "http://www.retailmenot.com/view/worldmarket.com";
	private $parserItems = 17;  // A little fragile: "17" is the count of data items. So if the parser ever changes, that number will change.

	protected function setUp() {
		$this->cspc = new ClientSelectParsedContent($this->offset, $this->maxRecords);
		$this->cspcu = new ClientSelectParsedContent($this->offset, $this->maxRecords, $this->url);
	}
	
	public function testConstruct() {
	// This needs to be changed for refactoring of constructor, which needs a url argument
		$this->assertInstanceOf("ClientSelectParsedContent", $this->cspc);
	}
	
	public function testCreateQuery() {
		$this->cspc->createQuery();
		$stmt = $this->cspc->getQueryStatement();
		$this->assertInstanceOf("PDOStatement", $stmt);
		$this->assertEquals("SELECT url, date_retrieved, parsed_content FROM files WHERE LENGTH(parsed_content) > 0  LIMIT :maxRecords OFFSET :offset", $stmt->queryString);
		
		$this->cspcu->createQuery();
		$stmt = $this->cspcu->getQueryStatement();
		$this->assertInstanceOf("PDOStatement", $stmt);
		$this->assertEquals("SELECT url, date_retrieved, parsed_content FROM files WHERE LENGTH(parsed_content) > 0 AND url = :url", $stmt->queryString);
	}
	
	public function testBindParameters() {
		$this->cspc->createQuery();
		$this->assertTrue($this->cspc->bindParameters());
		
		$this->cspcu->createQuery();
		$this->assertTrue($this->cspcu->bindParameters());
	}
	
	public function testExecuteQuery() {
		$this->cspc->createQuery();
		$this->cspc->bindParameters();
		$this->assertTrue($this->cspc->executeQuery());

		$this->cspcu->createQuery();
		$this->cspcu->bindParameters();
		$this->assertTrue($this->cspcu->executeQuery());
	}
	
	public function testGetParsedContent() {
		$this->cspc->createQuery();
		$this->cspc->bindParameters();
		$this->cspc->executeQuery();
		$this->assertEquals($this->maxRecords, count($this->cspc->getParsedContent()));

		$this->cspcu->createQuery();
		$this->cspcu->bindParameters();
		$this->cspcu->executeQuery();
		$this->assertEquals(1, count($this->cspcu->getParsedContent()));
	}
	

	public function testAggregateParsedContent() {
		$this->cspc->createQuery();
		$this->cspc->bindParameters();
		$this->cspc->executeQuery();
		$this->cspc->getParsedContent();
		$this->cspc->aggregateParsedContent();
		
		$aggregateArray = $this->cspc->getAggregateArray();
		$this->assertEquals($this->parserItems, count($aggregateArray[0])); // A little fragile: "parserItems" is the count of data items. So if the parser ever changes, that number will change.
		
		$this->cspcu->createQuery();
		$this->cspcu->bindParameters();
		$this->cspcu->executeQuery();
		$this->cspcu->getParsedContent();
		$this->cspcu->aggregateParsedContent();
		
		$aggregateArray = $this->cspcu->getAggregateArray();
		$this->assertEquals($this->parserItems, count($aggregateArray[0])); // A little fragile: "parserItems" is the count of data items. So if the parser ever changes, that number will change.

	}
	
		public function testAggregateParsedContentUrl() {
	// This needs a test
		$this->cspcu->createQuery();
		$this->cspcu->bindParameters();
		$this->cspcu->executeQuery();
		$this->cspcu->getParsedContent();
		$this->cspcu->aggregateParsedContent();
		var_dump($this->cspcu->getAggregateArray());
	}
}

?>

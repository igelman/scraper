<?php
require_once("../ajax/client-select-parsed-content-class.php");

class TestClientSelectParsedContent extends PHPUnit_Framework_TestCase {

	private $offset = 5;
	private $maxRecords = 2;
	private $url = "http://www.retailmenot.com/view/fossil.com";

	protected function setUp() {
		$this->cspc = new ClientSelectParsedContent($this->offset, $this->maxRecords);
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
		
		$this->cspc->url = $this->url;
		$this->cspc->createQuery();
		$stmt = $this->cspc->getQueryStatement();
		$this->assertInstanceOf("PDOStatement", $stmt);
		$this->assertEquals("SELECT url, date_retrieved, parsed_content FROM files WHERE LENGTH(parsed_content) > 0 AND url = :url LIMIT :maxRecords OFFSET :offset", $stmt->queryString);
	}
	
	public function testCreateWhereUrlClause() {
		$this->assertEquals("",$this->cspc->createWhereUrlClause());
	
		$this->assertEquals("AND url = :url", $this->cspc->createWhereUrlClause($this->url));
		$this->assertEquals($this->url, $this->cspc->url);
	}
	
	public function testBindParameters() {
		$this->cspc->createQuery();
		$this->assertTrue($this->cspc->bindParameters());
	}
	
	public function testExecuteQuery() {
	// This needs a test
		$this->cspc->createQuery();
		$this->cspc->bindParameters();
		$this->cspc->executeQuery();
	}
	
	public function testGetParsedContent() {
	// This needs a test
		$this->cspc->createQuery();
		$this->cspc->bindParameters();
		$this->cspc->executeQuery();
		$this->cspc->getParsedContent();
		//var_dump($this->cspc->getParsedContent());
	}
	
	public function testAggregateParsedContent() {
	// This needs a test
		$this->cspc->createQuery();
		$this->cspc->bindParameters();
		$this->cspc->executeQuery();
		$this->cspc->getParsedContent();
		$this->cspc->aggregateParsedContent();
		var_dump($this->cspc->getAggregateArray());
	}
}

?>

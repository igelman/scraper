<?php
require_once("../config/local.config"); // define DB connection params DB_HOST DB DB_USER DB_PASS

class PdoManager {

	/*** Declare instance ***/
	private static $instance = NULL;

	/**
	*
	* the constructor is set to private so
	* so nobody can create a new instance using new
	*
	*/
	private function __construct() {
		
	  /*** maybe set the db name here later ***/
	}


	/**
	* Return DB instance or create intitial connection
	* @return object (PDO)
	* @access public
	*/
	public static function getInstance() {
	
		$dbHost = DB_HOST;
		$db = DB;
		$dbUser = DB_USER;
		$dbPass = DB_PASS;
		if (!self::$instance) {
			try {
				self::$instance = new PDO("mysql:host=$dbHost;dbname=$db", $dbUser, $dbPass, array(PDO::ATTR_PERSISTENT => true) );
				self::$instance-> setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);				
			} catch(PDOException $e) {
				echo $e->getMessage();
			}
		}
		return self::$instance;
	}

	/**
	*
	* Like the constructor, we make __clone private
	* so nobody can clone the instance
	*
	*/
	private function __clone(){
	}

}

?>
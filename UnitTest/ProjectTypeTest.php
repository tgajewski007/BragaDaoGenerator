<?php
/**
 * Created on 10-03-2013 19:02:20
 * author Tomasz Gajewski
 * package frontoffice
 * error prefix
 *
 */
require_once "O:/wwwroot/PHPDAOGenerator/include/PHPDAO.php";
class ProjectTypeTest extends PHPUnit_Framework_TestCase
{
	// -------------------------------------------------------------------------
	function testConst()
	{
		$this->assertEquals("DB", ProjectType::DB);
		$this->assertEquals("DB2", ProjectType::DB2);
		$this->assertEquals("DBEN", ProjectType::DBEN);
	}
	// -------------------------------------------------------------------------
}
?>
<?php
/**
 * Created on 10-03-2013 17:15:27
 * author Tomasz Gajewski
 * package frontoffice
 * error prefix
 *
 */

require_once "O:/wwwroot/PHPDAOGenerator/include/PHPDAO.php";
class ColumnTypeTest extends PHPUnit_Framework_TestCase
{
	// -------------------------------------------------------------------------
	function testEnums()
	{
		$this->assertEquals("CHAR", ColumnType::CHAR);
		$this->assertEquals("DATE", ColumnType::DATE);
		$this->assertEquals("DATETIME", ColumnType::DATETIME);
		$this->assertEquals("VARCHAR", ColumnType::VARCHAR);
		$this->assertEquals("NUMBER", ColumnType::NUMBER);
		$this->assertEquals("TEXT", ColumnType::TEXT);
	}
	// -------------------------------------------------------------------------
}
?>
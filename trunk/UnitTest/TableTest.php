<?php
/**
 * Created on 10-03-2013 16:35:55
 * author Tomasz Gajewski
 * package frontoffice
 * error prefix
 *
 */
require_once __DIR__ . "/../include/PHPDAO.php";
class TableTest extends PHPUnit_Framework_TestCase
{
	// -------------------------------------------------------------------------
	function testSetGet()
	{
		$t = new Table();
		$this->assertNull($t->setName("test"));
		$this->assertNull($t->setSchema("ORA_SCHEMA"));
		$this->assertNull($t->setClassName("Test"));
		$this->assertNull($t->setTableSpace("EN"));
		$this->assertNull($t->setIndexTableSpace("EN_IND"));
		$this->assertNull($t->setErrorPrefix("EN:123"));
		
		$this->assertEquals("test", $t->getName());
		$this->assertEquals("ORA_SCHEMA", $t->getSchema());
		$this->assertEquals("Test", $t->getClassName());
		$this->assertEquals("EN", $t->getTableSpace());
		$this->assertEquals("EN", $t->getTableSpace());
		$this->assertEquals("EN:123", $t->getErrorPrefix());
	}
	// -------------------------------------------------------------------------
	function testAddColumn()
	{
		$t = new Table();
		$this->assertNull($t->addColumn(new Column()));
	}
	// -------------------------------------------------------------------------
	function testGetColumn()
	{
		$t = new Table();
		$t->addColumn(new Column());
		$this->assertInstanceOf("Column", current($t->getColumny()));
	}
	// -------------------------------------------------------------------------
	/**
	 * @expectedException Exception
	 */
	function testSetAutoPrimaryKeyKolumnExcepption()
	{
		$t = new Table();
		$t->addAutoPrimaryColumn();
	}
	// -------------------------------------------------------------------------
	/**
	 * @expectedException Exception
	 */
	function testSetAutoPrimaryKeyKolumnExcepption2()
	{
		$t = new Table();
		$t->setName("a");
		$t->addAutoPrimaryColumn();
	}
	// -------------------------------------------------------------------------
	/**
	 * @expectedException Exception
	 */
	function testSetAutoPrimaryKeyKolumnExcepption3()
	{
		$t = new Table();
		$t->setClassName("bn");
		$t->addAutoPrimaryColumn();
	}
	// -------------------------------------------------------------------------
	function testSetAutoPrimaryKeyKolumn()
	{
		$t = new Table();
		$t->setName("test_table");
		
		$t->setClassName("TestTable");
		$t->addAutoPrimaryColumn();
		
		$this->assertEquals(1, count($t->getColumny()));
		$this->assertInstanceOf("Column", current($t->getColumny()));
	}
	// -------------------------------------------------------------------------
	function testGetPK()
	{
		$t = new Table();
		$t->setName("a");
		$t->setClassName("a");
		$t->addAutoPrimaryColumn();
		$this->assertEquals(1, count($t->getPk()));
	}
	// -------------------------------------------------------------------------
}
?>
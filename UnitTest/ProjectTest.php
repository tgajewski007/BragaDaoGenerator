<?php
/**
 * Created on 10-03-2013 16:17:49
 * author Tomasz Gajewski
 * package frontoffice
 * error prefix
 */
require_once __DIR__ . "/../include/PHPDAO.php";
class ProjectTest extends PHPUnit_Framework_TestCase
{
	// -------------------------------------------------------------------------
	function testVersion()
	{
		$this->assertNotEmpty(Project::VERSION);
	}
	// -------------------------------------------------------------------------
	function testSet()
	{
		$p = new Project();
		$this->assertNull($p->setAuthor("Tomek"));
		$this->assertNull($p->setDaoFolder("dao1"));
		$this->assertNull($p->setProjectFolder("./"));
		$this->assertNull($p->setXmlFile("dao1.xml"));
		$this->assertNull($p->setName("ElektronicznyNadawca"));
		
		$this->assertEquals("Tomek", $p->getAuthor());
		$this->assertEquals("dao1", $p->getDaoFolder());
		$this->assertEquals("./", $p->getProjectFolder());
		$this->assertEquals("dao1.xml", $p->getXmlFile());
		$this->assertEquals("ElektronicznyNadawca", $p->getName());
	}
	// -------------------------------------------------------------------------
	function testAddTable()
	{
		$p = new Project();
		$p->setProjectFolder("O:\\wwwroot\\TestDao\\test1");
		$t = new Table();
		$t->setName("a");
		$this->assertNull($p->addTable($t));
	}
	// -------------------------------------------------------------------------
	function testGetTables()
	{
		$p = new Project();
		$p->setProjectFolder("O:\\wwwroot\\TestDao\\test2");
		$t = new Table();
		$t->setName("a");
		$p->addTable($t);
		$this->assertInstanceOf("Table", current($p->getTables()));
	}
	// -------------------------------------------------------------------------
	function testGenerate()
	{
		$p = new Project();
		$p->setProjectFolder("O:\\wwwroot\\TestDao\\test3");
		$this->assertNull(FilesGenerator::GO($p));
	}
	// -------------------------------------------------------------------------
	function testKeyOfTableList()
	{
		$p = new Project();
		$p->setProjectFolder("O:\\wwwroot\\TestDao\\test4");
		$t = new Table();
		$t->setName("zbior");
		$t->setSchema("ORA_SCHEMA");
		
		$p->addTable($t);
		
		$r = $p->getTables();
		$this->assertTrue(isset($r["ORA_SCHEMA.zbior"]));
		
		$t = new Table();
		$t->setName("zbior");
		$p->addTable($t);
		$r = $p->getTables();
		$this->assertTrue(isset($r["zbior"]));
	}
	// -------------------------------------------------------------------------
	function testGO()
	{
		$t = new Table();
		$t->setName("KLIENT");
		$t->setClassName("Klient");
		$t->setErrorPrefix("EN:017");
		$t->setSchema("ORA_SCHEMA");
		$t->setTableSpace("EN");
		$t->setIndexTableSpace("EN_IND");
		$t->addAutoPrimaryColumn();
		
		$c = new Column();
		$c->setPK();
		$c->setClassFieldName("idKlient2");
		$c->setName("IDKLIENT2");
		$t->addColumn($c);
		
		$c = new Column();
		$c->setClassFieldName("nazwaSkrocona");
		$c->setName("NAZWA_SKROCONA");
		$t->addColumn($c);
		
		$c = new Column();
		$c->setClassFieldName("idKlientMrumc");
		$c->setName("IDKLIENTMRUMC");
		$c->setType(ColumnType::NUMBER);
		$t->addColumn($c);
		
		$c = new Column();
		$c->setClassFieldName("nip");
		$c->setName("NIP");
		$c->setSize("11");
		$t->addColumn($c);
		
		$c = new Column();
		$c->setClassFieldName("wartosc");
		$c->setName("WARTOSC");
		$c->setSize(9);
		$c->setScale(2);
		$c->setType(ColumnType::NUMBER);
		$t->addColumn($c);
		
		$c = new Column();
		$c->setClassFieldName("guid");
		$c->setName("GUID");
		$c->setSize("32");
		$t->addColumn($c);
		
		$c = new Column();
		$c->setClassFieldName("opis");
		$c->setName("OPIS");
		$t->addColumn($c);
		
		$c = new Column();
		$c->setClassFieldName("createDate");
		$c->setName("CREATE_DATE");
		$c->setType(ColumnType::DATE);
		$t->addColumn($c);
		
		$c = new Column();
		$c->setClassFieldName("lastUpdate");
		$c->setName("LAST_UPDATE");
		$c->setType(ColumnType::DATETIME);
		$t->addColumn($c);
		
		$p = new Project();
		$p->setAuthor("Tomasz.Gajewski");
		$p->setDaoFolder("dao");
		$p->setXmlFile("O:\\wwwroot\\TestDao\\test6\\Doc\\dao.xml");
		$p->setProjectFolder("O:\\wwwroot\\TestDao\\test6");
		$p->setName("DAOElektronicznyNadawca");
		$p->addTable($t);
		
		$c1 = new Column();
		$c1->setPK();
		$c1->setName("IDZBIOR");
		$c1->setClassFieldName("idZbior");
		$c1->setType(ColumnType::NUMBER);
		$c1->setSize(22);
		
		$c2 = new Column();
		$c2->setName("NAZWA");
		$c2->setClassFieldName("nazwa");
		$c2->setType(ColumnType::VARCHAR);
		$c2->setSize(255);
		
		$c3 = new Column();
		$c3->setName("NAZWA_LONG");
		$c3->setClassFieldName("nazwa_long");
		$c3->setType(ColumnType::TEXT);
		
		$c4 = new Column();
		$c4->setName("IDKLIENTTEST1");
		$c4->setClassFieldName("idKlientTestowyPierwszy");
		$c4->setType(ColumnType::NUMBER);
		$c4->setSize(22);
		
		$c5 = new Column();
		$c5->setName("IDKLIENTTEST2");
		$c5->setClassFieldName("idKlientTetowyDrugi");
		$c5->setType(ColumnType::NUMBER);
		$c5->setSize(22);
		
		$fk1 = new ForeginKey();
		$fk1->setTableName($t->getName());
		$fk1->setTableSchema($t->getSchema());
		$fk1->addColumn("IDKLIENTTEST1", "IDKLIENT");
		$fk1->addColumn("IDKLIENTTEST2", "IDKLIENT2");
		
		$t = new Table();
		$t->setClassName("Zbior2");
		$t->setName("ZBIOR");
		$t->setErrorPrefix("EN:111");
		$t->setSchema("ORA_SCHEMA");
		$t->setTableSpace("EN");
		$t->setIndexTableSpace("EN_IND");
		$t->addColumn($c1);
		$t->addColumn($c2);
		$t->addColumn($c3);
		$t->addColumn($c4);
		$t->addColumn($c5);
		$t->addFK($fk1);
		
		$p->addTable($t);
		
		FilesGenerator::GO($p);
		
		$this->assertTrue(file_exists("O:\\wwwroot\\TestDao\\test6\\Doc\\dao.xml"));
		$this->assertTrue(file_exists("O:\\wwwroot\\TestDao\\test6\\dao\\KlientDAO.php"));
		include 'O:\\wwwroot\\TestDao\\test6\\dao\\KlientDAO.php';
		$this->assertTrue(file_exists("O:\\wwwroot\\TestDao\\test6\\obj\\Klient.php"));
		include 'O:\\wwwroot\\TestDao\\test6\\obj\\Klient.php';
		
		$t = Klient::get();
		$this->assertInstanceOf("KlientDAO", $t);
		// $this->assertTrue($t->save());
	}
	// -------------------------------------------------------------------------
}
?>
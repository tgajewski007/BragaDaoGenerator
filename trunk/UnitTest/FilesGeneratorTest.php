<?php
/**
 * Created on 10-03-2013 17:26:46
 * author Tomasz Gajewski
 * package frontoffice
 * error prefix
 */
require_once __DIR__ . "/../include/PHPDAO.php";
class FilesGeneratorTest extends PHPUnit_Framework_TestCase
{
	// -------------------------------------------------------------------------
	function testStart()
	{
		$p = new Project();
		$p->setProjectFolder("O:\\wwwroot\\TestDao\\test11");
		$t = new Table();
		$t->setName("a");
		$c = new Column();
		$c->setName("ida");
		$c->setPK(true);
		$p->addTable($t);
		$this->assertNull(FilesGenerator::GO($p));
	}
	// -------------------------------------------------------------------------
	function testStatic()
	{
		$p = new Project();
		$p->setProjectFolder("O:\\wwwroot\\TestDao\\test12");
		$t = new Table();
		$t->setName("a");
		$c = new Column();
		$c->setName("ida");
		$c->setPK(true);
		$p->addTable($t);
		$this->assertClassHasStaticAttribute("project", "FilesGenerator");
		$this->assertAttributeInstanceOf("Project", "project", "FilesGenerator");
	}
	// -------------------------------------------------------------------------
	function testSchemaFile()
	{
		$cu = new Column();
		$cu->setPK();
		// $cu->setAutoGenerate();
		$cu->setName("IDUZYTKOWNIK");
		$cu->setClassFieldName("idUzytkownik");
		$cu->setType(ColumnType::NUMBER);
		$cu->setSize(22);

		$cuName = new Column();
		$cuName->setName("USER_NAME");
		$cuName->setClassFieldName("userName");

		$u = new Table();
		$u->setClassName("Uzytkownik");
		$u->setName("UZYTKOWNIK");
		$u->setErrorPrefix("EN:112");
		$u->setSchema("ORA_SCHEMA");
		$u->setTableSpace("EN");
		$u->setIndexTableSpace("EN_IND");
		$u->addColumn($cu);
		$u->addColumn($cuName);

		$c1 = new Column();
		$c1->setPK();
		$c1->setAutoGenerate();
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
		$c3->setName("IDUZYSZKODNIK");
		$c3->setClassFieldName("idUzyszkodnik");
		$c3->setType(ColumnType::NUMBER);
		$c3->setSize(22);

		$fk1 = new ForeginKey();
		$fk1->setTableName($u->getName());
		$fk1->setTableSchema($u->getSchema());
		$fk1->addColumn("IDUZYSZKODNIK", "IDUZYTKOWNIK");

		$t = new Table();
		$t->setClassName("Zbior");
		$t->setName("ZBIOR");
		$t->setErrorPrefix("EN:111");
		$t->setSchema("ORA_SCHEMA");
		$t->setTableSpace("EN");
		$t->setIndexTableSpace("EN_IND");
		$t->addColumn($c1);
		$t->addColumn($c2);
		$t->addColumn($c3);
		$t->addFK($fk1);

		$przes = new Table();
		$przes->setName("PRZESYLKA");
		$przes->setClassName("Przesylka");
		$przes->addAutoPrimaryColumn();
		$przes->setErrorPrefix("EN:112");
		$przes->setTableSpace("EN");
		$przes->setIndexTableSpace("EN_IND");
		$przes->setSchema("ORA_SCHEMA");

		$pc1 = new Column();
		$pc1->setName("IDZBIOR");
		$pc1->setClassFieldName("idZbior");
		$pc1->setType(ColumnType::NUMBER);
		$pc1->setSize(22);

		$przes->addColumn($pc1);

		$fk2 = new ForeginKey();
		$fk2->setTableName($t->getName());
		$fk2->setTableSchema($t->getSchema());
		$fk2->addColumn("IDZBIOR", "IDZBIOR");
		$przes->addFK($fk2);

		$p = new Project();
		$p->setProjectFolder("O:\\wwwroot\\TestDao\\test13");
		$p->setName("ELektronicznyNadawca");
		$p->addTable($t);
		$p->addTable($u);
		$p->addTable($przes);

		@unlink("O:\\wwwroot\\TestDao\\test13\\dao.xml");

		FilesGenerator::GO($p);
		$this->assertTrue(file_exists("O:\\wwwroot\\TestDao\\test13\\dao.xml"));

		/* @var $doc DOMDocument */
		$doc = DOMDocument::load("O:\\wwwroot\\TestDao\\test13\\dao.xml");
		$this->assertTrue($doc->schemaValidate(__DIR__ . "/../Doc/PHPDAOSchema.xsd"));
	}
	// -------------------------------------------------------------------------
	function testReadProject()
	{
		$content = '<?xml version="1.0" encoding="UTF-8"?>
 				<project name="ELektronicznyNadawca1" dataSourceImplementation="DBEN" namespace="Kaczka" author="">
 					<table schema="ORA_SCHEMA" name="ZBIOR77" className="Zbior" tableSpace="EN" indexTableSpace="EN_IND" errorPrefix="EN:111">
 						<column name="idzbior" classFieldName="idZbior" type="NUMBER" size="22" pk="true" autoGeneratedValue="true" />
 						<column name="idzbior2" classFieldName="idZbior2" type="NUMBER" size="22" pk="false" autoGeneratedValue="false" />
 						<column name="idzbior4" classFieldName="idZbior3" type="NUMBER" size="22" pk="false" autoGeneratedValue="false" />
 						<column name="idzbior5" classFieldName="idZbior3" type="NUMBER" size="22" pk="false" autoGeneratedValue="false" />
 					</table>
 					<table schema="ORA_SCHEMA" name="UZYTKOWNIK77" className="Uzytkownik" tableSpace="EN" indexTableSpace="EN_IND" errorPrefix="EN:112">
 						<column autoGeneratedValue="true" name="iduzytkownik" classFieldName="idUzytkownik" type="NUMBER" size="22"/>
 					</table>
 				</project>';
		@mkdir("O:\\wwwroot\\TestDao\\test14", 0777, true);
		$h = fopen("O:\\wwwroot\\TestDao\\test14\\dao.xml", "w");
		fwrite($h, $content);
		fclose($h);

		$p = new Project();
		$p->setProjectFolder("O:\\wwwroot\\TestDao\\test14");
		$p->setDaoFolder("dao1");
		$this->assertNull(FilesGenerator::GO($p));
		$this->assertEquals(2, count($p->getTables()));
		$this->assertEquals("ELektronicznyNadawca1", $p->getName());
		$this->assertEquals("Kaczka", $p->getNameSpace());

		/* @var $t Table */
		$t = current($p->getTables());
		$this->assertEquals(4, count($t->getColumny()));

		// unlink("O:\\wwwroot\\TestDao\\dao\\dao.xml");
	}
	// -------------------------------------------------------------------------
}
?>
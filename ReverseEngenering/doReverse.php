<?php
/**
 * Created on 07-04-2013 13:19:35
 * author Tomasz Gajewski
 * package frontoffice
 * error prefix
 */
include "../include/PHPDAO.php";
include "ReverseProxy.php";
include "ReverseColumn.php";
include "ReverseTable.php";
include "ReverseForeginKey.php";
include "ReversePrimaryKey.php";
include "ReverseCreator.php";

/*
define("ORA_SERVER", "localhost");
define("ORA_PORT", "1521");
define("ORA_SID", "xe");
define("ORA_USERNAME", "scot");
define("ORA_PASSWORD", "tiger");
define("ORA_SCHEMA", "test_database");
include "oracle/DB.php";
include "oracle/OracleParams.php";
include "oracle/OracleParam.php";
include "oracle/OracleProxy.php";
*/


define("DB_HOST", "localhost");
define("DB_SCHEMA", "test_database");
define("DB_USER", "root");
define("DB_PASS", "1");
include "mysql/DB.php";
include "mysql/MySQLProxy.php";

$folder = "O:\\wwwroot\\TestApp\\common\\";
$project = new Project();
$project->setProjectFolder($folder);
$project->setErrorPrefix("TEST:");
$project->setDataBaseStyle(DataBaseStyle::MYSQL);
$project->setAuthor("Tomasz Gajewski");
$project->setName("TestApp");
$project->setXmlFile("O:\\wwwroot\\TestApp\\Doc\\dao.xml");

$x = new MySQLProxy();
$p = new ReverseCreator($project, $x);
$p->schemaName = "DB_SCHEMA";
$p->GO();


?>
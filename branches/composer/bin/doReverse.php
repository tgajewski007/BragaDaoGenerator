<?php
/**
 * Created on 07-04-2013 13:19:35
 * author Tomasz Gajewski
 * package frontoffice
 * error prefix
 */
include "../include/PHPDAO.php";
include "../../reverse-engeenering/ReverseProxy.php";
include "../../reverse-engeenering/ReverseColumn.php";
include "../../reverse-engeenering/ReverseTable.php";
include "../../reverse-engeenering/ReverseForeginKey.php";
include "../../reverse-engeenering/ReversePrimaryKey.php";
include "../../reverse-engeenering/ReverseCreator.php";

/*
 * define("ORA_SERVER", "localhost");
 * define("ORA_PORT", "1521");
 * define("ORA_SID", "xe");
 * define("ORA_USERNAME", "scot");
 * define("ORA_PASSWORD", "tiger");
 * define("ORA_SCHEMA", "test_database");
 * include "oracle/DB.php";
 * include "oracle/OracleParams.php";
 * include "oracle/OracleParam.php";
 * include "oracle/OracleProxy.php";
 */

/*
 * define("DB_HOST", "localhost");
 * define("DB_NAME", "postgres");
 * define("DB_SCHEMA", "test_schema");
 * define("DB_USER", "postgres");
 * define("DB_PASS", "1");
 * include "pgsql/DB.php";
 * include "pgsql/PostgreProxy.php";
 */

define("DB_HOST", "localhost");
define("DB_SCHEMA", "test_database");
define("DB_USER", "root");
define("DB_PASS", "1");
include "../../reverse-engeenering/mysql/DB.php";
include "../../reverse-engeenering/mysql/MySQLProxy.php";

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
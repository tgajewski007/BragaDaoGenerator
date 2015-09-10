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

define("ORA_SERVER", "10.32.80.71");
define("ORA_PORT", "1521");
define("ORA_SID", "ENDEV");
//define("ORA_USERNAME", "ENDEVBRANCH_CONNECT");
//define("ORA_SCHEMA", "ENDEVBRANCH");
define("ORA_USERNAME", "ENDEVTRUNK_CONNECT");
define("ORA_SCHEMA", "ENDEVTRUNK");
define("ORA_PASSWORD", "1");

include "oracle/DB.php";
include "oracle/OracleParams.php";
include "oracle/OracleParam.php";
include "oracle/OracleProxy.php";


/*
define("DB_HOST", "localhost");
define("DB_SCHEMA", "test_database");
define("DB_USER", "root");
define("DB_PASS", "1");
include "mysql/DB.php";
include "mysql/MySQLProxy.php";
*/

$folder = "O:\\wwwroot\\EN_DAO\\common\\";
$project = new Project();
$project->setProjectFolder($folder);
$project->setErrorPrefix("EN:");
$project->setDataBaseStyle(DataBaseStyle::ORACLE);
$project->setAuthor("PIT/DS/DRSI");
$project->setName("EN");
$project->setXmlFile("O:\\wwwroot\\EN_DAO\\Doc\\dao.xml");

//$x = new MySQLProxy();
$x = new OracleProxy();
$p = new ReverseCreator($project, $x);
$p->schemaName = "ORA_SCHEMA";
$p->GO();


?>
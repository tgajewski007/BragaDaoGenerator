<?php
/**
 * Created on 07-04-2013 13:19:35
 * author Tomasz Gajewski
 * package frontoffice
 * error prefix
 */
$folder = "O:\\wwwroot\\ENNEW\\dev\\daoGen\\";
$dbFolder = "db";
$daoFolder = "db\\dao";
$objFolder = "obj_dao";
$errorPrefix = "DB:";
$author = "EN Team";
$name = "EN";
$xmlFile = $folder . "dao.xml";
$nameSpace = "pp\\en";
// ------------------------------------------------------------------------------
define("FORCE_GEN_CLASS", true);
define("FORCE_GEN_DB", false);
define("FORCE_GEN_DAO", false);
// ------------------------------------------------------------------------------

define("ORA_SOURCE", "TRUNK"); // BRANCH|TRUNK
define("ORA_SERVER", "10.32.80.71");
define("ORA_PORT", "1521");
define("ORA_SID", "ENDEV");
define("ORA_USERNAME", "ENDEV" . ORA_SOURCE . "_CONNECT");
define("ORA_SCHEMA", "ENDEV" . ORA_SOURCE);
define("ORA_PASSWORD", "1");

/*
 * define("ORA_SOURCE", "ENMG");
 * define("ORA_SERVER", "127.0.0.1");
 * define("ORA_PORT", "1521");
 * define("ORA_SID", "XE");
 * define("ORA_USERNAME", ORA_SOURCE . "_CONNECT");
 * define("ORA_SCHEMA", ORA_SOURCE);
 * define("ORA_PASSWORD", "1");
 */

// ------------------------------------------------------------------------------

include dirname(__DIR__) . "/include/PHPDAO.php";
include __DIR__ . "/ReverseProxy.php";
include __DIR__ . "/ReverseColumn.php";
include __DIR__ . "/ReverseTable.php";
include __DIR__ . "/ReverseForeignKey.php";
include __DIR__ . "/ReversePrimaryKey.php";
include __DIR__ . "/ReverseCreator.php";

include __DIR__ . "/oracle/DB.php";
include __DIR__ . "/oracle/OracleParams.php";
include __DIR__ . "/oracle/OracleParam.php";
include __DIR__ . "/oracle/OracleProxy.php";

$project = new Project();
$project->setProjectFolder($folder);
$project->setNameSpace($nameSpace);
$project->setXmlFile($xmlFile);
$project->setDbFolder($dbFolder);
$project->setDaoFolder($daoFolder);
$project->setObjFolder($objFolder);
$project->setErrorPrefix($errorPrefix);
$project->setAuthor($author);
$project->setName($name);
$project->setDataBaseStyle(DataBaseStyle::ORACLE);

// $x = new MySQLProxy();
$x = new OracleProxy();
$p = new ReverseCreator($project, $x);
$p->schemaName = "ORA_SCHEMA";
$p->GO();

?>
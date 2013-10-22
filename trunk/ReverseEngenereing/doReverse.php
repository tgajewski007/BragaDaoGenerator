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

// define("ORA_SERVER", "10.32.80.11");
// define("ORA_PORT", "1521");
// define("ORA_SID", "ORA11");
// define("ORA_USERNAME", "EN");
// define("ORA_PASSWORD", "1");
// define("ORA_SCHEMA", "EN");
// include "oracle/DB.php";
// include "oracle/OracleParams.php";
// include "oracle/OracleParam.php";
// include "oracle/OracleProxy.php";

/*
define("DB_HOST", "localhost");
define("DB_SCHEMA", "trans");
define("DB_USER", "root");
define("DB_PASS", "1");
include "mysql/DB.php";
include "mysql/MySQLProxy.php";

$folder = "O:\\wwwroot\\TransSped\\include\\";
$project = new Project();
$project->setProjectFolder($folder);
$project->setErrorPrefix("TS:");
$project->setDataBaseStyle(DataBaseStyle::MYSQL);
$project->setAuthor("Tomasz Gajewski");
$project->setName("EnMarket");
$project->setXmlFile("O:\\wwwroot\\TransSped\\Doc\\dao.xml");

$x = new MySQLProxy();
$p = new ReverseCreator($project, $x);
$p->schemaName = "DB_SCHEMA";
$p->indexTableSpace = "EN_IND";
$p->GO();
 */

/*
define("DB_HOST", "localhost");
define("DB_SCHEMA", "tomurb02_enmarke");
define("DB_USER", "root");
define("DB_PASS", "1");
include "mysql/DB.php";
include "mysql/MySQLProxy.php";

$folder = "O:\\wwwroot\\EnMarket\\common\\";
$project = new Project();
$project->setProjectFolder($folder);
$project->setErrorPrefix("EM:");
$project->setDataBaseStyle(DataBaseStyle::MYSQL);
$project->setAuthor("Tomasz Gajewski");
$project->setName("EnMarket");
$project->setXmlFile("O:\\wwwroot\\EnMarket\\Doc\\dao.xml");

$x = new MySQLProxy();
$p = new ReverseCreator($project, $x);
$p->schemaName = "DB_SCHEMA";
$p->indexTableSpace = "EN_IND";
$p->GO();
*/
define("DB_HOST", "localhost");
define("DB_SCHEMA", "parkmar");
define("DB_USER", "root");
define("DB_PASS", "1");
include "mysql/DB.php";
include "mysql/MySQLProxy.php";

$folder = "O:/wwwroot/ParkMar/include/";
$project = new Project();
$project->setProjectFolder($folder);
$project->setErrorPrefix("PM:");
$project->setDataBaseStyle(DataBaseStyle::MYSQL);
$project->setAuthor("Tomasz Gajewski");
$project->setName("ParkMar");
$project->setXmlFile("O:/wwwroot/ParkMar/Doc/dao.xml");

$x = new MySQLProxy();
$p = new ReverseCreator($project, $x);
$p->schemaName = "DB_SCHEMA";
$p->indexTableSpace = "EN_IND";
$p->GO();
// $folder = "O:\\wwwroot\\TransSped\\include\\";
// $project = new Project();
// $project->setProjectFolder($folder);
// $project->setErrorPrefix("TS:");
// $project->setDataBaseStyle(DataBaseStyle::MYSQL);
// $project->setAuthor("Tomasz Gajewski");
// $project->setName("TransSped");
// $project->setXmlFile("O:\\wwwroot\\TransSped\\Doc\\dao.xml");

// $x = new MySQLProxy();
// $p = new ReverseCreator($project, $x);
// $p->schemaName = "DB_SCHEMA";
// $p->indexTableSpace = "EN_IND";
// $p->GO();

?>
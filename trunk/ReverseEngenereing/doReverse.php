<?php
/**
 * Created on 07-04-2013 13:19:35
 * author Tomasz Gajewski
 * package frontoffice
 * error prefix
 *
 */

define("ORA_SERVER", "10.32.80.11");
define("ORA_PORT", "1521");
define("ORA_SID", "ORA11");
define("ORA_USERNAME", "EN");
define("ORA_PASSWORD", "1");
define("ORA_SCHEMA", "EN");

include "../include/PHPDAO.php";
include "ReverseProxy.php";
include "ReverseColumn.php";
include "ReverseTable.php";
include "ReverseForeginKey.php";
include "ReversePrimaryKey.php";
include "ReverseCreator.php";
include "oracle/DB.php";
include "oracle/OracleParams.php";
include "oracle/OracleParam.php";
include "oracle/OracleProxy.php";


$folder = "O:\\wwwroot\\TestDao\\en\\include";
$project = new Project();
$project->setProjectFolder($folder);
$project->setErrorPrefix("EN:");
$project->setNameSpace("ENadawca");
$x = new OracleProxy();
$p = new ReverseCreator($project, $x);
$p->schemaName = "ORA_SCHEMA";
$p->indexTableSpace = "EN_IND";
$p->GO();

?>
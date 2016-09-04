<?php
use braga\daogenerator\generator\Project;
use braga\daogenerator\worker\ReverseCreator;
use braga\daogenerator\worker\ConfigReader;

/**
 * Created on 07-04-2013 13:19:35
 * author Tomasz Gajewski
 * package frontoffice
 * error prefix
 */

$autoloadFiles = array(
		__DIR__ . '/../vendor/autoload.php',
		__DIR__ . '/../../../autoload.php');

foreach($autoloadFiles as $autoloadFile)
{
	if(file_exists($autoloadFile))
	{
		require_once $autoloadFile;
	}
}

$c = ConfigReader::readConfig();

define("DB_CONNECTION_STRING", $c->getConnectonString());
define("DB_USER", $c->getUser());
define("DB_PASS", $c->getPass());
define("DB_SCHEMA", $c->getSchema());

$project = new Project();
$project->setProjectFolder($c->getProjectFolder());
$project->setErrorPrefix($c->getErrorPrefix());
$project->setDataBaseStyle($c->getDataBaseStyle());
$project->setAuthor($c->getAutor());
$project->setName($c->getProjectName());
$project->setXmlFile($c->getXmlFile());
$project->setNameSpace($c->getNameSpace());

$x = ConfigReader::getProxy();
$p = new ReverseCreator($project, $x);
$p->schemaName = "DB_SCHEMA";
$p->GO();

?>
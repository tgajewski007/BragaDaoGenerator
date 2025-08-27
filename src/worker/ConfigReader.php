<?php
namespace braga\daogenerator\worker;
use braga\daogenerator\worker\mysql\MySQLProxy;
use braga\daogenerator\worker\oracle\OracleProxy;
use braga\daogenerator\worker\pgsql\PostgreProxy;
use braga\daogenerator\generator\DataBaseStyle;

/**
 * Created on 04.09.2016 15:56:45
 * error prefix
 * @author Tomasz Gajewski
 * @package
 *
 */
class ConfigReader
{
	// -----------------------------------------------------------------------------------------------------------------
	protected static $instance = null;
	// -----------------------------------------------------------------------------------------------------------------
	protected $connectonString;
	protected $user;
	protected $pass;
	protected $schema;
	protected $autor;
	protected $outputFolder;
	protected $configFolder;
	protected $errorPrefix;
	protected $dbDriver;
	protected $projectName;
	protected $namespace;
	protected $dbName;
	protected $baseFolder;
	// -----------------------------------------------------------------------------------------------------------------
	/**
	 *
	 * @return mixed
	 */
	public function getDbName()
	{
		return $this->dbName;
	}
	// -----------------------------------------------------------------------------------------------------------------
	/**
	 *
	 * @param mixed $dbName
	 */
	public function setDbName($dbName)
	{
		$this->dbName = $dbName;
	}
	// -----------------------------------------------------------------------------------------------------------------
	public function getConnectonString()
	{
		return $this->connectonString;
	}
	// -----------------------------------------------------------------------------------------------------------------
	public function setConnectonString($connectonString)
	{
		$this->connectonString = $connectonString;
	}
	// -----------------------------------------------------------------------------------------------------------------
	public function getUser()
	{
		return $this->user;
	}
	// -----------------------------------------------------------------------------------------------------------------
	public function setUser($user)
	{
		$this->user = $user;
	}
	// -----------------------------------------------------------------------------------------------------------------
	public function getPass()
	{
		return $this->pass;
	}
	// -----------------------------------------------------------------------------------------------------------------
	public function setPass($pass)
	{
		$this->pass = $pass;
	}
	// -----------------------------------------------------------------------------------------------------------------
	public function getSchema()
	{
		return $this->schema;
	}
	// -----------------------------------------------------------------------------------------------------------------
	public function setSchema($schema)
	{
		$this->schema = $schema;
	} // -----------------------------------------------------------------------------------------------------------------
	public function getAutor()
	{
		return $this->autor;
	} // -----------------------------------------------------------------------------------------------------------------
	public function setAutor($autor)
	{
		$this->autor = $autor;
	}
	// -----------------------------------------------------------------------------------------------------------------
	public function getOutputFolder()
	{
		return $this->outputFolder;
	}
	// -----------------------------------------------------------------------------------------------------------------
	public function setOutputFolder($outputFolder)
	{
		$this->outputFolder = $outputFolder;
	}
	// -----------------------------------------------------------------------------------------------------------------
	public function getConfigFolder()
	{
		return $this->configFolder;
	}
	// -----------------------------------------------------------------------------------------------------------------
	public function setConfigFolder($configFolder)
	{
		$this->configFolder = $configFolder;
	}
	// -----------------------------------------------------------------------------------------------------------------
	public function getErrorPrefix()
	{
		return $this->errorPrefix;
	}
	// -----------------------------------------------------------------------------------------------------------------
	public function setErrorPrefix($errorPrefix)
	{
		$this->errorPrefix = $errorPrefix;
	}
	// -----------------------------------------------------------------------------------------------------------------
	public function getDbDriver()
	{
		return $this->dbDriver;
	}
	// -----------------------------------------------------------------------------------------------------------------
	public function setDbDriver($dbDriver)
	{
		$this->dbDriver = $dbDriver;
	}
	// -----------------------------------------------------------------------------------------------------------------
	public function getProjectName()
	{
		return $this->projectName;
	}
	// -----------------------------------------------------------------------------------------------------------------
	public function setProjectName($projectName)
	{
		$this->projectName = $projectName;
	}
	// -----------------------------------------------------------------------------------------------------------------
	public function getNamespace()
	{
		return $this->namespace;
	}
	// -----------------------------------------------------------------------------------------------------------------
	public function setNamespace($namespace)
	{
		$this->namespace = $namespace;
		return $this;
	}
	// -----------------------------------------------------------------------------------------------------------------
	public function getBaseFolder()
	{
		return $this->baseFolder;
	}
	// -----------------------------------------------------------------------------------------------------------------
	public function setBaseFolder($baseFolder)
	{
		$this->baseFolder = $baseFolder;
	}
	// -----------------------------------------------------------------------------------------------------------------
	public function getXmlFile()
	{
		return $this->getBaseFolder() . DIRECTORY_SEPARATOR . $this->getConfigFolder() . DIRECTORY_SEPARATOR . "dao.xml";
	}
	// -----------------------------------------------------------------------------------------------------------------
	public function getProjectFolder()
	{
		return $this->getBaseFolder() . DIRECTORY_SEPARATOR . $this->getOutputFolder() . DIRECTORY_SEPARATOR;
	}
	// -----------------------------------------------------------------------------------------------------------------
	/**
	 *
	 * @return ReverseProxy
	 */
	public static function getProxy()
	{
		switch(self::readConfig()->getDbDriver())
		{
			case "oracle":
				return new OracleProxy();
			case "pgsql":
				return new PostgreProxy();
			case "mysql":
			default :
				return new MySQLProxy();
		}
	}
	// -----------------------------------------------------------------------------------------------------------------
	public function getDataBaseStyle()
	{
		switch(self::readConfig()->getDbDriver())
		{
			case "oracle":
				return DataBaseStyle::ORACLE;
			case "pgsql":
				return DataBaseStyle::PGSQL;
			case "mysql":
			default :
				return DataBaseStyle::MYSQL;
		}
	}
	// -----------------------------------------------------------------------------------------------------------------
	public static function readConfig()
	{
		if(empty(self::$instance))
		{
			$configFilenameArray = array();
			$configFilenameArray[] = "";
			$configFilenameArray[] = ".." . DIRECTORY_SEPARATOR;
			$configFilenameArray[] = ".." . DIRECTORY_SEPARATOR . ".." . DIRECTORY_SEPARATOR;
			$configFilenameArray[] = ".." . DIRECTORY_SEPARATOR . ".." . DIRECTORY_SEPARATOR . ".." . DIRECTORY_SEPARATOR;
			$configFilenameArray[] = ".." . DIRECTORY_SEPARATOR . ".." . DIRECTORY_SEPARATOR . ".." . DIRECTORY_SEPARATOR . ".." . DIRECTORY_SEPARATOR;
			$configFilenameArray[] = ".." . DIRECTORY_SEPARATOR . ".." . DIRECTORY_SEPARATOR . ".." . DIRECTORY_SEPARATOR . ".." . DIRECTORY_SEPARATOR . ".." . DIRECTORY_SEPARATOR;
			$configFilenameArray[] = ".." . DIRECTORY_SEPARATOR . ".." . DIRECTORY_SEPARATOR . ".." . DIRECTORY_SEPARATOR . ".." . DIRECTORY_SEPARATOR . ".." . DIRECTORY_SEPARATOR . ".." . DIRECTORY_SEPARATOR;
			foreach($configFilenameArray as $c)
			{
				$realpath = realpath(__DIR__ . DIRECTORY_SEPARATOR . $c);
				$filename = $realpath . DIRECTORY_SEPARATOR . "dbConfig.json";
				if(file_exists($filename))
				{
					self::$instance = new self();
					$content = file_get_contents($filename);
					$content = json_decode($content, true);
					self::$instance->setBaseFolder($realpath);
					self::$instance->setUser($content["user"]);
					self::$instance->setPass($content["pass"]);
					self::$instance->setSchema($content["schema"]);
					self::$instance->setConnectonString($content["connection-string"]);
					self::$instance->setDbName(@$content["db-name"]);

					self::$instance->setAutor($content["author"]);
					self::$instance->setOutputFolder($content["output-folder"]);
					self::$instance->setConfigFolder($content["dao.xml-folder"]);
					self::$instance->setErrorPrefix($content["error-prefix"]);
					self::$instance->setDbDriver($content["db-driver"]);
					self::$instance->setProjectName($content["project-name"]);
					self::$instance->setNamespace($content["namespace"]);
					return self::$instance;
				}
			}
			throw new \Exception("dbConfig.json file not found", 1);
		}
		return self::$instance;
	}
	// -----------------------------------------------------------------------------------------------------------------
}
?>
<?php
/**
 * create 29-05-2012 07:48:24
 *
 * @author Tomasz Gajewski
 * @package common
 */
class DB implements DataSource
{
	// -------------------------------------------------------------------------
	/**
	 *
	 * @var PDO
	 */
	protected static $connectionObject = null;
	/**
	 *
	 * @var boolean
	 */
	protected $transaction = true;
	/**
	 *
	 * @var PDOStatement
	 */
	protected $statement = null;
	protected $params = null;
	protected $row = null;
	protected $rowAffected = 0;
	protected $lastQuery = null;
	protected $orginalQuery = null;
	protected $limit = null;
	protected $offset = null;
	/**
	 *
	 * @var boolean
	 */
	protected static $inTransaction = false;
	// -------------------------------------------------------------------------
	function __construct($transaction = true)
	{
		$this->transaction = $transaction;
		$this->params = array();
	}
	// -------------------------------------------------------------------------
	/**
	 *
	 * @return boolean
	 */
	public function query($sql)
	{
		$this->lastQuery = $sql;
		try
		{
			if($this->connect())
			{
				if($this->prepare())
				{
					if($this->statement->execute($this->params))
					{
						if(strtoupper(substr($this->lastQuery, 0, 1)) == "S")
						{
							$this->setMetaData();
						}
						else
						{
							$this->rowAffected = $this->statement->rowCount();
						}
						return true;
					}
					else
					{
						$errors = $this->statement->errorInfo();
						return false;
					}
				}
				else
				{
					return false;
				}
			}
			else
			{
				return false;
			}
		}
		catch(Exception $e)
		{
			return false;
		}
	}
	// -------------------------------------------------------------------------
	protected function setMetaData()
	{
	}
	// -------------------------------------------------------------------------
	/**
	 *
	 * @return boolean
	 */
	protected function prepare()
	{
		$this->orginalQuery = $this->lastQuery;
		if(!is_null($this->limit))
		{
			$this->lastQuery .= " LIMIT " . $this->offset . ", " . $this->limit;
		}
		$this->statement = self::$connectionObject->prepare($this->lastQuery, array(
				PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY 
		));
		if($this->statement !== false)
		{
			return true;
		}
		else
		{
			return true;
		}
	}
	// -------------------------------------------------------------------------
	static function getCount(DB $countingDB)
	{
		$sql = "SELECT Count(*) FROM (" . $countingDB->orginalQuery . ") t ";
		$db = new DB();
		$db->params = $countingDB->params;
		$db->query($sql);
		if($db->nextRecord())
		{
			return $db->f(0);
		}
		else
		{
			return 0;
		}
	}
	// -------------------------------------------------------------------------
	public function setLimit($offset, $limit = PAGELIMIT)
	{
		$this->offset = intval($offset);
		$this->limit = intval($limit);
	}
	// -------------------------------------------------------------------------
	/**
	 *
	 * @return boolean
	 */
	public function nextRecord()
	{
		$this->row = $this->statement->fetch(PDO::FETCH_BOTH);
		if($this->row !== false)
		{
			return true;
		}
		else
		{
			return false;
		}
	}
	// -------------------------------------------------------------------------
	public function f($fieldIndex)
	{
		if(isset($this->row[$fieldIndex]))
		{
			return $this->row[$fieldIndex];
		}
		else
		{
			return null;
		}
	}
	// -------------------------------------------------------------------------
	public function setParam($name, $value, $clear = false)
	{
		if($clear)
		{
			$this->params = array();
		}
		$this->params[":" . $name] = $value;
	}
	// -------------------------------------------------------------------------
	public function commit()
	{
		if(self::$inTransaction)
		{
			self::$inTransaction = false;
			return self::$connectionObject->commit();
		}
		else
		{
			return true;
		}
	}
	// -------------------------------------------------------------------------
	public function rollback()
	{
		if(self::$inTransaction)
		{
			self::$inTransaction = false;
			return self::$connectionObject->rollback();
		}
		else
		{
			return true;
		}
	}
	// -------------------------------------------------------------------------
	public function getRowAffected()
	{
		return $this->rowAffected;
	}
	// -------------------------------------------------------------------------
	/**
	 *
	 * @return array zwaraca tablice DataSourceMetaData informacje o recordSecie
	 */
	public function getMetaData()
	{
		return null;
	}
	// -------------------------------------------------------------------------
	/**
	 *
	 * @return int
	 */
	public function getLastInsertID()
	{
		return self::$connectionObject->lastInsertId();
	}
	// -------------------------------------------------------------------------
	public function setFetchMode($fetchMode)
	{
		$this->fetchMode = $fetchMode;
	}
	// -------------------------------------------------------------------------
	/**
	 *
	 * @return boolean
	 */
	protected function connect()
	{
		if(empty(self::$connectionObject))
		{
			self::$connectionObject = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_SCHEMA . "", DB_USER, DB_PASS);
			self::$connectionObject->query("SET NAMES utf8 COLLATE 'utf8_polish_ci'");
		}
		if($this->transaction)
		{
			if(!self::$inTransaction)
			{
				self::$connectionObject->beginTransaction();
				self::$inTransaction = true;
			}
		}
		else
		{
			if(self::$inTransaction)
			{
				$this->commit();
				self::$inTransaction = false;
			}
		}
		
		return true;
	}
	// ------------------------------------------------------------------------
	public function count()
	{
		return self::getCount($this);
	}
	// -------------------------------------------------------------------------
	static function getParameName($length = 8)
	{
		return "P" . strtoupper(getRandomStringLetterOnly($length));
	}
	// -------------------------------------------------------------------------
}
?>
<?php
/**
 *
 * @package common
 * @author Tomasz.Gajewski
 * Created on 2006-03-27
 * klasa zapewniająca łączność z bazą danych Oracle
 * error prefix EN:016
 */
define("ORACLE_DATE_FORMAT", "YYYY-MM-DD");
define("ORACLE_DATETIME_FORMAT", "YYYY-MM-DD HH24:MI:SS");
class DB implements DataSource
{
	protected $Serwer = ORA_SERVER;
	protected $Port = ORA_PORT;
	protected $SID = ORA_SID;
	public $UserName = ORA_USERNAME;
	public $Password = ORA_PASSWORD;
	public $Debug = false;
	public $Error = "";
	public $MetaData;
	public $RowCount;
	protected $connectionObiect;
	protected $IsConnected;
	protected $RecordSet;
	protected $Row;
	protected $RowNum;
	protected $Param;
	protected $WorkTime = 0;
	protected $trasactionMode = OCI_COMMIT_ON_SUCCESS;
	protected $queryStr = null;
	// ------------------------------------------------------------------------
	protected $oryginalQueryString = null;
	// ------------------------------------------------------------------------
	protected $startFrom = null;
	protected $limit = null;
	// ------------------------------------------------------------------------
	public function getConnectionObject()
	{
		return $this->connectionObiect;
	}
	// ------------------------------------------------------------------------
	public function getRowAffected()
	{
		return $this->RowCount;
	}
	// ------------------------------------------------------------------------
	public function __construct($trasactionOn = true)
	{
		$this->Param = new OracleParams();
		if($trasactionOn)
			$this->trasactionMode = OCI_DEFAULT;

		putenv("NLS_LANG=Polish_Poland.UTF8");
		$this->checkConnection();
		$this->Database = "(DESCRIPTION = (ADDRESS = (PROTOCOL = TCP)(HOST = " . $this->Serwer . ")(PORT = " . $this->Port . ")(CONNECT_DATA = (SID = " . $this->SID . ")))";
	}
	// ------------------------------------------------------------------------
	public function setLimit($arg1, $arg2 = null)
	{
		if(null == $arg2)
		{
			$this->limit = $arg1;
		}
		else
		{
			$this->limit = $arg2;
			$this->startFrom = $arg1;
		}
	}
	// ------------------------------------------------------------------------
	public function checkConnection()
	{
		global $ORACONNECTION;
		if(isset($ORACONNECTION))
		{
			if(is_resource($ORACONNECTION))
			{
				$this->connectionObiect = $ORACONNECTION;
				$this->IsConnected = true;
			}
			else
			{
				$this->IsConnected = false;
			}
		}
		else
		{
			$this->IsConnected = false;
		}
	}
	// ------------------------------------------------------------------------
	public function connect()
	{
		global $ORACONNECTION;
		$this->checkConnection();
		if($this->IsConnected)
		{
			return true;
		}
		$this->connectionObiect = @oci_pconnect($this->UserName, $this->Password, $this->Database, 'UTF8');
		if(!$this->connectionObiect)
		{
			$this->getOciErrors("Błąd połączenia");
			return false;
		}
		else
		{
			$ORACONNECTION = $this->connectionObiect;
			$this->IsConnected = true;
			$SQL = "ALTER SESSION SET NLS_DATE_FORMAT = '" . ORACLE_DATE_FORMAT . "'";
			$this->fastQuery($SQL);
			$SQL = "ALTER SESSION SET NLS_TIMESTAMP_FORMAT = '" . ORACLE_DATETIME_FORMAT . "'";
			$this->fastQuery($SQL);
			$SQL = "ALTER SESSION SET NLS_NUMERIC_CHARACTERS = '.`'";
			$this->fastQuery($SQL);
			return true;
		}
	}
	// ------------------------------------------------------------------------
	public function commit()
	{
		@oci_commit($this->connectionObiect);
	}
	// ------------------------------------------------------------------------
	public function rollback()
	{
		@oci_rollback($this->connectionObiect);
	}
	// -------------------------------------------------------------------------
	protected function fastQuery($SQLqueryString)
	{
		$this->RecordSet = @oci_parse($this->connectionObiect, $SQLqueryString);
		if($this->RecordSet)
		{
			if(@oci_execute($this->RecordSet, OCI_COMMIT_ON_SUCCESS))
			{
				return true;
			}
			else
			{
				return false;
			}
		}
	}
	// -------------------------------------------------------------------------
	/**
	 * query
	 * Funkcja wykonuje właściwe zapytanie do bazy danych
	 *
	 * @return bool true jeżeli ok false jeżeli zapytanie kończy się błędem
	 * @var string $SQLqueryString
	 * @var bool $GetMetaData zmienna określająca czy pobierane będą metadane z
	 * zapytania SQL
	 */
	public function query($SQLqueryString, $GetMetaData = true, $Mode = null)
	{
		if(null == $Mode)
		{
			$Mode = $this->trasactionMode;
		}

		$this->queryStr = $SQLqueryString;
		$this->oryginalQueryString = $SQLqueryString;
		if(null !== $this->limit)
		{
			if(null !== $this->startFrom)
			{
				$this->queryStr = "SELECT * FROM (SELECT regular.*, ROWNUM db_numer_recordu FROM (" . $this->queryStr . ") regular ) WHERE db_numer_recordu BETWEEN :REC_LIMIT_FROM AND :REC_LIMIT_TO ";
				$this->setParam("REC_LIMIT_FROM", $this->startFrom + 1);
				$this->setParam("REC_LIMIT_TO", $this->startFrom + $this->limit);
			}
			else
			{
				$this->queryStr = "SELECT * FROM (SELECT regular.*, ROWNUM db_numer_recordu FROM (" . $this->queryStr . ") regular ) WHERE db_numer_recordu <= :REC_LIMIT_FROM  ";
				$this->setParam("REC_LIMIT_FROM", $this->limit);
			}
		}

		if($this->connect())
		{
			$this->RecordSet = @oci_parse($this->connectionObiect, $this->queryStr);
			$this->addParam();
			if($this->RecordSet)
			{
				if(@oci_execute($this->RecordSet, $Mode))
				{
					if($GetMetaData)
					{
						$this->setMetaData();
					}
					$this->RowCount = @oci_num_rows($this->RecordSet);
					return true;
				}
				else
				{
					$this->getOciErrors("Błąd wykonania", $this->queryStr);
					return false;
				}
			}
			else
			{
				$this->getOciErrors("Błąd wykonania", $this->queryStr);
				return false;
			}
		}
		else
		{
			return false;
		}
	}
	// ------------------------------------------------------------------------
	public function clearParam()
	{
		$this->Param->clear();
	}
	// ------------------------------------------------------------------------
	protected function addParam()
	{
		$this->Param->bind($this->RecordSet);
	}
	// ------------------------------------------------------------------------
	public function setParam($name, $value = "", $clear = false, $length = -1, $type = SQLT_CHR)
	{
		if($clear)
			$this->Param->clear();
		$this->Param->add($name, new OracleParam($this->connectionObiect, $value, $length, $type));
	}
	// ------------------------------------------------------------------------
	public function getParam($name)
	{
		return $this->Param->get($name);
	}
	// ------------------------------------------------------------------------
	protected function getOciErrors($Error, $SQL = "")
	{
		$retval = $Error . "<br>\n";
		$this->RowCount = 0;
		if(is_resource($this->connectionObiect))
			$tmp = @oci_error($this->connectionObiect);
		else
			$tmp = @oci_error();

		if($SQL != "")
		{
			if(substr($SQL, 0, 10) != "ALTER USER")
			{
				$retval .= "SQL: <br>\n" . "<code>$SQL</code><br>\n";
				$retval .= "<p>PARAM:" . "<pre>" . var_export($this->Param, true) . "" . "</pre><p>";
			}
		}
		if(!is_array($tmp))
			$tmp = (error_get_last());
		$this->Error = $tmp;
		if(is_array($tmp))
		{
			$a = mb_strpos($tmp["message"], ":");
			if($a !== false)
			{
				$tmp["message"] = mb_substr($tmp["message"], $a + 1);
			}
			$retval .= $tmp["message"] . " <br>\n";
		}
		else
		{
			$retval .= "<b>Brak opisu błędu</b><br>";
		}
	}
	// ------------------------------------------------------------------------
	protected function setMetaData()
	{
		$max = oci_num_fields($this->RecordSet);
		if(null !== $this->limit)
			$max--;
		$this->MetaData["FieldNum"] = $max;
		$this->MetaData["RecCount"] = @oci_num_rows($this->RecordSet);
		for($i = 1;$i <= $max;$i++)
		{
			$tmp = @oci_field_name($this->RecordSet, $i);
			$this->MetaData["Name"][$i - 1] = $tmp;

			if(strtolower(mb_substr($this->MetaData["Name"][$i - 1], -3, 3, "UTF-8")) != "_nn")
			{
				$this->MetaData["Flags"][$i - 1] = "null";
			}
			else
			{
				$this->MetaData["Flags"][$i - 1] = "not null";
			}
			if(strtolower(mb_substr($this->MetaData["Name"][$i - 1], -3, 3, "UTF-8")) == "_ro")
			{
				$this->MetaData["Flags"][$i - 1] .= " read only";
			}
			$this->MetaData["Len"][$i - 1] = @oci_field_size($this->RecordSet, $i);
			$this->MetaData["Prec"][$i - 1] = @oci_field_precision($this->RecordSet, $i);
			$this->MetaData["Type"][$i - 1] = @oci_field_type($this->RecordSet, $i);
			$this->MetaData["Scale"][$i - 1] = @oci_field_scale($this->RecordSet, $i);
		}
	}
	// ------------------------------------------------------------------------
	public function nextRecord()
	{
		global $SumaryFetchTime;
		if($this->connect())
		{
			$this->Row = @oci_fetch_array($this->RecordSet, OCI_BOTH + OCI_RETURN_NULLS);
			if(is_array($this->Row))
			{
				return true;
			}
			else
			{
				return false;
			}
		}
	}
	// ------------------------------------------------------------------------
	public function field($FieldName)
	{
		return @$this->Row[$FieldName];
	}
	// ------------------------------------------------------------------------
	public function f($FieldName)
	{
		// alias funkcji Field
		return $this->field($FieldName);
	}
	// ------------------------------------------------------------------------
	static function getCount(DB $db)
	{
		$dbCount = new DB();
		$SQL = "SELECT Count(*) " . "FROM (" . $db->oryginalQueryString . ") ";
		$dbCount->Param = $db->Param;
		$dbCount->query($SQL);
		if($dbCount->nextRecord())
		{
			$ilosc = $dbCount->f(0);
		}
		else
		{
			$ilosc = 0;
		}
		return $ilosc;
	}
	// ------------------------------------------------------------------------
	public function count()
	{
		return self::getCount($this);
	}
	// ------------------------------------------------------------------------
	public function getParamNameGenerated($dlugosc = 8)
	{
		$p = "P" . strtoupper(RandomStringLetterOnly($dlugosc - 1));
		return $p;
	}
	// ------------------------------------------------------------------------
}
?>
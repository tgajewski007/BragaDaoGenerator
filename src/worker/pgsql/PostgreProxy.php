<?php
namespace braga\daogenerator\worker\pgsql;
use braga\daogenerator\worker\ReverseColumn;
use braga\daogenerator\worker\ReverseProxy;
use braga\daogenerator\worker\ReverseTable;
use braga\daogenerator\generator\ColumnType;
use braga\daogenerator\worker\ReversePrimaryKey;
use braga\daogenerator\worker\ReverseForeginKey;
use braga\daogenerator\generator\ConnectedColumn;
use braga\db\pgsql\DB;

/**
 * Created on 9 lip 2013 18:49:34
 * author Tomasz Gajewski
 * package frontoffice
 * error prefix
 */
class PostgreProxy implements ReverseProxy
{
	// -------------------------------------------------------------------------
	public function getTables()
	{
		$db = new DB();
		$sql = "SELECT table_name, table_schema ";
		$sql .= ",(SELECT Count(*) FROM INFORMATION_SCHEMA.COLUMNS c WHERE t.TABLE_CATALOG = c.TABLE_CATALOG AND t.TABLE_SCHEMA = c.TABLE_SCHEMA AND t.TABLE_NAME =c.TABLE_NAME  AND column_default LIKE 'nextval%'  ) ";
		$sql .= "FROM INFORMATION_SCHEMA.TABLES t ";
		$sql .= "WHERE TABLE_CATALOG = :TABLE_CATALOG ";
		$sql .= "AND TABLE_SCHEMA = :TABLE_SCHEMA ";
		$sql .= "ORDER BY TABLE_NAME ";
		$db->setParam("TABLE_CATALOG", DB_NAME);
		$db->setParam("TABLE_SCHEMA", DB_SCHEMA);
		echo "selecting tables ....\n";
		$db->query($sql);
		$retval = array();
		while($db->nextRecord())
		{
			$tmp = new ReverseTable();
			$tmp->tableName = $db->f(0);
			$tmp->tableSpace = $db->f(1);
			$tmp->haveAutoNumberPKField = ($db->f(2) > 0);
			$retval[] = $tmp;
		}
		return $retval;
	}
	// -------------------------------------------------------------------------
	public function getColumn($tableName)
	{
		$db = new DB();

		$sql = <<<SQL
			SELECT
				column_name,
				data_type,
				character_maximum_length,
				numeric_precision,
				numeric_scale,
				column_default,
				is_generated 
			FROM INFORMATION_SCHEMA.COLUMNS 
			WHERE TABLE_CATALOG = :TABLE_CATALOG	 
			AND TABLE_SCHEMA = :TABLE_SCHEMA 
			AND TABLE_NAME = :TABLE_NAME 
			ORDER BY ORDINAL_POSITION
			SQL;

		$db->setParam("TABLE_CATALOG", DB_NAME);
		$db->setParam("TABLE_SCHEMA", DB_SCHEMA);
		$db->setParam("TABLE_NAME", $tableName);

		echo "columns for table " . str_pad($tableName, 60, ".", STR_PAD_RIGHT) . " ";

		$db->query($sql);

		$retval = array();

		while($db->nextRecord())
		{
			$tmp = new ReverseColumn();

			$tmp->name = $db->f(0);

			$dataType = strtolower(trim($db->f(1)));
			$charLength = $db->f(2);
			$numericPrecision = $db->f(3);
			$numericScale = $db->f(4);
			$tmp->databaseGenerated = $db->f(6) == "ALWAYS";
			switch($dataType)
			{
				// -------------------------------------------------------------
				// DATE / TIME
				// -------------------------------------------------------------
				case 'date':
					$tmp->type = ColumnType::DATE;
					break;

				case 'timestamp':
				case 'timestamp without time zone':
				case 'timestamp with time zone':
					$tmp->type = ColumnType::DATETIME;
					break;

				case 'time':
				case 'time without time zone':
				case 'time with time zone':
					$tmp->type = ColumnType::TIME;
					break;

				// -------------------------------------------------------------
				// INTEGER
				// -------------------------------------------------------------
				case 'smallint':
				case 'integer':
				case 'bigint':
				case 'serial':
				case 'bigserial':
					$tmp->type = ColumnType::NUMBER;
					break;

				// -------------------------------------------------------------
				// FLOAT / DECIMAL
				// -------------------------------------------------------------
				case 'numeric':
				case 'decimal':
					if((int)$numericScale > 0)
					{
						$tmp->type = ColumnType::FLOAT;
					}
					else
					{
						$tmp->type = ColumnType::NUMBER;
					}
					break;

				case 'real':
				case 'double precision':
					$tmp->type = ColumnType::FLOAT;
					break;

				// -------------------------------------------------------------
				// TEXT
				// -------------------------------------------------------------
				case 'text':
					$tmp->type = ColumnType::TEXT;
					break;

				case 'character varying':
				case 'varchar':
				case 'character':
				case 'char':
				case 'tsvector':
					$tmp->type = ColumnType::VARCHAR;
					break;

				// -------------------------------------------------------------
				// BOOLEAN
				// -------------------------------------------------------------
				case 'boolean':
					$tmp->type = ColumnType::NUMBER;
					break;

				// -------------------------------------------------------------
				// JSON
				// -------------------------------------------------------------
				case 'json':
				case 'jsonb':
					$tmp->type = ColumnType::TEXT;
					break;

				// -------------------------------------------------------------
				// UUID
				// -------------------------------------------------------------
				case 'uuid':
					$tmp->type = ColumnType::VARCHAR;
					$tmp->size = 36;
					break;

				// -------------------------------------------------------------
				// BYTEA
				// -------------------------------------------------------------
				case 'bytea':
					$tmp->type = ColumnType::BLOB;
					break;

				// -------------------------------------------------------------
				// DEFAULT
				// -------------------------------------------------------------
				default:
					echo "\n!!!{" . $dataType . " -> VARCHAR}\n";
					$tmp->type = ColumnType::VARCHAR;
					break;
			}
			// -------------------------------------------------------------
			// SIZE
			// -------------------------------------------------------------
			if(empty($tmp->size))
			{
				if(!is_null($charLength))
				{
					$tmp->size = (int)$charLength;
				}
				elseif(!is_null($numericPrecision))
				{
					$tmp->size = (int)$numericPrecision;
				}
				else
				{
					$tmp->size = null;
				}
			}

			// -------------------------------------------------------------
			// SCALE
			// -------------------------------------------------------------
			if(!is_null($numericScale))
			{
				$tmp->scale = (int)$numericScale;
			}

			$retval[$tmp->name] = $tmp;
		}

		return $retval;
	}
	// -------------------------------------------------------------------------
	public function getPrimaryKeys($tableName)
	{
		$db = new DB();
		$sql = "SELECT COLUMN_NAME ";
		$sql .= "FROM INFORMATION_SCHEMA.TABLES t ";
		$sql .= "JOIN INFORMATION_SCHEMA.TABLE_CONSTRAINTS tc ON tc.table_catalog = t.table_catalog AND tc.table_schema = t.table_schema AND tc.table_name = t.table_name AND tc.constraint_type = 'PRIMARY KEY' ";
		$sql .= "JOIN INFORMATION_SCHEMA.KEY_COLUMN_USAGE kcu ON kcu.table_catalog = tc.table_catalog AND kcu.table_schema = tc.table_schema AND kcu.table_name = tc.table_name AND kcu.constraint_name = tc.constraint_name ";
		$sql .= "WHERE t.TABLE_CATALOG = :TABLE_CATALOG ";
		$sql .= "AND t.TABLE_SCHEMA = :TABLE_SCHEMA ";
		$sql .= "AND t.TABLE_NAME = :TABLE_NAME ";
		$sql .= "ORDER BY ORDINAL_POSITION ";
		$db->setParam("TABLE_CATALOG", DB_NAME);
		$db->setParam("TABLE_SCHEMA", DB_SCHEMA);
		$db->setParam("TABLE_NAME", $tableName);
		echo "selecting pk, ";
		$db->query($sql);
		$retval = array();
		while($db->nextRecord())
		{
			$tmp = new ReversePrimaryKey();
			$tmp->name = $db->f(0);
			$retval[$tmp->name] = $tmp;
		}
		return $retval;
	}
	// -------------------------------------------------------------------------
	public function getForeginKeys($tableName)
	{
		$db = new DB();
		$sql = "SELECT DISTINCT kcu.CONSTRAINT_NAME, kcu.COLUMN_NAME, ccu.COLUMN_NAME, ccu.TABLE_NAME, ccu.TABLE_SCHEMA, ORDINAL_POSITION ";
		$sql .= "FROM  information_schema.table_constraints AS tc ";
		$sql .= "JOIN information_schema.key_column_usage AS kcu ON tc.constraint_name = kcu.constraint_name ";
		$sql .= "JOIN information_schema.constraint_column_usage AS ccu ON ccu.constraint_name = tc.constraint_name ";
		$sql .= "WHERE tc.TABLE_CATALOG = :TABLE_CATALOG ";
		$sql .= "AND tc.TABLE_SCHEMA = :TABLE_SCHEMA ";
		$sql .= "AND tc.TABLE_NAME = :TABLE_NAME ";
		$sql .= "AND CONSTRAINT_TYPE = 'FOREIGN KEY'";
		$sql .= "ORDER BY ccu.TABLE_NAME ";
		$db->setParam("TABLE_CATALOG", DB_NAME);
		$db->setParam("TABLE_SCHEMA", DB_SCHEMA);
		$db->setParam("TABLE_NAME", $tableName);
		echo "fk, ";
		$db->query($sql);
		$retval = array();
		while($db->nextRecord())
		{
			if(!isset($retval[$db->f(0)]))
			{
				$retval[$db->f(0)] = new ReverseForeginKey();
				$retval[$db->f(0)]->refTableName = $db->f(3);
				$retval[$db->f(0)]->refTableSchema = $db->f(4);
			}
			$col = new ConnectedColumn();
			$col->fkColumnName = $db->f(1);
			$col->pkColumnName = $db->f(2);
			$retval[$db->f(0)]->columns[] = $col;
		}
		return $retval;
	}
	// -------------------------------------------------------------------------
}
?>
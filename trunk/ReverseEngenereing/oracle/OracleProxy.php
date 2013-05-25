<?php
/**
 * Created on 07-04-2013 13:32:26
 * author Tomasz Gajewski
 * package frontoffice
 * error prefix
 *
 */
class OracleProxy implements ReverseProxy
{
	// -------------------------------------------------------------------------
	public function getTables()
	{
		$db = new DB();

		$SQL = "SELECT TABLE_NAME ";
		$SQL .= "FROM ALL_TRIGGERS ";
		$SQL .= "WHERE TABLE_OWNER = :ORA_SCHEMA ";
		$SQL .= "AND TRIGGERING_EVENT = 'INSERT' ";
		$SQL .= "AND BASE_OBJECT_TYPE = 'TABLE' ";
		$SQL .= "ORDER BY TABLE_NAME ";

		$db->setParam("ORA_SCHEMA", ORA_SCHEMA,true);
		$db->query($SQL);
		$trigger = array();
		while($db->nextRecord())
		{
			$trigger[$db->f(0)] = true;
		}

		$SQL = "SELECT table_name, tablespace_name ";
		$SQL .= "FROM all_tables ";
		$SQL .= "WHERE owner = :ORA_SCHEMA ";
		$SQL .= "ORDER BY table_name ";
		$db->setParam("ORA_SCHEMA", ORA_SCHEMA,true);
		$db->query($SQL);
		$retval = array();
		while($db->nextRecord())
		{
			$tmp = new ReverseTable();
			$tmp->tableName = $db->f(0);
			$tmp->tableSpace = $db->f(1);
			$tmp->haveAutoNumberPKField = isset($trigger[$db->f(0)]);
			$retval[] = $tmp;
		}
		return $retval;
	}
	// -------------------------------------------------------------------------
	public function getColumn($tableName)
	{
		$db = new DB();
		$SQL = "SELECT column_name,data_type,data_length, data_precision, data_scale ";
		$SQL .= "FROM all_tab_columns ";
		$SQL .= "WHERE owner = :ORA_SCHEMA ";
		$SQL .= "AND table_name = :TABLE_NAME ";
		$SQL .= "ORDER BY column_id ";

		$db->setParam("ORA_SCHEMA", ORA_SCHEMA);
		$db->setParam("TABLE_NAME", $tableName);
		$db->query($SQL);
		$retval = array();
		while($db->nextRecord())
		{
			$tmp = new ReverseColumn();
			$tmp->name = $db->f(0);
			switch($db->f(1))
			{
				case "DATE" :
					$tmp->type = ColumnType::DATE;
					break;
				case "TIMESTAMP(6)" :
					$tmp->type = ColumnType::DATETIME;
					break;
				case "NUMBER" :
					$tmp->type = ColumnType::NUMBER;
					break;
				case "FLOAT" :
					$tmp->type = ColumnType::FLOAT;
					break;
				case "LONG" :
					$tmp->type = ColumnType::TEXT;
					break;
				default:
					$tmp->type = ColumnType::VARCHAR;
					break;

			}
			if(is_null($db->f(3)))
			{
				$tmp->size = $db->f(2);
			}
			else
			{
				$tmp->size = $db->f(3);
				$tmp->size = $db->f(4);
			}
			$retval[$tmp->name] = $tmp;
		}
		return $retval;

	}
	// -------------------------------------------------------------------------
	public function getPrimaryKeys($tableName)
	{
		$db = new DB();
		$SQL = "SELECT b.COLUMN_NAME ";
		$SQL .= "FROM ALL_CONSTRAINTS a ";
		$SQL .= "INNER JOIN  ALL_CONS_COLUMNS b ON a.CONSTRAINT_NAME  = b.CONSTRAINT_NAME ";
		$SQL .= "WHERE a.OWNER = :ORA_SCHEMA ";
		$SQL .= "AND a.TABLE_NAME = :TABLE_NAME ";
		$SQL .= "AND a.CONSTRAINT_TYPE = 'P' ";
		$SQL .= "ORDER BY b.POSITION ";
		$db->setParam("ORA_SCHEMA", ORA_SCHEMA);
		$db->setParam("TABLE_NAME", $tableName);
		$db->query($SQL);
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
		$SQL = "SELECT a.CONSTRAINT_NAME, c.COLUMN_NAME, c.COLUMN_NAME, b.TABLE_NAME, b.OWNER ";
		$SQL .= "FROM ALL_CONSTRAINTS a ";
		$SQL .= "INNER JOIN  ALL_CONSTRAINTS b ON b.CONSTRAINT_NAME  = a.R_CONSTRAINT_NAME ";
		$SQL .= "INNER JOIN  ALL_CONS_COLUMNS c ON a.CONSTRAINT_NAME  = c.CONSTRAINT_NAME ";
		$SQL .= "WHERE a.OWNER = :ORA_SCHEMA ";
		$SQL .= "AND a.TABLE_NAME = :TABLE_NAME ";
		$SQL .= "AND a.CONSTRAINT_TYPE = 'R' ";
		$SQL .= "ORDER BY c.POSITION ";
		$db->setParam("ORA_SCHEMA", ORA_SCHEMA);
		$db->setParam("TABLE_NAME", $tableName);
		$db->query($SQL);
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
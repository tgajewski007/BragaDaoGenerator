<?php
namespace braga\daogenerator\generator;
/**
 * Created on 13 lip 2013 11:04:37
 * author Tomasz Gajewski
 * package frontoffice
 * error prefix
 */
class MySQLDAOFileGenerator extends DAOFileGenerator
{
	// ------------------------------------------------------------------------------------------------------------------
	protected function generateUpdate(Table $t)
	{
		$data = array();
		$pk = array();
		foreach($t->getColumny() as $column)
			/* @var $column Column */
		{
			if($column->isPK())
			{
				$pk[$column->getKey()] = $column;
			}
			elseif($column instanceof Column)
			{
				$data[$column->getKey()] = $column;
			}
		}

		$this->addLine("/**", 1);
		$this->addLine(" * Method change object of class " . $t->getClassName(), 1);
		$this->addLine(" * update record in table " . $t->getName(), 1);
		$this->addLine(" * @return boolean", 1);
		$this->addLine(" */", 1);
		$this->addLine("protected function update()", 1);
		$this->addLine("{", 1);
		$this->addLine("if(!\$this->forUpdate)", 2);
		$this->addLine("{", 2);
		$this->addLine("\\braga\\graylogger\\BaseLogger::exception(new \\braga\\tools\\exception\\BragaException(\"SaveWithoutLock\", 0), \\Monolog\\Level::Critical, [ \"id\" => \$this->getKey(), \"class\" => \"" . $t->getClassName() . "\" ]);", 3);
		$this->addLine("}", 2);
		$this->addLine("\$db = new DB();", 2);
		$this->addLine("\$sql = <<<SQL", 2);
		$this->addLine("UPDATE {$t->getName()} ", 3);

		$columns = array();
		$params = array();
		foreach($t->getColumny() as $column)
		{
			$columns[$column->getName()] = $column->getName();
			$params[$column->getName()] = preg_replace("/[^A-Z1-9]/", "", strtoupper($column->getName()));
			if(strlen($params[$column->getName()]) == 0)
			{
				$params[$column->getName()] = RandomStringLetterOnly(8);
			}
		}

		$separator = "SET";
		foreach($data as $column)
		{
			$this->addLine("{$separator} {$columns[$column->getName()]} = :{$params[$column->getName()]} ", 3);
			$separator = ",";
		}
		$separator = "WHERE";
		$tab = 3;
		foreach($pk as $column)
		{
			$this->addLine("{$separator} {$column->getName()} = :{$params[$column->getName()]} ", $tab);
			$separator = "AND";
			$tab = 4;
		}
		$this->addLine("SQL;", 3);
		$tmp = $pk + $data;
		foreach($tmp as $column)
		{
			$this->addLine("\$db->setParam(\"" . $params[$column->getName()] . "\", \$this->get" . ucfirst($column->getClassFieldName()) . "());", 2);
		}
		$this->addLine("\$db->query(\$sql);", 2);
		$this->addLine("if(1 == \$db->getRowAffected())", 2);
		$this->addLine("{", 2);
		$this->addLine("return true;", 3);
		$this->addLine("}", 2);
		$this->addLine("else", 2);
		$this->addLine("{", 2);
		$this->addLine("throw new \\braga\\db\\exception\\ExecutionSqlException(\$db, \"" . $t->getErrorPrefix() . "03 Update record in table " . $t->getName() . " fail\");", 3);
		$this->addLine("}", 2);
		$this->addLine("}", 1);
		$this->addLine("// -----------------------------------------------------------------------------------------------------------------", 1);
	}
	// ------------------------------------------------------------------------------------------------------------------
	protected function generateCreate(Table $t)
	{
		$data = array();
		$pk = array();
		foreach($t->getColumny() as $column)
		{
			if($column->isPK())
			{
				$pk[$column->getKey()] = $column;
				if(!$column->isAutoGenerated())
				{
					$data[$column->getKey()] = $column;
				}
			}
			else
			{
				$data[$column->getKey()] = $column;
			}
		}

		$this->addLine("/**", 1);
		$this->addLine(" * Methods add object of class " . $t->getClassName(), 1);
		$this->addLine(" * insert record into table " . $t->getName(), 1);
		$this->addLine(" * @return boolean", 1);
		$this->addLine(" */", 1);
		$this->addLine("protected function create()", 1);
		$this->addLine("{", 1);
		$this->addLine("\$db = new DB();", 2);
		$this->addLine("\$sql = <<<SQL", 2);

		$columns = array();
		$params = array();
		foreach($data as $column)
		{
			$columns[$column->getName()] = $column->getName();
			$params[$column->getName()] = preg_replace("/[^A-Z1-9]/", "", strtoupper($column->getName()));
			if(strlen($params[$column->getName()]) == 0)
			{
				$params[$column->getName()] = RandomStringLetterOnly(8);
			}
		}
		$this->addLine("INSERT INTO {$t->getName()} (" . implode(", ", $columns) . ") ", 3);
		$this->addLine("VALUES (:" . implode(", :", $params) . ") ", 3);

		$this->addLine("SQL;", 3);
		$pkSequenced = false;
		if(count($pk) == 1)
		{
			if(current($pk)->isAutoGenerated())
			{
				$pkSequenced = true;
			}
		}
		foreach($data as $column)
		{
			$this->addLine("\$db->setParam(\"" . $params[$column->getName()] . "\", \$this->get" . ucfirst($column->getClassFieldName()) . "());", 2);
		}
		$this->addLine("\$db->query(\$sql);", 2);
		$this->addLine("if(1 == \$db->getRowAffected())", 2);
		$this->addLine("{", 2);
		if($pkSequenced)
		{
			$this->addLine("\$this->set" . ucfirst(current($pk)->getClassFieldName()) . "(\$db->getLastInsertID());", 3);
		}
		$this->addLine("self::updateFactoryIndex(\$this);", 3);
		$this->addLine("\$this->setReaded();", 3);
		$this->addLine("return true;", 3);
		$this->addLine("}", 2);
		$this->addLine("else", 2);
		$this->addLine("{", 2);
		$this->addLine("throw new \\braga\\db\\exception\\ExecutionSqlException(\$db, \"" . $t->getErrorPrefix() . "02 Insert record into table " . $t->getName() . " fail\");", 3);
		$this->addLine("}", 2);
		$this->addLine("}", 1);
		$this->addLine("// -----------------------------------------------------------------------------------------------------------------", 1);
	}
	// ------------------------------------------------------------------------------------------------------------------
}
?>
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
	// -------------------------------------------------------------------------
	protected function generateUpdate(Table $t)
	{
		$data = array();
		$pk = array();
		foreach($t->getColumny() as $c)/* @var $c Column */
		{
			if($c->isPK())
			{
				$pk[$c->getKey()] = $c;
			}
			elseif($c instanceof Column)
			{
				$data[$c->getKey()] = $c;
			}
		}

		$this->addLine("/**", 1);
		$this->addLine(" * Method change object of class " . $t->getClassName(), 1);
		$this->addLine(" * update record in table " . $t->getName(), 1);
		$this->addLine(" * @return boolean", 1);
		$this->addLine(" */", 1);
		$this->addLine("protected function update()", 1);
		$this->addLine("{", 1);
		$this->addLine("\$db = new DB();", 2);

		$this->addLine("\$sql  = \"UPDATE \" . " . $t->getSchema() . " . \"." . $t->getName() . " \";", 2);

		$columns = array();
		$params = array();
		foreach($t->getColumny() as $c)/* @var $c Column */
		{
			$columns[$c->getName()] = $c->getName();
			$params[$c->getName()] = preg_replace("/[^A-Z1-9]/", "", strtoupper($c->getName()));
			if(strlen($params[$c->getName()]) == 0)
			{
				$params[$c->getName()] = RandomStringLetterOnly(8);
			}
		}

		$separator = "SET";
		foreach($data as $c)
		{
			$this->addLine("\$sql .= \"" . $separator . " " . $columns[$c->getName()] . " = :" . $params[$c->getName()] . " \";", 2);
			$separator = " ,";
		}
		$separator = "WHERE";
		foreach($pk as $c)
		{
			$this->addLine("\$sql .= \"" . $separator . " " . $columns[$c->getName()] . " = :" . $params[$c->getName()] . " \";", 2);
			$separator = "AND";
		}
		$tmp = $pk + $data;
		foreach($tmp as $c)/* @var $c Column */
		{
			$this->addLine("\$db->setParam(\"" . $params[$c->getName()] . "\",\$this->get" . ucfirst($c->getClassFieldName()) . "());", 2);
		}
		$this->addLine("\$db->query(\$sql);", 2);
		$this->addLine("if(1 == \$db->getRowAffected())", 2);
		$this->addLine("{", 2);
		$this->addLine("return true;", 3);
		$this->addLine("}", 2);
		$this->addLine("else", 2);
		$this->addLine("{", 2);
		$this->addLine("AddAlert(\"" . $t->getErrorPrefix() . "03 Update record in table " . $t->getName() . " fail\");", 3);
		$this->addLine("return false;", 3);
		$this->addLine("}", 2);
		$this->addLine("}", 1);
		$this->addLine("// -------------------------------------------------------------------------", 1);
	}
	// -------------------------------------------------------------------------
	protected function generateCreate(Table $t)
	{
		$data = array();
		$pk = array();
		foreach($t->getColumny() as $c)/* @var $c Column */
		{
			if($c->isPK())
			{
				$pk[$c->getKey()] = $c;
				if(!$c->isAutoGenerated())
				{
					$data[$c->getKey()] = $c;
				}
			}
			else
			{
				$data[$c->getKey()] = $c;
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

		$columns = array();
		$params = array();
		foreach($data as $c)/* @var $c Column */
		{
			$columns[$c->getName()] = $c->getName();
			$params[$c->getName()] = preg_replace("/[^A-Z1-9]/", "", strtoupper($c->getName()));
			if(strlen($params[$c->getName()]) == 0)
			{
				$params[$c->getName()] = RandomStringLetterOnly(8);
			}
		}
		$this->addLine("\$sql  = \"INSERT INTO \" . " . $t->getSchema() . " . \"." . $t->getName() . "(" . implode(", ", $columns) . ") \";", 2);
		$this->addLine("\$sql .= \"VALUES(:" . implode(", :", $params) . ") \";", 2);

		$pkSequenced = false;
		if(count($pk) == 1)
		{
			if(current($pk)->isAutoGenerated())
			{
				$pkSequenced = true;
			}
		}
		foreach($data as $c)/* @var $c Column */
		{
			$this->addLine("\$db->setParam(\"" . $params[$c->getName()] . "\",\$this->get" . ucfirst($c->getClassFieldName()) . "());", 2);
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
		$this->addLine("AddAlert(\"" . $t->getErrorPrefix() . "02 Insert record into table " . $t->getName() . " fail\");", 3);
		$this->addLine("return false;", 3);
		$this->addLine("}", 2);
		$this->addLine("}", 1);
		$this->addLine("// -------------------------------------------------------------------------", 1);
	}
	// -------------------------------------------------------------------------
}
?>
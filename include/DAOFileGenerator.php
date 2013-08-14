<?php
/**
 * Created on 23-03-2013 07:57:30
 * author Tomasz Gajewski
 * package frontoffice
 * error prefix
 */
class DAOFileGenerator
{
	// -------------------------------------------------------------------------
	/**
	 *
	 * @var Project
	 */
	protected $project = null;
	protected $fileHandle = null;
	// -------------------------------------------------------------------------
	function __construct(Project $p)
	{
		$this->project = $p;
	}
	// -------------------------------------------------------------------------
	public function GO()
	{
		foreach($this->project->getTables() as $t)/* @var $t Table */
		{
			$this->open($t);
			$this->generateNameSpace();
			$this->prepareClass($t);
			$this->close();
		}
	}
	// -------------------------------------------------------------------------
	protected function addLine($content, $tabLevel, $newLine = true)
	{
		$tmp = str_repeat("\t", $tabLevel);
		if($newLine)
		{
			$tmp .= $content . "\n";
		}
		else
		{
			$tmp .= $content;
		}
		fwrite($this->fileHandle, $tmp);
	}
	// -------------------------------------------------------------------------
	protected function prepareClass(Table $t)
	{
		$this->generateClassDocumentation($t);
		$this->generateClassHead($t);
		$this->generateProperties($t);
		$this->generateConstruktor($t);
		$this->generateStaticGetMethod($t);
		$this->generateUpdateFactoryIndex($t);
		$this->generateStaticGetByDataSourceMethod($t);
		$this->generateIsReaded();
		$this->generateSetters($t);
		$this->generateGetters($t);
		$this->generateGetKey($t);
		$this->generateGetterCollection($t);
		$this->generateGetterObject($t);
		$this->generateRead($t);
		$this->generateCreate($t);
		$this->generateUpdate($t);
		$this->generateDestroy($t);

		$this->generateSetAllFromDB($t);
		$this->generateGetAllForForeginColumn($t);
		$this->generateClassFooter();
	}
	// -------------------------------------------------------------------------
	protected function generateGetKey($t)
	{
		$pkField = array();
		foreach($t->getColumny() as $c) /* @var $c Column */
		{
			if($c->isPK())
			{
				$pkField[] = $c;
			}
		}
		$tmp1 = array();
		foreach($pkField as $c) /* @var $c Column */
		{
			$tmp1[] = "\$this->get" . ucfirst($c->getClassFieldName()) . "()";
		}
		$this->addLine("public function getKey()", 1);
		$this->addLine("{", 1);
		$this->addLine("return " . implode(" . \"_\" . ", $tmp1) . ";", 2);
		$this->addLine("}", 1);
		$this->addLine("// -------------------------------------------------------------------------", 1);
	}
	// -------------------------------------------------------------------------
	protected function generateStaticGetByDataSourceMethod($t)
	{
		$pkField = array();
		foreach($t->getColumny() as $c) /* @var $c Column */
		{
			if($c->isPK())
			{
				$pkField[] = $c;
			}
		}
		$tmp1 = array();
		foreach($pkField as $c) /* @var $c Column */
		{
			$tmp1[] = "\$db->f(\"" . $c->getName() . "\")";
		}
		$this->addLine("/**", 1);
		$this->addLine(" * @param DataSource \$db", 1);
		$this->addLine(" * @return ".$t->getClassName(), 1);
		$this->addLine(" */", 1);
		$this->addLine("static function getByDataSource(DataSource \$db)", 1);
		$this->addLine("{", 1);
		$this->addLine("\$key = " . implode(" . \"_\" . ", $tmp1) . ";", 2);
		$this->addLine("if(!isset(self::\$instance[\$key]))", 2);
		$this->addLine("{", 2);
		$this->addLine("self::\$instance[\$key] = new " . $t->getClassName() . "();", 3);
		$this->addLine("self::\$instance[\$key]->setAllFromDB(\$db);", 3);
		$this->addLine("}", 2);
		$this->addLine("return self::\$instance[\$key];", 2);
		$this->addLine("}", 1);
		$this->addLine("// -------------------------------------------------------------------------", 1);
	}
	// -------------------------------------------------------------------------
	protected function generateUpdateFactoryIndex(Table $t)
	{
		$tmp1 = array();
		foreach($t->getPk() as $c) /* @var $c Column */
		{
			$tmp1[] = "\$" . lcfirst($t->getClassName()) . "->get" . ucfirst($c->getClassFieldName()) . "()";
		}

		$this->addLine("protected static function updateFactoryIndex(" . $t->getClassName() . " \$" . lcfirst($t->getClassName()) . ")", 1);
		$this->addLine("{", 1);
		$this->addLine("\$key = array_search(\$" . lcfirst($t->getClassName()) . ",self::\$instance,true);", 2);
		$this->addLine("if(\$key !== false)", 2);
		$this->addLine("{", 2);
		$this->addLine("if(\$key !== " . implode(" . \"_\" . ", $tmp1) . ")", 3);
		$this->addLine("{", 3);
		$this->addLine("unset(self::\$instance[\$key]);", 4);
		$this->addLine("self::\$instance[" . implode(" . \"_\" . ", $tmp1) . "] = \$" . lcfirst($t->getClassName()) . ";", 4);
		$this->addLine("}", 3);
		$this->addLine("}", 2);
		$this->addLine("else", 2);
		$this->addLine("{", 2);
		$this->addLine("self::\$instance[" . implode(" . \"_\" . ", $tmp1) . "] = \$" . lcfirst($t->getClassName()) . ";", 3);
		$this->addLine("}", 2);
		$this->addLine("}", 1);
		$this->addLine("// -------------------------------------------------------------------------", 1);
	}
	// -------------------------------------------------------------------------
	protected function generateStaticGetMethod($t)
	{
		$pkField = array();
		foreach($t->getColumny() as $c) /* @var $c Column */
		{
			if($c->isPK())
			{
				$pkField[] = $c;
			}
		}

		$this->addLine("/**", 1);
		$tmp1 = array();
		$tmp2 = array();
		$tmp3 = array();
		foreach($pkField as $c) /* @var $c Column */
		{
			$this->addLine(" * @param int \$" . $c->getClassFieldName() . "", 1);
			$tmp1[] = "\$" . $c->getClassFieldName() . " = null";
			$tmp2[] = "isset(\$" . $c->getClassFieldName() . ")";
			$tmp3[] = "\$" . $c->getClassFieldName();
		}

		$this->addLine(" * @return " . $t->getClassName() . "", 1);
		$this->addLine(" */", 1);
		$this->addLine("static function get(" . implode(", ", $tmp1) . ")", 1);
		$this->addLine("{", 1);
		$this->addLine("if(" . implode(" && ", $tmp2) . ")", 2);
		$this->addLine("{", 2);
		$this->addLine("if(!isset(self::\$instance[" . implode(" . \"_\" . ", $tmp3) . "]))", 3);
		$this->addLine("{", 3);
		$this->addLine("self::\$instance[" . implode(" . \"_\" . ", $tmp3) . "] = new " . $t->getClassName() . "(" . implode(", ", $tmp3) . ");", 4);
		$this->addLine("}", 3);
		$this->addLine("return self::\$instance[" . implode(" . \"_\" . ", $tmp3) . "];", 3);
		$this->addLine("}", 2);
		$this->addLine("else", 2);
		$this->addLine("{", 2);
		$this->addLine("return self::\$instance[\"\\\$\".count(self::\$instance)] = new " . $t->getClassName() . "();", 3);
		$this->addLine("}", 2);
		$this->addLine("}", 1);
		$this->addLine("// -------------------------------------------------------------------------", 1);
	}
	// -------------------------------------------------------------------------
	protected function generateGetAllForForeginColumn($t)
	{
		$fk = array();
		foreach($t->getFk() as $fk) /* @var $fk ForeginKey */
		{
			$fkTable = $fk->getTable();

			$this->addLine("/**", 1);
			$this->addLine(" * Metoda zwraca kolekcję obiektów " . $t->getClassName(), 1);
			$this->addLine(" * @return Collection&lt;" . $t->getClassName() . "&gt; ", 1);
			$this->addLine(" */", 1);
			$this->addLine("public static function getAllBy" . $fkTable->getClassName() . "(" . $fkTable->getClassName() . "DAO \$" . lcfirst($fkTable->getClassName()) . ")", 1);
			$this->addLine("{", 1);
			$this->addLine("\$db = new DB();", 2);
			$this->addLine("\$sql  = \"SELECT * \";", 2);
			$this->addLine("\$sql .= \"FROM \" . " . $t->getSchema() . " . \"." . $t->getName() . " \";", 2);

			$separator = "WHERE";
			foreach($fk->getColumn() as $c)/* @var $c ConnectedColumn */
			{
				foreach($t->getColumny() as $i)/* @var $i Column */
				{
					if($c->fkColumnName == $i->getName())
					{
						$this->addLine("\$sql .= \"" . $separator . " " . $i->getName() . " = :" . mb_strtoupper($i->getName()) . " \";", 2);
						$separator = "AND";
					}
				}
			}

			foreach($fk->getColumn() as $c)/* @var $c ConnectedColumn */
			{
				foreach($t->getColumny() as $i)/* @var $i Column */
				{
					if($c->fkColumnName == $i->getName())
					{
						foreach($fk->getTable()->getPk() as $pk)/* @var $pk Column */
						{
							if($pk->getName() == $c->pkColumnName)
							{
								$this->addLine("\$db->setParam(\"" . mb_strtoupper($i->getName()) . "\", \$" . lcfirst($fkTable->getClassName()) . "->get" . ucfirst($pk->getClassFieldName()) . "());", 2);
							}
						}
					}
				}
			}

			$this->addLine("\$db->query(\$sql);", 2);
			$this->addLine("\$retval = new Collection(\$db, " . $t->getClassName() . "::get());", 2);
			$this->addLine("return \$retval;", 2);
			$this->addLine("}", 1);
			$this->addLine("// -------------------------------------------------------------------------", 1);
		}
	}
	// -------------------------------------------------------------------------
	protected function generateDestroy(Table $t)
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
			elseif($c instanceof ColumnForeginKey)
			{
				foreach($c->getTable()->getColumny() as $z)/* @var $z Column */
				{
					if($z instanceof ColumnPrimaryKey)
					{
						$data[$z->getKey()] = $z;
					}
				}
			}
		}

		$this->addLine("/**", 1);
		$this->addLine(" * Metoda usuwa obiekt klasy " . $t->getClassName(), 1);
		$this->addLine(" * usuwany jest rekord z tablicy " . $t->getName(), 1);
		$this->addLine(" * @return boolean Zwraca true w przypadku powodzenia i false w przypadku przeciwnym", 1);
		$this->addLine(" */", 1);
		$this->addLine("protected function destroy()", 1);
		$this->addLine("{", 1);
		$this->addLine("\$db = new DB();", 2);
		$this->addLine("\$sql  = \"DELETE FROM \" . " . $t->getSchema() . " . \"." . $t->getName() . " \";", 2);
		$separator = "WHERE";
		foreach($pk as $c)
		{
			$this->addLine("\$sql .= \"" . $separator . " " . $c->getName() . " = :" . mb_strtoupper($c->getName()) . " \";", 2);
			$separator = "AND";
		}
		foreach($pk as $c)/* @var $c Column */
		{
			$this->addLine("\$db->setParam(\"" . mb_strtoupper($c->getName()) . "\", \$" . $c->getClassFieldName() . ");", 2);
		}
		$this->addLine("\$db->query(\$sql);", 2);
		$this->addLine("if(1 == \$db->getRowAffected())", 2);
		$this->addLine("{", 2);
		$this->addLine("\$db->commit();", 3);
		$this->addLine("return true;", 3);
		$this->addLine("}", 2);
		$this->addLine("else", 2);
		$this->addLine("{", 2);
		$this->addLine("\$db->rollback();", 3);
		$this->addLine("AddAlert(\"" . $t->getErrorPrefix() . "04 Usunięcie rekordu z tablicy " . $t->getName() . " nie powiodło się\");", 3);
		$this->addLine("return false;", 3);
		$this->addLine("}", 2);
		$this->addLine("}", 1);
		$this->addLine("// -------------------------------------------------------------------------", 1);
	}
	// -------------------------------------------------------------------------
	protected function generateRead(Table $t)
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
			elseif($c instanceof ColumnForeginKey)
			{
				foreach($c->getTable()->getColumny() as $z)/* @var $z Column */
				{
					if($z instanceof ColumnPrimaryKey)
					{
						$data[$z->getKey()] = $z;
					}
				}
			}
		}

		$this->addLine("/**", 1);
		$this->addLine(" * Metoda odczytuje obiekt klasy " . $t->getClassName() . " własności możliwe do pobrania poprzez metody get...", 1);
		$this->addLine(" * Odczytywany jest rekord z tablicy " . $t->getName(), 1);
		$this->addLine(" * @return boolean Zwraca true w przypadku powodzenia i false w przypadku przeciwnym", 1);
		$this->addLine(" */", 1);
		$tmp1 = array();
		foreach($pk as $c)/* @var $c Column */
		{
			$tmp1[] = "\$" . $c->getClassFieldName();
		}
		$this->addLine("protected function retrieve(" . implode(", ", $tmp1) . ")", 1);
		$this->addLine("{", 1);
		$this->addLine("\$db = new DB();", 2);
		$this->addLine("\$sql  = \"SELECT * FROM \" . " . $t->getSchema() . " . \"." . $t->getName() . " \";", 2);
		$separator = "WHERE";
		foreach($pk as $c)
		{
			$this->addLine("\$sql .= \"" . $separator . " " . $c->getName() . " = :" . mb_strtoupper($c->getName()) . " \";", 2);
			$separator = "AND";
		}
		foreach($pk as $c)/* @var $c Column */
		{
			$this->addLine("\$db->setParam(\"" . mb_strtoupper($c->getName()) . "\", \$" . $c->getClassFieldName() . ");", 2);
		}
		$this->addLine("\$db->query(\$sql);", 2);
		$this->addLine("if(\$db->nextRecord())", 2);
		$this->addLine("{", 2);
		$this->addLine("\$this->setAllFromDB(\$db);", 3);
		$this->addLine("return true;", 3);
		$this->addLine("}", 2);
		$this->addLine("else", 2);
		$this->addLine("{", 2);
		$this->addLine("return false;", 3);
		$this->addLine("}", 2);
		$this->addLine("}", 1);
		$this->addLine("// -------------------------------------------------------------------------", 1);
	}
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
			elseif($c instanceof ColumnForeginKey)
			{
				foreach($c->getTable()->getColumny() as $z)/* @var $z Column */
				{
					if($z instanceof ColumnPrimaryKey)
					{
						$data[$z->getKey()] = $z;
					}
				}
			}
		}

		$this->addLine("/**", 1);
		$this->addLine(" * Metoda zmienia obiekt klasy " . $t->getClassName() . " własności możliwe do pobrania poprzez metody get...", 1);
		$this->addLine(" * Zmieniany jest rekord w tablicy " . $t->getName(), 1);
		$this->addLine(" * @return boolean Zwraca true w przypadku powodzenia i false w przypadku przeciwnym", 1);
		$this->addLine(" */", 1);
		$this->addLine("protected function update()", 1);
		$this->addLine("{", 1);
		$this->addLine("\$db = new DB();", 2);

		$pieces = array();
		foreach($data as $c)/* @var $c Column */
		{
			$pieces[] = $c->getName();
		}
		$this->addLine("\$sql  = \"UPDATE \" . " . $t->getSchema() . " . \"." . $t->getName() . " \";", 2);

		$columns = array();
		$params = array();
		foreach($t->getColumny() as $c)/* @var $c Column */
		{

			if($c->getName() == strtoupper($c->getName()))
				$columns[$c->getName()] = $c->getName();
			else
				$columns[$c->getName()] = "\\\"" . $c->getName() . "\\\"";
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
		$this->addLine("\$db->commit();", 3);
		$this->addLine("return true;", 3);
		$this->addLine("}", 2);
		$this->addLine("else", 2);
		$this->addLine("{", 2);
		$this->addLine("\$db->rollback();", 3);
		$this->addLine("AddAlert(\"" . $t->getErrorPrefix() . "03 Zmiana rekordu w tablicy " . $t->getName() . " nie powiodło się\");", 3);
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
		$this->addLine(" * Metoda tworzy obiekt klasy " . $t->getClassName() . " własności możliwe do pobrania poprzez metody get...", 1);
		$this->addLine(" * Dodawany jest rekord do tablicy " . $t->getName(), 1);
		$this->addLine(" * @return boolean Zwraca true w przypadku powodzenia i false w przypadku przeciwnym", 1);
		$this->addLine(" */", 1);
		$this->addLine("protected function create()", 1);
		$this->addLine("{", 1);
		$this->addLine("\$db = new DB();", 2);

		$columns = array();
		$params = array();
		foreach($data as $c)/* @var $c Column */
		{

			if($c->getName() == strtoupper($c->getName()))
				$columns[$c->getName()] = $c->getName();
			else
				$columns[$c->getName()] = "\\\"" . $c->getName() . "\\\"";
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
				$this->addLine("\$sql .= \"RETURNING " . current($pk)->getName() . " INTO :" . mb_strtoupper(current($pk)->getName()) . "\";", 2);
			}
		}

		if($pkSequenced)
		{
			$this->addLine("\$db->setParam(\"" . mb_strtoupper(current($pk)->getName()) . "\",\$this->get" . ucfirst(current($pk)->getClassFieldName()) . "(),true," . current($pk)->getSize() . ",SQLT_INT);", 2);
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
			$this->addLine("\$this->set" . ucfirst(current($pk)->getClassFieldName()) . "(\$db->getParam(\"" . mb_strtoupper(current($pk)->getName()) . "\"));", 3);
		}
		$this->addLine("\$db->commit();", 3);
		$this->addLine("self::updateFactoryIndex(\$this);", 3);
		$this->addLine("\$this->setReaded();", 3);
		$this->addLine("return true;", 3);
		$this->addLine("}", 2);
		$this->addLine("else", 2);
		$this->addLine("{", 2);
		$this->addLine("\$db->rollback();", 3);
		$this->addLine("AddAlert(\"" . $t->getErrorPrefix() . "02 Dodanie rekordu do tablicy " . $t->getName() . " nie powiodło się\");", 3);
		$this->addLine("return false;", 3);
		$this->addLine("}", 2);
		$this->addLine("}", 1);
		$this->addLine("// -------------------------------------------------------------------------", 1);
	}
	// -------------------------------------------------------------------------
	protected function generateGetterObject(Table $table)
	{
		foreach($table->getFk() as $fk)/* @var $fk ForeginKey */
		{
			$tmp1 = array();
			foreach($fk->getTable()->getPk() as $c) /* @var $c Column */
			{
				foreach($fk->getColumn() as $cc)/* @var $cc ConnectedColumn */
				{
					if($c->getName() == $cc->pkColumnName)
					{
						foreach($table->getColumny() as $x)/* @var $x Column */
						{
							if($x->getName() == $cc->fkColumnName)
							{
								$tmp1[] = "\$this->get" . ucfirst($x->getClassFieldName()) . "()";
							}
						}
					}
				}
			}
			$this->addLine("/**", 1);
			$this->addLine(" * @return " . $fk->getTable()->getClassName(), 1);
			$this->addLine(" */", 1);
			$this->addLine("public function get" . ucfirst($fk->getTable()->getClassName()) . "()", 1);
			$this->addLine("{", 1);
			$this->addLine("if(!isset(\$this->" . lcfirst($fk->getTable()->getClassName()) . "))", 2);
			$this->addLine("{", 2);

			$this->addLine("\$this->" . lcfirst($fk->getTable()->getClassName()) . " = " . $fk->getTable()->getClassName() . "::get(" . implode(", ", $tmp1) . ");", 3);
			$this->addLine("}", 2);
			$this->addLine("return \$this->" . lcfirst($fk->getTable()->getClassName()) . ";", 2);
			$this->addLine("}", 1);
			$this->addLine("// -------------------------------------------------------------------------", 1);
		}
	}
	// -------------------------------------------------------------------------
	protected function generateGetters(Table $t)
	{
		foreach($t->getColumny() as $c)/* @var $c Column */
		{
			$this->generateGetter($c->getClassFieldName());
		}
	}
	// -------------------------------------------------------------------------
	protected function generateGetterCollection(Table $table)
	{
		foreach($this->project->getTables() as $t) /* @var $t Table */
		{
			foreach($t->getFk() as $fk)/* @var $fk ForeginKey */
			{
				if($fk->getTable() == $table)
				{
					$this->addLine("/**", 1);
					$this->addLine(" * Metoda zwraca kolekcję obiektów " . $t->getClassName(), 1);
					$this->addLine(" * @return Collection&lt;" . $t->getClassName() . "&gt; ", 1);
					$this->addLine(" */", 1);
					$this->addLine("public function get" . ucfirst($t->getClassName()) . "s()", 1);
					$this->addLine("{", 1);
					$this->addLine("return " . $t->getClassName() . "::getAllBy" . ucfirst($table->getClassName()) . "(\$this);", 2);
					$this->addLine("}", 1);
					$this->addLine("// -------------------------------------------------------------------------", 1);
				}
			}
		}
	}
	// -------------------------------------------------------------------------
	protected function generateGetter($classFieldName)
	{
		$this->addLine("public function get" . ucfirst($classFieldName) . "()", 1);
		$this->addLine("{", 1);
		$this->addLine("return \$this->" . $classFieldName . ";", 2);
		$this->addLine("}", 1);
		$this->addLine("// -------------------------------------------------------------------------", 1);
	}
	// -------------------------------------------------------------------------
	protected function generateIsReaded()
	{
		$this->addLine("protected function isReaded()", 1);
		$this->addLine("{", 1);
		$this->addLine("return \$this->readed;", 2);
		$this->addLine("}", 1);
		$this->addLine("// -------------------------------------------------------------------------", 1);
		$this->addLine("protected function setReaded()", 1);
		$this->addLine("{", 1);
		$this->addLine("\$this->readed = true;", 2);
		$this->addLine("}", 1);
		$this->addLine("// -------------------------------------------------------------------------", 1);
	}
	// -------------------------------------------------------------------------
	protected function generateSetter(Column $c)
	{
		if($c->isAutoGenerated() && $c->isPK())
		{
			$this->addLine("protected function set" . ucfirst($c->getClassFieldName()) . "(\$" . $c->getClassFieldName() . ")", 1);
		}
		else
		{
			$this->addLine("public function set" . ucfirst($c->getClassFieldName()) . "(\$" . $c->getClassFieldName() . ")", 1);
		}
		$this->addLine("{", 1);
		switch($c->getType())
		{
			case ColumnType::CHAR:
			case ColumnType::VARCHAR:
				$this->addLine("if(empty(\$" . $c->getClassFieldName() . "))", 2);
				$this->addLine("{", 2);
				$this->addLine("\$this->" . $c->getClassFieldName() . " = null;", 3);
				$this->addLine("}", 2);
				$this->addLine("else", 2);
				$this->addLine("{", 2);
				$this->addLine("\$this->" . $c->getClassFieldName() . " = mb_substr(\$" . $c->getClassFieldName() . ",0," . $c->getSize() . ");", 2);
				$this->addLine("}", 2);
				break;
			case ColumnType::NUMBER:
				$this->addLine("if(empty(\$" . $c->getClassFieldName() . "))", 2);
				$this->addLine("{", 2);
				$this->addLine("\$this->" . $c->getClassFieldName() . " = null;", 3);
				$this->addLine("}", 2);
				$this->addLine("else", 2);
				$this->addLine("{", 2);
				$this->addLine("\$this->" . $c->getClassFieldName() . " = round(\$" . $c->getClassFieldName() . "," . intval($c->getScale()) . ");", 3);
				$this->addLine("}", 2);
				break;
			case ColumnType::DATE:
				$this->addLine("if(empty(\$" . $c->getClassFieldName() . "))", 2);
				$this->addLine("{", 2);
				$this->addLine("\$this->" . $c->getClassFieldName() . " = null;", 3);
				$this->addLine("}", 2);
				$this->addLine("else", 2);
				$this->addLine("{", 2);
				$this->addLine("\$this->" . $c->getClassFieldName() . " = date(PHP_DATE_FORMAT,strtotime(\$" . $c->getClassFieldName() . "));", 3);
				$this->addLine("}", 2);
				break;
			case ColumnType::DATETIME:
				$this->addLine("if(empty(\$" . $c->getClassFieldName() . "))", 2);
				$this->addLine("{", 2);
				$this->addLine("\$this->" . $c->getClassFieldName() . " = null;", 3);
				$this->addLine("}", 2);
				$this->addLine("else", 2);
				$this->addLine("{", 2);
				$this->addLine("\$this->" . $c->getClassFieldName() . " = date(PHP_DATETIME_FORMAT,strtotime(\$" . $c->getClassFieldName() . "));", 3);
				$this->addLine("}", 2);
				break;
			case ColumnType::FLOAT:
			case ColumnType::TEXT:
			case ColumnType::ENUM:
			default:
				$this->addLine("\$this->" . $c->getClassFieldName() . " = \$" . $c->getClassFieldName() . ";", 2);
				break;
		}
		$this->addLine("}", 1);
		$this->addLine("// -------------------------------------------------------------------------", 1);
	}
	// -------------------------------------------------------------------------
	protected function generateSetters(Table $t)
	{
		foreach($t->getColumny() as $c)/* @var $c Column */
		{
			$this->generateSetter($c);
		}
	}
	// -------------------------------------------------------------------------
	protected function generateSetAllFromDB(Table $t)
	{
		$this->addLine("/**", 1);
		$this->addLine(" * Metoda ustawia wszystkie własności klasy " . $t->getClassName() . " z obiektu klasy DB", 1);
		$this->addLine(" * @return void", 1);
		$this->addLine(" */", 1);
		$this->addLine("protected function setAllFromDB(DataSource \$db)", 1);
		$this->addLine("{", 1);
		foreach($t->getColumny() as $c) /* @var $c Column */
		{
			if($c instanceof ColumnForeginKey)
			{
				$columns = $c->getTable()->getColumny();
				foreach($c->getClassFieldName() as $key => $s)
				{
					$this->addLine("\$this->set" . ucfirst($s) . "(\$db->f(\"" . $columns[$key]->getName() . "\"));", 2);
				}
			}
			else
			{
				$this->addLine("\$this->set" . ucfirst($c->getClassFieldName()) . "(\$db->f(\"" . $c->getKey() . "\"));", 2);
			}
		}
		$this->addLine("\$this->setReaded();", 2);

		$this->addLine("}", 1);
		$this->addLine("// -------------------------------------------------------------------------", 1);
	}
	// -------------------------------------------------------------------------
	protected function generateConstruktor(Table $t)
	{
		$this->addLine("/**", 1);
		$tmp1 = array();
		$tmp2 = array();
		$tmp3 = array();
		foreach($t->getPk() as $c) /* @var $c Column */
		{
			$this->addLine(" * @param " . $c->getPHPType() . " \$" . $c->getClassFieldName() . "", 1);
			$tmp1[] = "\$" . $c->getClassFieldName() . " = null";
			$tmp2[] = "isset(\$" . $c->getClassFieldName() . ")";
			$tmp3[] = "\$" . $c->getClassFieldName() . "";
		}
		$this->addLine(" */", 1);
		$this->addLine("protected function __construct(" . implode(", ", $tmp1) . ")", 1);
		$this->addLine("{", 1);
		$this->addLine("if(" . implode(" && ", $tmp2) . ")", 2);
		$this->addLine("{", 2);

		$this->addLine("if(!\$this->retrieve(" . implode(", ", $tmp3) . "))", 3);
		$this->addLine("{", 3);
		$this->addLine("throw new Exception(\"" . $t->getErrorPrefix() . "01 \" . " . $t->getSchema() . " . \"." . $t->getName() . "(\" . " . implode(" . \", \".", $tmp3) . " . \")  nie istnieje\");", 4);
		$this->addLine("}", 3);
		$this->addLine("}", 2);
		$this->addLine("}", 1);
		$this->addLine("// -------------------------------------------------------------------------", 1);
	}
	// -------------------------------------------------------------------------
	protected function generateProperties(Table $t)
	{
		$this->addLine("// -------------------------------------------------------------------------", 1);
		$this->addLine("protected static \$instance = array();", 1);
		$this->addLine("// -------------------------------------------------------------------------", 1);
		foreach($t->getColumny() as $c) /* @var $c Column */
		{
			$this->addLine("protected \$" . $c->getClassFieldName() . " = null;", 1);
		}
		$this->addLine("protected \$readed = false;", 1);
		$this->addLine("// -------------------------------------------------------------------------", 1);

		$addSeparator = false;
		foreach($t->getFk() as $fk) /* @var $fk ForeginKey */
		{
			$addSeparator = true;
			$this->addLine("/**", 1);
			$this->addLine(" * @var " . $fk->getTable()->getClassName(), 1);
			$this->addLine(" */", 1);
			$this->addLine("protected \$" . lcfirst($fk->getTable()->getClassName()) . " = null;", 1);
		}

		if($addSeparator)
		{
			$this->addLine("// -------------------------------------------------------------------------", 1);
		}

// 		$addSeparator = false;
// 		foreach($this->project->getTables() as $table) /* @var $table Table */
// 		{
// 			if($table != $t)
// 			{
// 				foreach($table->getFk() as $fk)/* @var $fk ForeginKey */
// 				{
// 					if($fk->getTableName() == $t->getName() && $fk->getTableSchema() == $t->getSchema())
// 					{
// 						$addSeparator = true;
// 						$this->addLine("protected \$" . lcfirst($table->getClassName()) . "s = null;", 1);
// 					}
// 				}
// 			}
// 		}
// 		if($addSeparator)
// 		{
// 			$this->addLine("// -------------------------------------------------------------------------", 1);
// 		}
	}
	// -------------------------------------------------------------------------
	protected function generateClassHead($t)
	{
		$this->addLine("class " . $t->getClassName() . "DAO", 0);
		$this->addLine("{", 0);
	}
	// -------------------------------------------------------------------------
	protected function generateClassFooter()
	{
		$this->addLine("}", 0);
	}
	// -------------------------------------------------------------------------
	protected function generateClassDocumentation(Table $t)
	{
		$this->addLine("/**", 0);
		$this->addLine(" * Created on " . date("d-m-Y H:i:s"), 0);
		$this->addLine(" * @author " . $this->project->getAuthor(), 0);
		$this->addLine(" * @package " . $this->project->getName(), 0);
		$this->addLine(" * error prefix " . $t->getErrorPrefix(), 0);
		$this->addLine(" * Wygenerowano przy pomocy SimplePHPDAOClassGenerator ver " . Project::VERSION, 0);
		$this->addLine(" * Zaprojektowane wg schematu CRUD http://pl.wikipedia.org/wiki/CRUD", 0);
		$this->addLine(" * klasa wygenerowana automatycznie proszę nie wprowadzać zmian pod rygorem ", 0);
		$this->addLine(" * NADPISANIA BEZ OSTRZEŻENIA ", 0);
		$this->addLine(" */", 0);
	}
	// -------------------------------------------------------------------------
	protected function generateNameSpace()
	{
		if(strlen($this->project->getNameSpace()) > 0)
		{
			$this->addLine("namespace " . $this->project->getNameSpace() . ";", 0);
		}
	}
	// -------------------------------------------------------------------------
	protected function open(Table $t)
	{
		@mkdir($this->project->getProjectFolder() . "\\" . $this->project->getDaoFolder(), 0777, true);
		$this->fileHandle = fopen($this->project->getProjectFolder() . "\\" . $this->project->getDaoFolder() . "\\" . $t->getClassName() . "DAO.php", "w");
		$this->addLine("<?php", 0);
	}
	// -------------------------------------------------------------------------
	protected function close()
	{
		$this->addLine("?>", 0, false);
		fclose($this->fileHandle);
	}
	// -------------------------------------------------------------------------
}
// =============================================================================
function RandomStringLetterOnly($dlugosc)
{
	$keychars = "ABCDEFGHIJKLMNOPQRSTUVWXYZ";
	$randkey = "";
	$max = strlen($keychars) - 1;
	for($i = 0;$i < $dlugosc;$i++)
	{
		$randkey .= substr($keychars, rand(0, $max), 1);
	}
	return $randkey;
}
?>
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
	protected $file = null;
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
			@mkdir($this->project->getProjectFolder() . "\\" . $this->project->getDaoFolder(), 0777, true);
			$this->file = $this->project->getProjectFolder() . "\\" . $this->project->getDaoFolder() . "\\" . $t->getClassName() . "DAO.php";
			if(FORCE_GEN_DAO || !file_exists($this->file))
			{
				$this->open($t);
				$this->generateNameSpace($t);
				$this->prepareClass($t);
				$this->close();
			}
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
	/**
	 * Dodaje linię podkreślenia &lt;$indent&gt;// &lt;$spacer&gt; x &lt;$cols&gt;
	 * @param string $spacer
	 * @param number $indent
	 * @param number $cols
	 */
	protected function addSpacer($spacer = "-", $indent = 1, $cols = 73)
	{
		$this->addLine("// " . str_repeat($spacer, $cols), $indent);
	}
	// -------------------------------------------------------------------------
	protected function addPhpDoc(array $linie, int $indent = 0)
	{
		$this->addLine("/**", $indent);
		foreach($linie as $linia)
		{
			$ls = explode("\n", $linia);
			foreach($ls as $l)
			{
				$this->addLine(" * " . $l, $indent);
			}
		}
		$this->addLine(" */", $indent);
	}
	// -------------------------------------------------------------------------
	protected function prepareClass(Table $t)
	{
		$this->generateClassDocumentation($t);
		$this->generateClassHead($t);
		$this->generateProperties($t);
		$this->generateConstructor($t);
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
		if($t->getTableType() == 'table')
		{
			$this->generateCreate($t);
			$this->generateUpdate($t);
			$this->generateDestroy($t);
		}
		else
		{
			$this->generateFakeSave();
		}
		$this->generateSetAllFromDB($t);
		$this->generateGetAllForForeignColumn($t);
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
		$this->addSpacer();
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
		$this->addLine(" * @return " . $t->getClassName(), 1);
		$this->addLine(" */", 1);
		$this->addLine("static function getByDataSource(DataSource \$db): " . $t->getClassName(), 1);
		$this->addLine("{", 1);
		$this->addLine("\$key = " . implode(" . \"_\" . ", $tmp1) . ";", 2);
		$this->addLine("if(!isset(self::\$instance[\$key]))", 2);
		$this->addLine("{", 2);
		$this->addLine("self::\$instance[\$key] = new " . $t->getClassName() . "();", 3);
		$this->addLine("self::\$instance[\$key]->setAllFromDB(\$db);", 3);
		$this->addLine("}", 2);
		$this->addLine("return self::\$instance[\$key];", 2);
		$this->addLine("}", 1);
		$this->addSpacer();
	}
	// -------------------------------------------------------------------------
	protected function generateUpdateFactoryIndex(Table $t)
	{
		$tmp1 = array();
		foreach($t->getPk() as $c) /* @var $c Column */
		{
			$tmp1[] = "\$" . lcfirst($t->getClassName()) . "->get" . ucfirst($c->getClassFieldName()) . "()";
		}

		$this->addLine("/**", 1);
		$this->addLine(" * @param " . $t->getClassName() . " \$" . lcfirst($t->getClassName()) . "", 1);
		$this->addLine(" */", 1);
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
		$this->addSpacer();
	}
	// -------------------------------------------------------------------------
	protected function generateStaticGetMethod(Table $t)
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
			$this->addLine(" * @param " . $c->getPHPType() . " \$" . $c->getClassFieldName() . "", 1);
			$tmp1[] = "?" . $c->getPHPType() . " \$" . $c->getClassFieldName() . " = null";
			switch($c->getType())
			{
				case ColumnType::FLOAT:
				case ColumnType::NUMBER:
					$tmp2[] = "is_numeric(\$" . $c->getClassFieldName() . ")";
					break;
				default :
					$tmp2[] = "!empty(\$" . $c->getClassFieldName() . ")";
					break;
			}
			$tmp3[] = "\$" . $c->getClassFieldName();
		}

		$this->addLine(" * @return " . $t->getClassName() . "", 1);
		$this->addLine(" */", 1);
		$this->addLine("static function get(" . implode(", ", $tmp1) . "): " . $t->getClassName() . "", 1);
		$this->addLine("{", 1);
		$this->addLine("\$retval = null;", 2);
		$this->addLine("if(" . implode(" && ", $tmp2) . ")", 2);
		$this->addLine("{", 2);
		$this->addLine("if(!isset(self::\$instance[" . implode(" . \"_\" . ", $tmp3) . "]))", 3);
		$this->addLine("{", 3);
		$this->addLine("self::\$instance[" . implode(" . \"_\" . ", $tmp3) . "] = new " . $t->getClassName() . "(" . implode(", ", $tmp3) . ");", 4);
		$this->addLine("}", 3);
		$this->addLine("\$retval = self::\$instance[" . implode(" . \"_\" . ", $tmp3) . "];", 3);
		$this->addLine("}", 2);
		$this->addLine("else", 2);
		$this->addLine("{", 2);
		$this->addLine("\$retval = self::\$instance[\"\\\$\".count(self::\$instance)] = new " . $t->getClassName() . "();", 3);
		$this->addLine("}", 2);
		$this->addLine("if(count(self::\$instance) > static::CACHE_SIZE)", 2);
		$this->addLine("{", 2);
		$this->addLine("array_shift(self::\$instance);", 3);
		$this->addLine("}", 2);
		$this->addLine("return \$retval;", 2);
		$this->addLine("}", 1);
		$this->addSpacer();
	}
	// -------------------------------------------------------------------------
	protected function generateGetAllForForeignColumn(Table $t)
	{
		foreach($t->getFk() as $fk) /* @var $fk ForeignKey */
		{
			$fkTable = $fk->getTable();
			$functioName = "getAllBy";
			foreach($fk->getColumn() as $c) /* @var $c ConnectedColumn */
			{
				$classFieldName = Column::convertFieldNameToClassName($c->fkColumnName);
				if(substr($classFieldName, 0, 2) == "id")
				{
					$functioName .= substr($classFieldName, 2);
				}
				else
				{
					$functioName .= $classFieldName;
				}
			}

			$this->addLine("/**", 1);
			$this->addLine(" * Metoda zwraca kolekcję obiektów klasy " . $t->getClassName(), 1);
			$this->addLine(" * @return Collection &lt;" . $t->getClassName() . "&gt; ", 1);
			$this->addLine(" */", 1);
			$this->addLine("public static function " . $functioName . "(" . $fkTable->getClassName() . "DAO \$" . lcfirst($fkTable->getClassName()) . "): Collection", 1);
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
			$this->addLine("return new Collection(\$db, " . $t->getClassName() . "::get());", 2);
			$this->addLine("}", 1);
			$this->addSpacer();
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
			// elseif($c instanceof ColumnForeignKey)
			// {
			// foreach($c->getTable()->getColumny() as $z)/* @var $z Column */
			// {
			// if($z instanceof ColumnPrimaryKey)
			// {
			// $data[$z->getKey()] = $z;
			// }
			// }
			// }
		}

		$this->addLine("/**", 1);
		$this->addLine(" * Metoda usuwa obiekt klasy " . $t->getClassName(), 1);
		$this->addLine(" * będącego rekordem w tabeli " . $t->getName(), 1);
		$this->addLine(" * @return bool", 1);
		$this->addLine(" */", 1);
		$this->addLine("protected function destroy(): bool", 1);
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
			$this->addLine("\$db->setParam(\"" . mb_strtoupper($c->getName()) . "\", \$this->get" . ucfirst($c->getClassFieldName()) . "());", 2);
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
		$this->addLine("AddAlert(\"" . $t->getErrorPrefix() . "04 Usunięcie rekordu z tabeli " . $t->getName() . " nie udało się.\");", 3);
		$this->addLine("return false;", 3);
		$this->addLine("}", 2);
		$this->addLine("}", 1);
		$this->addSpacer();
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
			// elseif($c instanceof ColumnForeignKey)
			// {
			// foreach($c->getTable()->getColumny() as $z)/* @var $z Column */
			// {
			// if($z instanceof ColumnPrimaryKey)
			// {
			// $data[$z->getKey()] = $z;
			// }
			// }
			// }
		}

		$this->addLine("/**", 1);
		$this->addLine(" * Metoda odczytuje obiekt klasy " . $t->getClassName(), 1);
		$this->addLine(" * (możesz odczytać każdy atrybut obiektu funkcją get...())", 1);
		$this->addLine(" * wybrany jako rekord z tabeli " . $t->getName(), 1);
		$this->addLine(" * @return bool", 1);
		$this->addLine(" */", 1);
		$tmp1 = array();
		foreach($pk as $c)/* @var $c Column */
		{
			$tmp1[] = "\$" . $c->getClassFieldName();
		}
		$this->addLine("protected function retrieve(" . implode(", ", $tmp1) . "): bool", 1);
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
		$this->addSpacer();
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
			// elseif($c instanceof ColumnForeignKey)
			// {
			// foreach($c->getTable()->getColumny() as $z)/* @var $z Column */
			// {
			// if($z instanceof ColumnPrimaryKey)
			// {
			// $data[$z->getKey()] = $z;
			// }
			// }
			// }
		}

		$this->addLine("/**", 1);
		$this->addLine(" * Metoda modyfikuje obiekt klasy " . $t->getClassName(), 1);
		$this->addLine(" * aktualizując rekord w tabeli " . $t->getName(), 1);
		$this->addLine(" * @return bool", 1);
		$this->addLine(" */", 1);
		$this->addLine("protected function update(): bool", 1);
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
		$this->addLine("AddAlert(\"" . $t->getErrorPrefix() . "03 Aktualizacja rekordu w tabeli " . $t->getName() . " nie udała się.\");", 3);
		$this->addLine("return false;", 3);
		$this->addLine("}", 2);
		$this->addLine("}", 1);
		$this->addSpacer();
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
		$this->addLine(" * Metoda dodaje obiekt klasy " . $t->getClassName(), 1);
		$this->addLine(" * wstawiając rekord do tabeli " . $t->getName(), 1);
		$this->addLine(" * @return bool", 1);
		$this->addLine(" */", 1);
		$this->addLine("protected function create(): bool", 1);
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
		$this->addLine("\$sql  = \"INSERT INTO \" . " . $t->getSchema() . " . \"." . $t->getName() . " (" . implode(", ", $columns) . ") \";", 2);
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
		$this->addLine("AddAlert(\"" . $t->getErrorPrefix() . "02 Wstawienie rekordu do tabeli " . $t->getName() . " nie udało się.\");", 3);
		$this->addLine("return false;", 3);
		$this->addLine("}", 2);
		$this->addLine("}", 1);
		$this->addSpacer();
	}
	// -------------------------------------------------------------------------
	protected function generateGetterObject(Table $table)
	{
		foreach($table->getFk() as $fk)/* @var $fk ForeignKey */
		{
			$tmp1 = array();
			$functionName = "get"; // . $fk->getTable()->getClassName();
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
								if(substr($x->getClassFieldName(), 0, 2) == "id")
								{
									$functionName .= substr($x->getClassFieldName(), 2);
								}
								else
								{
									$functionName .= $x->getClassFieldName();
								}
							}
						}
					}
				}
			}
			$this->addLine("/**", 1);
			$this->addLine(" * @return " . $fk->getTable()->getClassName(), 1);
			$this->addLine(" */", 1);
			$this->addLine("public function " . $functionName . "(): " . $fk->getTable()->getClassName(), 1);
			$this->addLine("{", 1);
			$this->addLine("return " . $fk->getTable()->getClassName() . "::get(" . implode(", ", $tmp1) . ");", 2);
			$this->addLine("}", 1);
			$this->addSpacer();
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
		foreach($this->project->getTables() as $t)
		{
			if($t != $table)
			{
				foreach($t->getFk() as $fk)/* @var $fk ForeignKey */
				{
					if($fk->getTableName() == $table->getName() && $fk->getTableSchema() == $table->getSchema())
					{
						$functionName = "getAllBy";
						$objectName = "";
						foreach($fk->getColumn() as $c) /* @var $c ConnectedColumn */
						{
							$classFieldName = Column::convertFieldNameToClassName($c->fkColumnName);
							if(substr($classFieldName, 0, 2) == "id")
							{
								$objectName .= substr($classFieldName, 2);
								$functionName .= substr($classFieldName, 2);
							}
							else
							{
								$objectName .= $classFieldName;
								$functionName .= $classFieldName;
							}
						}

						$this->addLine("/**", 1);
						$this->addLine(" * Metoda zwraca kolekcję obiektów klasy " . $t->getClassName(), 1);
						$this->addLine(" * @return Collection &lt;" . $t->getClassName() . "&gt; ", 1);
						$this->addLine(" */", 1);
						$this->addLine("public function get" . $t->getClassName() . "sFor" . $objectName . "(): Collection", 1);
						$this->addLine("{", 1);
						$this->addLine("return " . $t->getClassName() . "::" . $functionName . "(\$this);", 2);
						$this->addLine("}", 1);
						$this->addSpacer();
					}
				}
			}
		}
	}
	// -------------------------------------------------------------------------
	protected function generateUseObjects(Table $table)
	{
		$uses = array(
					$table->getClassName() => $table->getClassName() );
		foreach($this->project->getTables() as $t)
		{
			foreach($t->getFk() as $fk)/* @var $fk ForeignKey */
			{
				if($t != $table)
				{
					if($fk->getTableName() == $table->getName() && $fk->getTableSchema() == $table->getSchema())
					{
						$uses["Collection"] = "Collection";
						$uses[$t->getClassName()] = $t->getClassName();
					}
				}
				else
				{
					$uses["Collection"] = "Collection";
					$uses[$fk->getTable()->getClassName()] = $fk->getTable()->getClassName();
				}
			}
		}
		return $uses;
	}
	// -------------------------------------------------------------------------
	protected function generateGetter($classFieldName)
	{
		$this->addLine("public function get" . ucfirst($classFieldName) . "()", 1);
		$this->addLine("{", 1);
		$this->addLine("return \$this->" . $classFieldName . ";", 2);
		$this->addLine("}", 1);
		$this->addSpacer();
	}
	// -------------------------------------------------------------------------
	protected function generateIsReaded()
	{
		$this->addLine("protected function isReaded(): bool", 1);
		$this->addLine("{", 1);
		$this->addLine("return \$this->readed;", 2);
		$this->addLine("}", 1);
		$this->addSpacer();
		$this->addLine("protected function setReaded()", 1);
		$this->addLine("{", 1);
		$this->addLine("\$this->readed = true;", 2);
		$this->addLine("}", 1);
		$this->addSpacer();
	}
	// -------------------------------------------------------------------------
	protected function generateSetter(Column $c, $private = false)
	{
		if($private == false)
		{
			if($c->isAutoGenerated() && $c->isPK())
			{
				$use = "protected";
			}
			else
			{
				$use = "public";
			}
		}
		else
		{
			$use = "private";
		}
		$this->addLine($use . " function set" . ucfirst($c->getClassFieldName()) . "(\$" . $c->getClassFieldName() . ")", 1);
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
				$this->addLine("\$this->" . $c->getClassFieldName() . " = mb_substr(\$" . $c->getClassFieldName() . ",0," . $c->getSize() . ");", 3);
				$this->addLine("}", 2);
				break;
			case ColumnType::NUMBER:
				$this->addLine("if(is_numeric(\$" . $c->getClassFieldName() . "))", 2);
				$this->addLine("{", 2);
				$this->addLine("\$this->" . $c->getClassFieldName() . " = round(\$" . $c->getClassFieldName() . "," . intval($c->getScale()) . ");", 3);
				$this->addLine("}", 2);
				$this->addLine("else", 2);
				$this->addLine("{", 2);
				$this->addLine("\$this->" . $c->getClassFieldName() . " = null;", 3);
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
			case ColumnType::TIME:
				$this->addLine("if(empty(\$" . $c->getClassFieldName() . "))", 2);
				$this->addLine("{", 2);
				$this->addLine("\$this->" . $c->getClassFieldName() . " = null;", 3);
				$this->addLine("}", 2);
				$this->addLine("else", 2);
				$this->addLine("{", 2);
				$this->addLine("\$this->" . $c->getClassFieldName() . " = date(PHP_TIME_FORMAT,strtotime(\$" . $c->getClassFieldName() . "));", 3);
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
			case ColumnType::CLOB:
				$this->addLine("if(empty(\$" . $c->getClassFieldName() . "))", 2);
				$this->addLine("{", 2);
				$this->addLine("\$this->" . $c->getClassFieldName() . " = null;", 3);
				$this->addLine("}", 2);
				$this->addLine("elseif(is_object(\$" . $c->getClassFieldName() . "))", 2);
				$this->addLine("{", 2);
				$this->addLine("\$this->" . $c->getClassFieldName() . " = \$" . $c->getClassFieldName() . "->read(\$" . $c->getClassFieldName() . "->size());", 3);
				$this->addLine("}", 2);
				$this->addLine("else", 2);
				$this->addLine("{", 2);
				$this->addLine("\$this->" . $c->getClassFieldName() . " = \$" . $c->getClassFieldName() . ";", 3);
				$this->addLine("}", 2);
				break;
			case ColumnType::FLOAT:
			case ColumnType::TEXT:
			case ColumnType::ENUM:
			default :
				$this->addLine("\$this->" . $c->getClassFieldName() . " = \$" . $c->getClassFieldName() . ";", 2);
				break;
		}
		$this->addLine("}", 1);
		$this->addSpacer();
	}
	// -------------------------------------------------------------------------
	protected function generateSetters(Table $t)
	{
		foreach($t->getColumny() as $c)/* @var $c Column */
		{
			$this->generateSetter($c, $t->getTableType() != 'table');
		}
	}
	// -------------------------------------------------------------------------
	protected function generateSetAllFromDB(Table $t)
	{
		$this->addLine("/**", 1);
		$this->addLine(" * Metoda ustawia wszystkie atrybuty w obiekcie klasy " . $t->getClassName(), 1);
		$this->addLine(" * pobrane z obiektu klasy DB", 1);
		$this->addLine(" * @return void", 1);
		$this->addLine(" */", 1);
		$this->addLine("protected function setAllFromDB(DataSource \$db)", 1);
		$this->addLine("{", 1);
		foreach($t->getColumny() as $c) /* @var $c Column */
		{
			// if($c instanceof ColumnForeignKey)
			// {
			// $columns = $c->getTable()->getColumny();
			// foreach($c->getClassFieldName() as $key => $s)
			// {
			// $this->addLine("\$this->set" . ucfirst($s) . "(\$db->f(\"" . $columns[$key]->getName() . "\"));", 2);
			// }
			// }
			// else
			// {
			$this->addLine("\$this->set" . ucfirst($c->getClassFieldName()) . "(\$db->f(\"" . $c->getKey() . "\"));", 2);
			// }
		}
		$this->addLine("\$this->setReaded();", 2);

		$this->addLine("}", 1);
		$this->addSpacer();
	}
	// -------------------------------------------------------------------------
	protected function generateConstructor(Table $t)
	{
		$this->addLine("/**", 1);
		$tmp1 = array();
		$tmp2 = array();
		$tmp3 = array();
		foreach($t->getPk() as $c) /* @var $c Column */
		{
			$this->addLine(" * @param " . $c->getPHPType() . " \$" . $c->getClassFieldName() . "", 1);
			$tmp1[] = "?" . $c->getPHPType() . " \$" . $c->getClassFieldName() . " = null";
			$tmp2[] = "!is_null(\$" . $c->getClassFieldName() . ")";
			$tmp3[] = "\$" . $c->getClassFieldName() . "";
		}
		$this->addLine(" * @throws \\Exception", 1);
		$this->addLine(" */", 1);
		$this->addLine("protected function __construct(" . implode(", ", $tmp1) . ")", 1);
		$this->addLine("{", 1);
		$this->addLine("if(" . implode(" && ", $tmp2) . ")", 2);
		$this->addLine("{", 2);

		$this->addLine("if(!\$this->retrieve(" . implode(", ", $tmp3) . "))", 3);
		$this->addLine("{", 3);
		$this->addLine("throw new \Exception(\"" . $t->getErrorPrefix() . "01 \" . " . $t->getSchema() . " . \"." . $t->getName() . "(\" . " . implode(" . \", \".", $tmp3) . " . \")  nie istnieje.\");", 4);
		$this->addLine("}", 3);
		$this->addLine("}", 2);
		$this->addLine("}", 1);
		$this->addSpacer();
	}
	// -------------------------------------------------------------------------
	protected function generateProperties(Table $table)
	{
		$this->addSpacer();
		$this->addPhpDoc(array(
								"Ilość rekordów pamiętanych w cache",
								"@var integer" ), 1);
		$this->addLine("const CACHE_SIZE = 100;", 1);
		$this->addSpacer();
		$this->addLine("protected static \$instance = array();", 1);
		$this->addSpacer();
		foreach($table->getColumny() as $c) /* @var $c Column */
		{
			$this->addLine("protected \$" . $c->getClassFieldName() . " = null;", 1);
		}
		$this->addLine("protected \$readed = false;", 1);
		$this->addSpacer();
	}
	// -------------------------------------------------------------------------
	protected function generateClassHead(Table $t)
	{
		$this->addLine("abstract class " . $t->getClassName() . "DAO implements DAO", 0);
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
		$this->addLine(" * Utworzono " . date("d-m-Y H:i:s"), 0);
		$this->addLine(" * tabela " . $t->getName(), 0);
		$this->addLine(" * error prefix " . $t->getErrorPrefix(), 0);
		$this->addLine(" * max error " . $t->getErrorPrefix() . "04", 0);
		$this->addLine(" * Wygenerowane przez SimplePHPDAOClassGenerator v" . Project::VERSION, 0);
		$this->addLine(" * https://sourceforge.net/projects/simplephpdaogen/code/branches/ENNEW ", 0);
		$this->addLine(" * Utworzono zgodnie ze schematem CRUD http://wikipedia.org/wiki/CRUD", 0);
		$this->addLine(" * Klasa wygenerowana automatycznie", 0);
		$this->addLine(" * NIE NALEŻY MODYFIKOWAĆ KODU", 0);
		$this->addLine(" * @author " . $this->project->getAuthor(), 0);
		$this->addLine(" * @package " . $this->project->getName(), 0);
		$this->addLine(" */", 0);
	}
	// -------------------------------------------------------------------------
	protected function generateNameSpace(Table $t)
	{
		if(strlen($this->project->getNameSpace()) > 0)
		{
			$this->addLine("namespace " . $this->project->getNameSpace() . "\\" . $this->project->getDaoFolder() . ";\n", 0);
			$this->addLine("use " . $this->project->getNameSpace() . "\\" . $this->project->getDbFolder() . "\\framework_dao\\DB;", 0);
			$this->addLine("use " . $this->project->getNameSpace() . "\\" . $this->project->getDbFolder() . "\\framework_dao\\iface\\DAO;", 0);
			$this->addLine("use " . $this->project->getNameSpace() . "\\" . $this->project->getDbFolder() . "\\framework_dao\\iface\\DataSource;", 0);
			$uses = $this->generateUseObjects($t);
			if(isset($uses["Collection"]))
			{
				$this->addLine("use " . $this->project->getNameSpace() . "\\" . $this->project->getDbFolder() . "\\framework_dao\\Collection;", 0);
				unset($uses["Collection"]);
			}
			foreach($uses as $obj)
			{
				$this->addLine("use " . $this->project->getNameSpace() . "\\" . $this->project->getObjFolder() . "\\" . $obj . ";", 0);
			}
		}
	}
	private function generateFakeSave()
	{
		$this->addPhpDoc(array(
								"Metoda niedozwolona w tym obiekcie\nzamarkowana tylko dla zgodności z interface DAO" ), 1);
		$this->addLine("public function save(): bool", 1);
		$this->addLine("{", 1);
		$this->addLine("return false;", 2);
		$this->addLine("}", 1);
		$this->addSpacer();
	}
	// -------------------------------------------------------------------------
	protected function open(Table $t)
	{
		$this->fileHandle = fopen($this->file, "w");
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
	for($i = 0; $i < $dlugosc; $i++)
	{
		$randkey .= substr($keychars, rand(0, $max), 1);
	}
	return $randkey;
}
?>
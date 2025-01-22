<?php
namespace braga\daogenerator\generator;

/**
 * Created on 23-03-2013 07:57:30
 * author Tomasz Gajewski
 * package frontoffice
 * error prefix
 */
class DAOFileGenerator
{
	// -----------------------------------------------------------------------------------------------------------------
	/**
	 * @var Project
	 */
	protected $project = null;
	protected $fileHandle = null;
	// -----------------------------------------------------------------------------------------------------------------
	function __construct(Project $p)
	{
		$this->project = $p;
	}
	// -----------------------------------------------------------------------------------------------------------------
	public function GO()
	{
		foreach($this->project->getTables() as $t)
			/* @var     $t Table */
		{
			$this->open($t);
			$this->generateNameSpace($t);
			$this->prepareClass($t);
			$this->close();
		}
	}
	// -----------------------------------------------------------------------------------------------------------------
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
	// -----------------------------------------------------------------------------------------------------------------
	protected function prepareClass(Table $t)
	{
		$this->generateClassDocumentation($t);
		$this->generateClassHead($t);
		$this->generateProperties($t);
		$this->generateConstruktor($t);
		$this->generateStaticGetMethod($t);
		$this->generateStaticGetForUpdateMethod($t);
		$this->generateUpdateFactoryIndex($t);
		$this->generateStaticGetByDataSourceMethod($t);
		$this->generateIsReaded();
		$this->generateSetters($t);
		$this->generateSetterObject($t);
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
	// -----------------------------------------------------------------------------------------------------------------
	protected function generateGetKey($t)
	{
		$pkField = array();
		foreach($t->getColumny() as $c)
			/* @var $c Column */
		{
			if($c->isPK())
			{
				$pkField[] = $c;
			}
		}
		$tmp1 = array();
		foreach($pkField as $c)
			/* @var $c Column */
		{
			$tmp1[] = "\$this->get" . ucfirst($c->getClassFieldName()) . "()";
		}
		$this->addLine("public function getKey()", 1);
		$this->addLine("{", 1);
		$this->addLine("return " . implode(" . \"_\" . ", $tmp1) . ";", 2);
		$this->addLine("}", 1);
		$this->addLine("// -----------------------------------------------------------------------------------------------------------------", 1);
	}
	// -----------------------------------------------------------------------------------------------------------------
	protected function generateStaticGetByDataSourceMethod($t)
	{
		$pkField = array();
		foreach($t->getColumny() as $c)
			/* @var $c Column */
		{
			if($c->isPK())
			{
				$pkField[] = $c;
			}
		}
		$tmp1 = array();
		foreach($pkField as $c)
			/* @var $c Column */
		{
			$tmp1[] = "\$db->f(\"" . $c->getName() . "\")";
		}
		$this->addLine("/**", 1);
		$this->addLine(" * @param DataSource \$db", 1);
		$this->addLine(" * @return static", 1);
		$this->addLine(" */", 1);
		$this->addLine("public static function getByDataSource(DataSource \$db)", 1);
		$this->addLine("{", 1);
		$this->addLine("\$key = " . implode(" . \"_\" . ", $tmp1) . ";", 2);
		$this->addLine("if(!isset(self::\$instance[\$key]))", 2);
		$this->addLine("{", 2);
		$this->addLine("self::\$instance[\$key] = new static();", 3);
		$this->addLine("self::\$instance[\$key]->setAllFromDB(\$db);", 3);
		$this->addLine("}", 2);
		$this->addLine("return self::\$instance[\$key];", 2);
		$this->addLine("}", 1);
		$this->addLine("// -----------------------------------------------------------------------------------------------------------------", 1);
	}
	// -----------------------------------------------------------------------------------------------------------------
	protected function generateUpdateFactoryIndex(Table $t)
	{
		$tmp1 = array();
		foreach($t->getPk() as $c)
			/* @var $c Column */
		{
			$tmp1[] = "\$" . lcfirst($t->getClassName()) . "->get" . ucfirst($c->getClassFieldName()) . "()";
		}

		$this->addLine("protected static function updateFactoryIndex(" . $t->getClassName() . "DAO \$" . lcfirst($t->getClassName()) . ")", 1);
		$this->addLine("{", 1);
		$this->addLine("\$key = array_search(\$" . lcfirst($t->getClassName()) . ", self::\$instance, true);", 2);
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
		$this->addLine("// -----------------------------------------------------------------------------------------------------------------", 1);
	}
	// -----------------------------------------------------------------------------------------------------------------
	protected function generateStaticGetMethod(Table $t)
	{
		$pkField = array();
		foreach($t->getColumny() as $c)
			/* @var $c Column */
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
		foreach($pkField as $c)
			/* @var $c Column */
		{
			$this->addLine(" * @param int \$" . $c->getClassFieldName() . "", 1);
			$tmp1[] = "\$" . $c->getClassFieldName() . " = null";
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

		$this->addLine(" * @return static", 1);
		$this->addLine(" */", 1);
		$this->addLine("public static function get(" . implode(", ", $tmp1) . ")", 1);
		$this->addLine("{", 1);
		$this->addLine("if(" . implode(" && ", $tmp2) . ")", 2);
		$this->addLine("{", 2);
		$this->addLine("if(!isset(self::\$instance[" . implode(" . \"_\" . ", $tmp3) . "]))", 3);
		$this->addLine("{", 3);
		$this->addLine("self::\$instance[" . implode(" . \"_\" . ", $tmp3) . "] = new static(" . implode(", ", $tmp3) . ");", 4);
		$this->addLine("}", 3);
		$this->addLine("return self::\$instance[" . implode(" . \"_\" . ", $tmp3) . "];", 3);
		$this->addLine("}", 2);
		$this->addLine("else", 2);
		$this->addLine("{", 2);
		$this->addLine("return new static();", 3);
		$this->addLine("}", 2);
		$this->addLine("}", 1);
		$this->addLine("// -----------------------------------------------------------------------------------------------------------------", 1);
	}
	// -----------------------------------------------------------------------------------------------------------------
	protected function generateStaticGetForUpdateMethod(Table $table)
	{
		$pkField = array();
		foreach($table->getColumny() as $column)
		{
			if($column->isPK())
			{
				$pkField[] = $column;
			}
		}

		$this->addLine("/**", 1);
		$tmp1 = array();
		$tmp2 = array();
		$tmp3 = array();
		foreach($pkField as $column)
		{
			$this->addLine(" * @param int \$" . $column->getClassFieldName() . "", 1);
			$tmp1[] = "\$" . $column->getClassFieldName() . " = null";
			switch($column->getType())
			{
				case ColumnType::FLOAT:
				case ColumnType::NUMBER:
					$tmp2[] = "is_numeric(\$" . $column->getClassFieldName() . ")";
					break;
				default :
					$tmp2[] = "!empty(\$" . $column->getClassFieldName() . ")";
					break;
			}
			$tmp3[] = "\$" . $column->getClassFieldName();
		}

		$this->addLine(" * @return static", 1);
		$this->addLine(" */", 1);
		$this->addLine("public static function getForUpdate(" . implode(", ", $tmp1) . ")", 1);
		$this->addLine("{", 1);
		$this->addLine("if(" . implode(" && ", $tmp2) . ")", 2);
		$this->addLine("{", 2);
		$this->addLine("if(isset(self::\$instance[" . implode(" . \"_\" . ", $tmp3) . "]))", 3);
		$this->addLine("{", 3);
		$this->addLine("unset(self::\$instance[" . implode(" . \"_\" . ", $tmp3) . "]);", 4);
		$this->addLine("}", 3);
		$this->addLine("}", 2);
		$this->addLine("else", 2);
		$this->addLine("{", 2);
		$this->addLine("throw new \\braga\\tools\\exception\\BragaException(\"" . $table->getErrorPrefix() . "05 Empty or wrong object id type\");", 3);
		$this->addLine("}", 2);
		$pk = array();
		foreach($table->getColumny() as $column)
		{
			if($column->isPK())
			{
				$pk[$column->getKey()] = $column;
			}
		}
		$this->addLine("\$db = new DB();", 2);
		$this->addLine("\$sql = <<<SQL", 2);
		$this->addLine("SELECT * ", 3);
		$this->addLine("FROM {$table->getName()} ", 3);
		$separator = "WHERE";
		$i = 3;
		foreach($pk as $column)
		{
			$val = mb_strtoupper($column->getName());
			$this->addLine("{$separator} {$column->getName()} = :{$val} ", $i);
			$i = 4;
			$separator = "AND";
		}
		$this->addLine("FOR UPDATE ", 3);
		$this->addLine("SQL;", 0);
		foreach($pk as $column)
		{
			$this->addLine("\$db->setParam(\"" . mb_strtoupper($column->getName()) . "\", \$" . $column->getClassFieldName() . ");", 2);
		}
		$this->addLine("\$db->query(\$sql);", 2);
		$this->addLine("if(\$db->nextRecord())", 2);
		$this->addLine("{", 2);
		$this->addLine("\$retval = self::getByDataSource(\$db); ", 3);
		$this->addLine("\$retval->forUpdate = true; ", 3);
		$this->addLine("return \$retval;", 3);
		$this->addLine("}", 2);
		$this->addLine("else", 2);
		$this->addLine("{", 2);
		$this->addLine("throw new \\braga\\tools\\exception\\BragaException(\"" . $table->getErrorPrefix() . "06 " . $table->getName() . "(\" . " . implode(" . \", \".", $tmp3) . " . \")  does not exists\");", 3);
		$this->addLine("}", 2);
		$this->addLine("}", 1);
		$this->addLine("// -----------------------------------------------------------------------------------------------------------------", 1);
	}
	// -----------------------------------------------------------------------------------------------------------------
	protected function generateGetAllForForeginColumn(Table $table)
	{
		foreach($table->getFk() as $fk)
		{
			$fkTable = $fk->getTable();
			$functioName = "getAllBy";
			foreach($fk->getColumn() as $c)
				/* @var $c ConnectedColumn */
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
			$this->addLine(" * Methods return colection of  " . $table->getClassName(), 1);
			$this->addLine(" * @return \\braga\\db\\Collection|\\" . $this->project->getNameSpace() . $this->project->getObjFolder() . "\\" . $table->getClassName() . "[]", 1);
			$this->addLine(" */", 1);
			$this->addLine("public static function " . $functioName . "(" . $fkTable->getClassName() . "DAO \$" . lcfirst($fkTable->getClassName()) . ")", 1);
			$this->addLine("{", 1);
			$this->addLine("\$db = new DB();", 2);
			$this->addLine("\$sql = <<<SQL", 2);
			$this->addLine("SELECT * ", 3);
			$this->addLine("FROM {$table->getName()} ", 3);

			$separator = "WHERE";
			$tab = 3;
			foreach($fk->getColumn() as $c)
			{
				foreach($table->getColumny() as $column)
				{
					if($c->fkColumnName == $column->getName())
					{
						$val = mb_strtoupper($column->getName());
						$this->addLine("{$separator} {$column->getName()} = :{$val} ", $tab);
						$separator = "AND";
						$tab = 4;
					}
				}
			}
			$this->addLine("SQL;", 0);

			foreach($fk->getColumn() as $connectedColumn)
			{
				foreach($table->getColumny() as $column)
				{
					if($connectedColumn->fkColumnName == $column->getName())
					{
						foreach($fk->getTable()->getPk() as $pkColumn)
						{
							if($pkColumn->getName() == $connectedColumn->pkColumnName)
							{
								$this->addLine("\$db->setParam(\"" . mb_strtoupper($column->getName()) . "\", \$" . lcfirst($fkTable->getClassName()) . "->get" . ucfirst($pkColumn->getClassFieldName()) . "());", 2);
							}
						}
					}
				}
			}

			$this->addLine("\$db->query(\$sql);", 2);
			$this->addLine("return new \\braga\\db\\Collection(\$db, \\" . $this->project->getNameSpace() . $this->project->getObjFolder() . "\\" . $table->getClassName() . "::get());", 2);
			$this->addLine("}", 1);
			$this->addLine("// -----------------------------------------------------------------------------------------------------------------", 1);
		}
	}
	// -----------------------------------------------------------------------------------------------------------------
	protected function generateDestroy(Table $t)
	{
		$pk = array();
		foreach($t->getColumny() as $column)
		{
			if($column->isPK())
			{
				$pk[$column->getKey()] = $column;
			}
		}

		$this->addLine("/**", 1);
		$this->addLine(" * Method removes object of class " . $t->getClassName(), 1);
		$this->addLine(" * removed are record from table " . $t->getName(), 1);
		$this->addLine(" * @return boolean", 1);
		$this->addLine(" */", 1);
		$this->addLine("protected function destroy()", 1);
		$this->addLine("{", 1);
		$this->addLine("\$db = new DB();", 2);
		$this->addLine("\$sql = <<<SQL", 2);
		$this->addLine("DELETE ", 3);
		$this->addLine("FROM {$t->getName()} ", 3);
		$separator = "WHERE";
		$tab = 3;
		foreach($pk as $column)
		{
			$val = mb_strtoupper($column->getName());
			$this->addLine("{$separator} {$column->getName()} = :{$val} ", $tab);
			$separator = "AND";
			$tab = 4;
		}
		$this->addLine("SQL;", 0);
		foreach($pk as $column)
		{
			$this->addLine("\$db->setParam(\"" . mb_strtoupper($column->getName()) . "\", \$this->get" . ucfirst($column->getClassFieldName()) . "());", 2);
		}
		$this->addLine("\$db->query(\$sql);", 2);
		$this->addLine("if(1 == \$db->getRowAffected())", 2);
		$this->addLine("{", 2);
		$this->addLine("return true;", 3);
		$this->addLine("}", 2);
		$this->addLine("else", 2);
		$this->addLine("{", 2);
		$this->addLine("throw new \\braga\\db\\exception\\ExecutionSqlException(\$db, \"" . $t->getErrorPrefix() . "04 Delete record from table " . $t->getName() . " fail\");", 3);
		$this->addLine("}", 2);
		$this->addLine("}", 1);
		$this->addLine("// -----------------------------------------------------------------------------------------------------------------", 1);
	}
	// -----------------------------------------------------------------------------------------------------------------
	protected function generateRead(Table $t)
	{
		$pk = array();
		foreach($t->getColumny() as $column)
		{
			if($column->isPK())
			{
				$pk[$column->getKey()] = $column;
			}
		}

		$this->addLine("/**", 1);
		$this->addLine(" * Method read object of class " . $t->getClassName() . " you can read all of atrib by get...() function", 1);
		$this->addLine(" * select record from table " . $t->getName(), 1);
		$this->addLine(" * @return boolean", 1);
		$this->addLine(" */", 1);
		$tmp1 = array();
		foreach($pk as $column)
			/* @var $column Column */
		{
			$tmp1[] = "\$" . $column->getClassFieldName();
		}
		$this->addLine("protected function retrieve(" . implode(", ", $tmp1) . ")", 1);
		$this->addLine("{", 1);
		$this->addLine("\$db = new DB();", 2);
		$this->addLine("\$sql = <<<SQL", 2);
		$this->addLine("SELECT * ", 3);
		$this->addLine("FROM {$t->getName()} ", 3);
		$separator = "WHERE";
		$tab = 3;
		foreach($pk as $column)
		{
			$val = mb_strtoupper($column->getName());
			$this->addLine("{$separator} {$column->getName()} = :{$val} ", $tab);
			$separator = "AND";
			$tab = 4;
		}
		$this->addLine("SQL;", 0);
		foreach($pk as $column)
		{
			$this->addLine("\$db->setParam(\"" . mb_strtoupper($column->getName()) . "\", \$" . $column->getClassFieldName() . ");", 2);
		}
		$this->addLine("\$db->query(\$sql);", 2);
		$this->addLine("if(\$db->nextRecord())", 2);
		$this->addLine("{", 2);
		$this->addLine("\$this->setAllFromDB(\$db);", 3);
		$this->addLine("return true;", 3);
		$this->addLine("}", 2);
		$this->addLine("else", 2);
		$this->addLine("{", 2);
		$this->addLine("throw new \\braga\\db\\exception\\ExecutionSqlException(\$db, \"" . $t->getErrorPrefix() . "07 Read record from table " . $t->getName() . " fail\");", 3);
		$this->addLine("}", 2);
		$this->addLine("}", 1);
		$this->addLine("// -----------------------------------------------------------------------------------------------------------------", 1);
	}
	// -----------------------------------------------------------------------------------------------------------------
	protected function generateUpdate(Table $t)
	{
		$data = [];
		$pk = [];
		foreach($t->getColumny() as $column)
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
		$this->addLine("\\braga\\graylogger\\BaseLogger::exception(new \\braga\\tools\\exception\\BragaException(\"" . $t->getErrorPrefix() . "09 SaveWithoutLock\", -1), \\Monolog\\Level::Critical, [ \"id\" => \$this->getKey(), \"class\" => \"" . $t->getClassName() . "\" ]);", 3);
		$this->addLine("}", 2);
		$this->addLine("\$db = new DB();", 2);
		$this->addLine("\$sql = <<<SQL", 2);
		$this->addLine("UPDATE {$t->getName()} ", 3);

		$columns = [];
		$params = [];
		foreach($t->getColumny() as $column)
		{
			if($column->getName() == strtoupper($column->getName()))
			{
				$columns[$column->getName()] = $column->getName();
			}
			else
			{
				$columns[$column->getName()] = "\\\"" . $column->getName() . "\\\"";
			}
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
			$val = mb_strtoupper($column->getName());
			$this->addLine("{$separator} {$column->getName()} = :{$val} ", $tab);
			$separator = "AND";
			$tab = 4;
		}
		$this->addLine("SQL;", 0);
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
	// -----------------------------------------------------------------------------------------------------------------
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
			if($column->getName() == strtoupper($column->getName()))
			{
				$columns[$column->getName()] = $column->getName();
			}
			else
			{
				$columns[$column->getName()] = "\\\"" . $column->getName() . "\\\"";
			}
			$params[$column->getName()] = preg_replace("/[^A-Z1-9]/", "", strtoupper($column->getName()));
			if(strlen($params[$column->getName()]) == 0)
			{
				$params[$column->getName()] = RandomStringLetterOnly(8);
			}
		}
		$this->addLine("INSERT INTO {$t->getName()} (" . implode(", ", $columns) . ") ", 3);
		$this->addLine("VALUES (:" . implode(", :", $params) . ") ", 3);

		$pkSequenced = false;
		if(count($pk) == 1)
		{
			if(current($pk)->isAutoGenerated())
			{
				$pkSequenced = true;
				$this->addLine("RETURNING " . current($pk)->getName() . " INTO :" . mb_strtoupper(current($pk)->getName()) . " ", 3);
			}
		}
		$this->addLine("SQL;", 0);

		if($pkSequenced)
		{
			if(current($pk)->getType() == ColumnType::NUMBER)
			{
				$size = current($pk)->getSize() + current($pk)->getScale();
			}
			else
			{
				$size = current($pk)->getSize();
			}

			$this->addLine("\$db->setParam(\"" . mb_strtoupper(current($pk)->getName()) . "\", \$this->get" . ucfirst(current($pk)->getClassFieldName()) . "(),false," . $size . ");", 2);
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
			$this->addLine("\$this->set" . ucfirst(current($pk)->getClassFieldName()) . "(\$db->getParam(\"" . mb_strtoupper(current($pk)->getName()) . "\"));", 3);
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
	// -----------------------------------------------------------------------------------------------------------------
	protected function generateGetterObject(Table $table)
	{
		foreach($table->getFk() as $fk)
		{
			$tmp1 = [];
			$functionName = "get";
			foreach($fk->getTable()->getPk() as $c)
			{
				foreach($fk->getColumn() as $cc)
				{
					if($c->getName() == $cc->pkColumnName)
					{
						foreach($table->getColumny() as $x)
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
			$this->addLine(" * @return \\" . $this->project->getNameSpace() . $this->project->getObjFolder() . "\\" . $fk->getTable()->getClassName(), 1);
			$this->addLine(" */", 1);
			$this->addLine("public function " . $functionName . "(\$forUpdate = false)", 1);
			$this->addLine("{", 1);
			$this->addLine("if(\$forUpdate)", 2);
			$this->addLine("{", 2);
			$this->addLine("return \\" . $this->project->getNameSpace() . $this->project->getObjFolder() . "\\" . $fk->getTable()->getClassName() . "::getForUpdate(" . implode(", ", $tmp1) . ");", 3);
			$this->addLine("}", 2);
			$this->addLine("else", 2);
			$this->addLine("{", 2);
			$this->addLine("return \\" . $this->project->getNameSpace() . $this->project->getObjFolder() . "\\" . $fk->getTable()->getClassName() . "::get(" . implode(", ", $tmp1) . ");", 3);
			$this->addLine("}", 2);
			$this->addLine("}", 1);
			$this->addLine("// -----------------------------------------------------------------------------------------------------------------", 1);
		}
	}
	// -----------------------------------------------------------------------------------------------------------------
	protected function generateSetterObject(Table $table)
	{
		foreach($table->getFk() as $fk)
		{
			$tmp1 = array();
			foreach($fk->getTable()->getPk() as $c)
			{
				foreach($fk->getColumn() as $cc)
				{
					if($c->getName() == $cc->pkColumnName)
					{
						foreach($table->getColumny() as $x)
						{
							if($x->getName() == $cc->fkColumnName)
							{
								$tmp1[] = "\$" . lcfirst($fk->getTable()->getClassName()) . "->get" . ucfirst($c->getClassFieldName()) . "()";
								if(substr($x->getClassFieldName(), 0, 2) == "id")
								{
									$functionName = substr($x->getClassFieldName(), 2);
								}
								else
								{
									$functionName = $x->getClassFieldName();
								}
							}
						}
					}
				}
			}
			$this->addLine("/**", 1);
			$this->addLine(" * @param " . $fk->getTable()->getClassName() . "DAO \$" . lcfirst($fk->getTable()->getClassName()), 1);
			$this->addLine(" */", 1);
			$this->addLine("public function set" . $functionName . "(" . $fk->getTable()->getClassName() . "DAO \$" . lcfirst($fk->getTable()->getClassName()) . ")", 1);
			$this->addLine("{", 1);

			foreach($tmp1 as $t)
			{
				$this->addLine("\$this->id" . ucfirst($functionName) . " = " . $t . ";", 2);
			}

			$this->addLine("}", 1);
			$this->addLine("// -----------------------------------------------------------------------------------------------------------------", 1);
		}
	}
	// -----------------------------------------------------------------------------------------------------------------
	protected function generateGetters(Table $table)
	{
		foreach($table->getColumny() as $column)
		{
			$this->generateGetter($column->getClassFieldName());
		}
	}
	// -----------------------------------------------------------------------------------------------------------------
	protected function generateGetterCollection(Table $table)
	{
		foreach($this->project->getTables() as $t)
		{
			if($t != $table)
			{
				foreach($t->getFk() as $fk)
					/* @var $fk ForeginKey */
				{
					if($fk->getTableName() == $table->getName() && $fk->getTableSchema() == $table->getSchema())
					{
						$functionName = "getAllBy";
						$objectName = "";
						foreach($fk->getColumn() as $c)
							/* @var $c ConnectedColumn */
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
						$this->addLine(" * Methods returns colection of objects " . $t->getClassName(), 1);
						$this->addLine(" * @return \\braga\\db\\Collection|" . "\\" . $this->project->getNameSpace() . $this->project->getObjFolder() . "\\" . $t->getClassName() . "[]", 1);
						$this->addLine(" */", 1);
						$this->addLine("public function get" . $t->getClassName() . "sFor" . $objectName . "()", 1);
						$this->addLine("{", 1);
						$this->addLine("return \\" . $this->project->getNameSpace() . $this->project->getObjFolder() . "\\" . $t->getClassName() . "::" . $functionName . "(\$this);", 2);
						$this->addLine("}", 1);
						$this->addLine("// -----------------------------------------------------------------------------------------------------------------", 1);
					}
				}
			}
		}
	}
	// -----------------------------------------------------------------------------------------------------------------
	protected function generateGetter($classFieldName)
	{
		$this->addLine("public function get" . ucfirst($classFieldName) . "()", 1);
		$this->addLine("{", 1);
		$this->addLine("return \$this->" . $classFieldName . ";", 2);
		$this->addLine("}", 1);
		$this->addLine("// -----------------------------------------------------------------------------------------------------------------", 1);
	}
	// -----------------------------------------------------------------------------------------------------------------
	protected function generateIsReaded()
	{
		$this->addLine("protected function isReaded() :bool", 1);
		$this->addLine("{", 1);
		$this->addLine("return \$this->readed;", 2);
		$this->addLine("}", 1);
		$this->addLine("// -----------------------------------------------------------------------------------------------------------------", 1);
		$this->addLine("protected function setReaded()", 1);
		$this->addLine("{", 1);
		$this->addLine("\$this->readed = true;", 2);
		$this->addLine("}", 1);
		$this->addLine("// -----------------------------------------------------------------------------------------------------------------", 1);
		$this->addLine("public function isForUpdate() :bool", 1);
		$this->addLine("{", 1);
		$this->addLine("return \$this->forUpdate;", 2);
		$this->addLine("}", 1);
		$this->addLine("// -----------------------------------------------------------------------------------------------------------------", 1);
	}
	// -----------------------------------------------------------------------------------------------------------------
	protected function generateSetter(Column $c)
	{
		$this->addLine("public function set" . ucfirst($c->getClassFieldName()) . "(\$" . $c->getClassFieldName() . ")", 1);
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
				$this->addLine("\$this->" . $c->getClassFieldName() . " = mb_substr(\$" . $c->getClassFieldName() . ", 0, " . $c->getSize() . ");", 3);
				$this->addLine("}", 2);
				break;
			case ColumnType::NUMBER:
				$this->addLine("if(is_numeric(\$" . $c->getClassFieldName() . "))", 2);
				$this->addLine("{", 2);
				$this->addLine("\$this->" . $c->getClassFieldName() . " = round(\$" . $c->getClassFieldName() . ", " . intval($c->getScale()) . ");", 3);
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
				$this->addLine("\$this->" . $c->getClassFieldName() . " = date(PHP_DATE_FORMAT, strtotime(\$" . $c->getClassFieldName() . "));", 3);
				$this->addLine("}", 2);
				break;
			case ColumnType::TIME:
				$this->addLine("if(empty(\$" . $c->getClassFieldName() . "))", 2);
				$this->addLine("{", 2);
				$this->addLine("\$this->" . $c->getClassFieldName() . " = null;", 3);
				$this->addLine("}", 2);
				$this->addLine("else", 2);
				$this->addLine("{", 2);
				$this->addLine("\$this->" . $c->getClassFieldName() . " = date(PHP_TIME_FORMAT, strtotime(\$" . $c->getClassFieldName() . "));", 3);
				$this->addLine("}", 2);
				break;
			case ColumnType::DATETIME:
				$this->addLine("if(empty(\$" . $c->getClassFieldName() . "))", 2);
				$this->addLine("{", 2);
				$this->addLine("\$this->" . $c->getClassFieldName() . " = null;", 3);
				$this->addLine("}", 2);
				$this->addLine("else", 2);
				$this->addLine("{", 2);
				$this->addLine("\$this->" . $c->getClassFieldName() . " = date(PHP_DATETIME_FORMAT, strtotime(\$" . $c->getClassFieldName() . "));", 3);
				$this->addLine("}", 2);
				break;
			case ColumnType::FLOAT:
				$this->addLine("if(is_numeric(\$" . $c->getClassFieldName() . "))", 2);
				$this->addLine("{", 2);
				$this->addLine("\$this->" . $c->getClassFieldName() . " = \$" . $c->getClassFieldName() . ";", 3);
				$this->addLine("}", 2);
				$this->addLine("else", 2);
				$this->addLine("{", 2);
				$this->addLine("\$this->" . $c->getClassFieldName() . " = null;", 3);
				$this->addLine("}", 2);
				break;
			case ColumnType::TEXT:
			case ColumnType::ENUM:
			default :
				$this->addLine("\$this->" . $c->getClassFieldName() . " = \$" . $c->getClassFieldName() . ";", 2);
				break;
		}
		$this->addLine("}", 1);
		$this->addLine("// -----------------------------------------------------------------------------------------------------------------", 1);
	}
	// ------------------------------------------------------------------------------------------------------------------
	protected function generateSetters(Table $t)
	{
		foreach($t->getColumny() as $column)
		{
			$this->generateSetter($column);
		}
	}
	// ------------------------------------------------------------------------------------------------------------------
	protected function generateSetAllFromDB(Table $t)
	{
		$this->addLine("/**", 1);
		$this->addLine(" * Methods set all atributes in object of class " . $t->getClassName() . " from object class DB", 1);
		$this->addLine(" * @return void", 1);
		$this->addLine(" */", 1);
		$this->addLine("protected function setAllFromDB(DataSource \$db)", 1);
		$this->addLine("{", 1);
		foreach($t->getColumny() as $c)
			/* @var $c Column */
		{
			$this->addLine("\$this->set" . ucfirst($c->getClassFieldName()) . "(\$db->f(\"" . $c->getKey() . "\"));", 2);
		}
		$this->addLine("\$this->setReaded();", 2);

		$this->addLine("}", 1);
		$this->addLine("// -----------------------------------------------------------------------------------------------------------------", 1);
	}
	// ------------------------------------------------------------------------------------------------------------------
	protected function generateConstruktor(Table $t)
	{
		$this->addLine("/**", 1);
		$tmp1 = array();
		$tmp2 = array();
		$tmp3 = array();
		foreach($t->getPk() as $c)
			/* @var $c Column */
		{
			$this->addLine(" * @param " . $c->getPHPType() . " \$" . $c->getClassFieldName() . "", 1);
			$tmp1[] = "\$" . $c->getClassFieldName() . " = null";
			$tmp2[] = "!is_null(\$" . $c->getClassFieldName() . ")";
			$tmp3[] = "\$" . $c->getClassFieldName() . "";
		}
		$this->addLine(" */", 1);
		$this->addLine("protected function __construct(" . implode(", ", $tmp1) . ")", 1);
		$this->addLine("{", 1);
		$this->addLine("if(" . implode(" && ", $tmp2) . ")", 2);
		$this->addLine("{", 2);

		$this->addLine("if(!\$this->retrieve(" . implode(", ", $tmp3) . "))", 3);
		$this->addLine("{", 3);
		$this->addLine("throw new \\braga\\tools\\exception\\BragaException(\"" . $t->getErrorPrefix() . "01 " . $t->getName() . "(\" . " . implode(" . \", \".", $tmp3) . " . \")  does not exists\");", 4);
		$this->addLine("}", 3);
		$this->addLine("}", 2);
		$this->addLine("}", 1);
		$this->addLine("// -----------------------------------------------------------------------------------------------------------------", 1);
	}
	// -----------------------------------------------------------------------------------------------------------------
	protected function generateProperties(Table $table)
	{
		$this->addLine("// -----------------------------------------------------------------------------------------------------------------", 1);
		$this->addLine("/**", 1);
		$this->addLine(" * @var static[]", 1);
		$this->addLine(" */", 1);
		$this->addLine("protected static \$instance = array();", 1);
		$this->addLine("// -----------------------------------------------------------------------------------------------------------------", 1);
		foreach($table->getColumny() as $c)
			/* @var $c Column */
		{
			$this->addLine("protected \$" . $c->getClassFieldName() . " = null;", 1);
		}
		$this->addLine("protected bool \$readed = false;", 1);
		$this->addLine("protected bool \$forUpdate = false;", 1);
		$this->addLine("// -----------------------------------------------------------------------------------------------------------------", 1);
	}
	// -----------------------------------------------------------------------------------------------------------------
	protected function generateClassHead($t)
	{
		$this->addLine("class " . $t->getClassName() . "DAO implements DAO", 0);
		$this->addLine("{", 0);
	}
	// -----------------------------------------------------------------------------------------------------------------
	protected function generateClassFooter()
	{
		$this->addLine("}", 0);
	}
	// -----------------------------------------------------------------------------------------------------------------
	protected function generateClassDocumentation(Table $t)
	{
		$this->addLine("/**", 0);
		// $this->addLine(" * Created on " . date("d-m-Y H:i:s"), 0);
		$this->addLine(" * @author " . $this->project->getAuthor(), 0);
		$this->addLine(" * @package " . $this->project->getName(), 0);
		$this->addLine(" * error prefix " . $t->getErrorPrefix(), 0);
		$this->addLine(" * Genreated by BragaDaoGenereator ver " . Project::VERSION, 0);
		$this->addLine(" * class generated automatically, please do not modify under pain of OVERWRITTEN WITHOUT WARNING", 0);
		$this->addLine(" */", 0);
	}
	// -----------------------------------------------------------------------------------------------------------------
	protected function generateNameSpace(Table $t)
	{
		if(strlen($this->project->getNameSpace()) > 0)
		{
			$this->addLine("namespace " . $this->project->getNameSpace() . "dao;", 0);
			$this->addLine("", 0);
			$this->addLine("use braga\\db\\DAO;", 0);
			$this->addLine("use braga\\db\\DataSource;", 0);
			if($this->project->getDataBaseStyle() == DataBaseStyle::PGSQL)
			{
				$this->addLine("use braga\db\pgsql\DB;", 0);
			}
			elseif($this->project->getDataBaseStyle() == DataBaseStyle::ORACLE)
			{
				$this->addLine("use braga\db\oracle\DB;", 0);
			}
			else
			{
				$this->addLine("use braga\db\mysql\DB;", 0);
			}
		}
	}
	// -----------------------------------------------------------------------------------------------------------------
	protected function open(Table $t)
	{
		@mkdir($this->project->getProjectFolder() . DIRECTORY_SEPARATOR . $this->project->getDaoFolder(), 0777, true);
		$this->fileHandle = fopen($this->project->getProjectFolder() . DIRECTORY_SEPARATOR . $this->project->getDaoFolder() . DIRECTORY_SEPARATOR . $t->getClassName() . "DAO.php", "w");
		$this->addLine("<?php", 0);
	}
	// -----------------------------------------------------------------------------------------------------------------
	protected function close()
	{
		$this->addLine("", 0, false);
		fclose($this->fileHandle);
	}
	// -----------------------------------------------------------------------------------------------------------------
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
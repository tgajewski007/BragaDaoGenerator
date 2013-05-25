<?php
/**
 * Created on 23-03-2013 14:29:33
 * author Tomasz Gajewski
 * package frontoffice
 * error prefix
 *
 */
class ClassFileGenerator extends DAOFileGenerator
{
	// -------------------------------------------------------------------------
	protected $file = null;
	// -------------------------------------------------------------------------
	public function GO()
	{
		foreach($this->project->getTables() as $t)/* @var $t Table */
		{
			@mkdir($this->project->getProjectFolder() . "\\" . $this->project->getObjFolder(),0777,true);
			$this->file = $this->project->getProjectFolder() . "\\" . $this->project->getObjFolder() . "\\" . $t->getClassName() . ".php";
			if(!file_exists($this->file))
			{
				$this->open($t);
				$this->prepareClass($t);
				$this->close();
			}
		}
	}
	// -------------------------------------------------------------------------
	protected function prepareClass(Table $t)
	{
		$this->generateClassDocumentation($t);
		$this->generateClassHead($t);
		$this->generateProperties($t);
		$this->generateStaticGetMethod($t);
		$this->generateUpdateFactoryIndex($t);
		$this->generateStaticGetByDataSourceMethod($t);
		$this->generateGetKey($t);
		$this->generateSave($t);
		$this->generateGetAllExample($t);
		$this->generateClassFooter();
	}
	// -------------------------------------------------------------------------
	protected function generateUpdateFactoryIndex(Table $t)
	{
		$tmp1 = array();
		foreach($t->getPk() as $c) /* @var $c Column */
		{
			$tmp1[] = "\$" . lcfirst($t->getClassName()) . "->get" . ucfirst($c->getClassFieldName()) . "()";
		}

		$this->addLine("protected static function updateFactoryIndex(self \$" . lcfirst($t->getClassName()) . ")", 1);
		$this->addLine("{", 1);
		$this->addLine("if((\$key = array_search(\$" . lcfirst($t->getClassName()) . ",self::\$instance,true)) !== false )", 2);
		$this->addLine("{", 2);
		$this->addLine("if(\$key != " . implode(" . \"_\" . ", $tmp1) . ")", 3);
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
	protected function generateGetAllExample($t)
	{
		$this->addLine("/**", 1);
		$this->addLine(" * Metoda zwraca kolekcję obiektów ", 1);
		$this->addLine(" * @return Collection&lt;" . $t->getClassName() . "&gt; ", 1);
		$this->addLine(" */", 1);
		$this->addLine("public static function getAll()", 1);
		$this->addLine("{", 1);
		$this->addLine("// TODO: jest to przykładowa metoda pozwalajaca pobrać kolekcję obiektów", 2);
		$this->addLine("\$db = new DB();", 2);
		$this->addLine("\$SQL  = \"SELECT * \";", 2);
		$this->addLine("\$SQL .= \"FROM \" . ".$t->getSchema()." . \".".$t->getName()." \";", 2);
		$this->addLine("\$db->query(\$SQL);", 2);
		$this->addLine("\$retval = new Collection(\$db, self::get());", 2);
		$this->addLine("return \$retval;", 2);
		$this->addLine("}", 1);
		$this->addLine("// -------------------------------------------------------------------------", 1);
	}
	// -------------------------------------------------------------------------
	protected function generateProperties($t)
	{
		$this->addLine("// -------------------------------------------------------------------------", 1);
		$this->addLine("protected static \$instance = array();", 1);
		$this->addLine("// -------------------------------------------------------------------------", 1);
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
		$this->addLine("static function getByDataSource(DataSource \$db)", 1);
		$this->addLine("{", 1);
		$this->addLine("\$key = " . implode(" . \"_\" . ", $tmp1) . ";", 2);
		$this->addLine("if(!isset(self::\$instance[\$key]))", 2);
		$this->addLine("{", 2);
		$this->addLine("self::\$instance[\$key] = new self();", 3);
		$this->addLine("self::\$instance[\$key]->setAllFromDB(\$db);", 3);
		$this->addLine("}", 2);
		$this->addLine("return self::\$instance[\$key];", 2);
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
			$tmp2[] = "!is_null(\$" . $c->getClassFieldName() . ")";
			$tmp3[] = "\$" . $c->getClassFieldName();
		}

		$this->addLine(" * @return " . $t->getClassName() . "", 1);
		$this->addLine(" */", 1);
		$this->addLine("static function get(" . implode(", ", $tmp1) . ")", 1);
		$this->addLine("{", 1);
		$this->addLine("if(" . implode(" and ", $tmp2) . ")", 2);
		$this->addLine("{", 2);
		$this->addLine("if(!isset(self::\$instance[" . implode(" . \"_\" . ", $tmp3) . "]))", 3);
		$this->addLine("{", 3);
		$this->addLine("self::\$instance[" . implode(" . \"_\" . ", $tmp3) . "] = new self(" . implode(", ", $tmp3) . ");", 4);
		$this->addLine("}", 3);
		$this->addLine("return self::\$instance[" . implode(" . \"_\" . ", $tmp3) . "];", 3);
		$this->addLine("}", 2);
		$this->addLine("else", 2);
		$this->addLine("{", 2);
		$this->addLine("return self::\$instance[\"\\\$\".count(self::\$instance)] = new self();", 3);
		$this->addLine("}", 2);
		$this->addLine("}", 1);
		$this->addLine("// -------------------------------------------------------------------------", 1);
	}
	// -------------------------------------------------------------------------
	protected function generateSave($t)
	{
		$this->addLine("/**", 1);
		$this->addLine(" * Metoda zapisuje obiekt klasy " . $t->getClassName(), 1);
		$this->addLine(" * @return boolean Zwraca true w przypadku powodzenia i false w przypadku przeciwnym", 1);
		$this->addLine(" */", 1);

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
		$this->addLine("public function save()", 1);
		$this->addLine("{", 1);
		$this->addLine("// TODO: ustawienie wartości które nie mają być zależne od klienta klasy", 2);
		$this->addLine("if(\$this->isReaded())", 2);
		$this->addLine("{", 2);
		$this->addLine("return \$this->update();", 3);
		$this->addLine("}", 2);
		$this->addLine("else", 2);
		$this->addLine("{", 2);
		$this->addLine("return \$this->create();", 3);
		$this->addLine("}", 2);
		$this->addLine("}", 1);
		$this->addLine("// -------------------------------------------------------------------------", 1);
	}
	// -------------------------------------------------------------------------
	protected function generateClassHead($t)
	{
		$this->addLine("class " . $t->getClassName() . " extends " . $t->getClassName() . "DAO implements DAO", 0);
		$this->addLine("{", 0);
	}
	// -------------------------------------------------------------------------
	protected function generateClassDocumentation(Table $t)
	{
		$this->addLine("/**", 0);
		$this->addLine(" * Created on " . date("d-m-Y H:i:s"), 0);
		$this->addLine(" * @author " . $this->project->getAuthor(), 0);
		$this->addLine(" * @package " . $this->project->getName(), 0);
		$this->addLine(" * error prefix " . $t->getErrorPrefix(), 0);
		$this->addLine(" * Wygenerowano przy pomocy PHPDAOClassGenerator ver " . Project::VERSION, 0);
		$this->addLine(" * {uzupełnij dokumentację}", 0);
		$this->addLine(" */", 0);
	}
	// -------------------------------------------------------------------------
	protected function open(Table $t)
	{
		$this->fileHandle = fopen($this->file, "w");
		$this->addLine("<?php", 0);
	}
	// -------------------------------------------------------------------------
}
?>
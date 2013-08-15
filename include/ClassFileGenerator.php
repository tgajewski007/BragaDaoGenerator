<?php
/**
 * Created on 23-03-2013 14:29:33
 * author Tomasz Gajewski
 * package frontoffice
 * error prefix
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
			@mkdir($this->project->getProjectFolder() . "\\" . $this->project->getObjFolder(), 0777, true);
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
		$this->generateCheck($t);
		$this->generateSave($t);
		$this->generateKill($t);
		$this->generateGetAllExample($t);
		$this->generateClassFooter();
	}
	// -------------------------------------------------------------------------
	protected function generateCheck($t)
	{
		$this->addLine("/**", 1);
		$this->addLine(" * Metoda sprawdza poprawność danych przed zapisem do bazy", 1);
		$this->addLine(" * @return boolean", 1);
		$this->addLine(" */", 1);
		$this->addLine("protected function check()", 1);
		$this->addLine("{", 1);
		$this->addLine("// TODO: dodać sprawdzenia przed zapisem do bazy", 2);
		$this->addLine("return true;", 2);
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
		$this->addLine("\$sql  = \"SELECT * \";", 2);
		$this->addLine("\$sql .= \"FROM \" . " . $t->getSchema() . " . \"." . $t->getName() . " \";", 2);
		$this->addLine("\$db->query(\$sql);", 2);
		$this->addLine("\$retval = new Collection(\$db, self::get());", 2);
		$this->addLine("return \$retval;", 2);
		$this->addLine("}", 1);
		$this->addLine("// -------------------------------------------------------------------------", 1);
	}
	// -------------------------------------------------------------------------
	protected function generateKill($t)
	{
		$this->addLine("/**", 1);
		$this->addLine(" * Metoda usuwa obiekt klasy " . $t->getClassName(), 1);
		$this->addLine(" * @return boolean Zwraca true w przypadku powodzenia i false w przypadku przeciwnym", 1);
		$this->addLine(" */", 1);
		$this->addLine("public function kill()", 1);
		$this->addLine("{", 1);
		$this->addLine("// TODO: metode należy zmienić jeżeli rekordy mają być tylko oznaczane jako usunięte", 2);
		$this->addLine("return \$this->destroy();", 2);
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
		$this->addLine("if(\$this->check())", 2);
		$this->addLine("{", 2);
		$this->addLine("if(\$this->isReaded())", 3);
		$this->addLine("{", 3);
		$this->addLine("return \$this->update();", 4);
		$this->addLine("}", 3);
		$this->addLine("else", 3);
		$this->addLine("{", 3);
		$this->addLine("return \$this->create();", 4);
		$this->addLine("}", 3);
		$this->addLine("}", 2);
		$this->addLine("else", 2);
		$this->addLine("{", 2);
		$this->addLine("return false;", 3);
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
		$this->addLine(" * Wygenerowano przy pomocy SimplePHPDAOClassGenerator ver " . Project::VERSION, 0);
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
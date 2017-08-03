<?php
/**
 * Created on 02-08-2017 14:49:33
 * @author Mariusz Górski
 * @package frontoffice
 * error prefix
 */
class DbFileGenerator extends DAOFileGenerator
{
	// -------------------------------------------------------------------------
	public function GO()
	{
		foreach($this->project->getTables() as $t)/* @var $t Table */
		{
			@mkdir($this->project->getProjectFolder() . "\\" . $this->project->getDbFolder(), 0777, true);
			$this->file = $this->project->getProjectFolder() . "\\" . $this->project->getDbFolder() . "\\" . $t->getClassName() . "DB.php";
			if(FORCE_GEN_DB || !file_exists($this->file))
			{
				$this->open($t);
				$this->generateNameSpace($t);
				$this->prepareClass($t);
				$this->close();
			}
		}
	}
	// -------------------------------------------------------------------------
	protected function generateNameSpace(Table $t)
	{
		if(strlen($this->project->getNameSpace()) > 0)
		{
			$this->addLine("namespace " . $this->project->getNameSpace() . "\\" . $this->project->getDbFolder() . ";\n", 0);
			$this->addLine("use " . $this->project->getNameSpace() . "\\" . $this->project->getObjFolder() . "\\" . $t->getClassName() . ";", 0);
			$this->addLine("use " . $this->project->getNameSpace() . "\\" . $this->project->getDaoFolder() . "\\" . $t->getClassName() . "DAO;", 0);
			$this->addLine("use " . $this->project->getNameSpace() . "\\" . $this->project->getDbFolder() . "\\framework_dao\\DB;", 0);
			$this->addLine("use " . $this->project->getNameSpace() . "\\" . $this->project->getDbFolder() . "\\framework_dao\\Collection;", 0);
			// $this->addLine("use " . $this->project->getNameSpace() . "\\" . $this->project->getDbFolder() . "\\framework_dao\\iface\\DAO;", 0);
			// $this->addLine("use " . $this->project->getNameSpace() . "\\" . $this->project->getDbFolder() . "\\framework_dao\\iface\\DataSource;", 0);
		}
	}
	// -------------------------------------------------------------------------
	protected function prepareClass(Table $t)
	{
		$this->generateClassDocumentation($t);
		$this->generateClassHead($t);
		$this->generateGetAll($t);
		$this->generateClassFooter();
	}
	// -------------------------------------------------------------------------
	protected function generateClassHead(Table $t)
	{
		$this->addLine("abstract class " . $t->getClassName() . "DB extends " . $t->getClassName() . "DAO", 0);
		$this->addLine("{", 0);
		$this->addLine("// -------------------------------------------------------------------------", 1);
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
		$this->addLine(" * Klasa ma zawierać wszelkie metody zawierające zapytania SQL", 0);
		$this->addLine(" * {tutaj należy uzupełnić dokumentację klasy}", 0);
		$this->addLine(" * @author " . $this->project->getAuthor(), 0);
		$this->addLine(" * @package " . $this->project->getName(), 0);
		$this->addLine(" */", 0);
	}
	// -------------------------------------------------------------------------
	protected function open(Table $t)
	{
		$this->fileHandle = fopen($this->file, "w");
		$this->addLine("<?php", 0);
	}
	// -------------------------------------------------------------------------
	protected function generateGetAll(Table $t)
	{
		$this->addLine("/**", 1);
		$this->addLine(" * Metoda zwraca kolekcję wszystkich obiektów klasy " . $t->getClassName(), 1);
		$this->addLine(" * zapisanych jako rekordy w tabeli " . $t->getName(), 1);
		$this->addLine(" * @return Collection &lt;" . $t->getClassName() . "&gt; ", 1);
		$this->addLine(" */", 1);
		$this->addLine("public static function getAll(): Collection", 1);
		$this->addLine("{", 1);
		$this->addLine("\$db = new DB();", 2);
		$this->addLine("\$sql  = \"SELECT * \";", 2);
		$this->addLine("\$sql .= \"FROM \" . " . $t->getSchema() . " . \"." . $t->getName() . " \";", 2);
		$this->addLine("\$db->query(\$sql);", 2);
		$this->addLine("return new Collection(\$db, " . $t->getClassName() . "::get());", 2);
		$this->addLine("}", 1);
		$this->addLine("// -------------------------------------------------------------------------", 1);
	}
	// -------------------------------------------------------------------------
}
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
			if(FORCE_GEN_DB)
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
			$this->addLine("use " . $this->project->getNameSpace() . "\\" . $this->project->getDaoFolder() . "\\" . $t->getClassName() . "DAO;", 0);
			// $this->addLine("use " . $this->project->getNameSpace() . "\\" . $this->project->getDbFolder() . "\\framework_dao\\DB;", 0);
			// $this->addLine("use " . $this->project->getNameSpace() . "\\" . $this->project->getDbFolder() . "\\framework_dao\\Collection;", 0);
			$this->addLine("use " . $this->project->getNameSpace() . "\\" . $this->project->getDbFolder() . "\\framework_dao\\iface\\DAO;", 0);
			// $this->addLine("use " . $this->project->getNameSpace() . "\\" . $this->project->getDbFolder() . "\\framework_dao\\iface\\DataSource;", 0);
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
		$this->generateClassFooter();
	}
	// -------------------------------------------------------------------------
	protected function generateCheck(Table $t)
	{
		$this->addLine("/**", 1);
		$this->addLine(" * Metoda sprawdza poprawność atrybutów obiektu klasy " . $t->getClassName(), 1);
		$this->addLine(" * przed zapisem rekordu w tabeli " . $t->getName(), 1);
		$this->addLine(" * @return boolean", 1);
		$this->addLine(" */", 1);
		$this->addLine("protected function check()", 1);
		$this->addLine("{", 1);
		$this->addLine("// TODO: dodaj sprawdzenie poprawności atrybutów obiektu ", 2);
		$this->addLine("return true;", 2);
		$this->addLine("}", 1);
		$this->addLine("// -------------------------------------------------------------------------", 1);
	}
	// -------------------------------------------------------------------------
	protected function generateKill(Table $t)
	{
		$this->addLine("/**", 1);
		$this->addLine(" * Metoda usuwa obiekt klasy " . $t->getClassName(), 1);
		$this->addLine(" * umożliwiając wykonanie przed tym niezbędnych czynności", 1);
		$this->addLine(" * @return boolean", 1);
		$this->addLine(" */", 1);
		$this->addLine("public function kill()", 1);
		$this->addLine("{", 1);
		$this->addLine("// TODO: tą metodę można zmienić jeśli obiekt nie ma zostać usunięty jako rekord z tabeli", 2);
		$this->addLine("return \$this->destroy();", 2);
		$this->addLine("}", 1);
		$this->addLine("// -------------------------------------------------------------------------", 1);
	}
	// -------------------------------------------------------------------------
	protected function generateSave(Table $t)
	{
		$this->addLine("/**", 1);
		$this->addLine(" * Metoda zapisuje obiekt klasy " . $t->getClassName(), 1);
		$this->addLine(" * jako rekord w tabeli " . $t->getName(), 1);
		$this->addLine(" * (tworzy nowy lub modyfikuje istniejący", 1);
		$this->addLine(" * @return boolean", 1);
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
			elseif($c instanceof ColumnForeignKey)
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
		$this->addLine("// TODO: tu proszę ustawić atrybuty niezależne od ustawień aplikacji", 2);
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
	protected function generateClassHead(Table $t)
	{
		$this->addLine("abstract class " . $t->getClassName() . "DB extends " . $t->getClassName() . "DAO implements DAO", 0);
		$this->addLine("{", 0);
		$this->addLine("// -------------------------------------------------------------------------", 1);
	}
	// -------------------------------------------------------------------------
	protected function generateClassDocumentation(Table $t)
	{
		$this->addLine("/**", 0);
		$this->addLine(" * Created on " . date("d-m-Y H:i:s"), 0);
		$this->addLine(" * tabela " . $t->getName(), 0);
		$this->addLine(" * error prefix " . $t->getErrorPrefix(), 0);
		$this->addLine(" * max error " . $t->getErrorPrefix() . "04", 0);
		$this->addLine(" * Generated by SimplePHPDAOClassGenerator ver " . Project::VERSION, 0);
		$this->addLine(" * https://sourceforge.net/projects/simplephpdaogen/ ", 0);
		$this->addLine(" * {please complete documentation}", 0);
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
}
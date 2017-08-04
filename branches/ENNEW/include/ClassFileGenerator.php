<?php
/**
 * Created on 23-03-2013 14:29:33
 * author Tomasz Gajewski
 * package frontoffice
 * error prefix
 */
class ClassFileGenerator extends DbFileGenerator
{
	// -------------------------------------------------------------------------
	public function GO()
	{
		foreach($this->project->getTables() as $t)/* @var $t Table */
		{
			@mkdir($this->project->getProjectFolder() . "\\" . $this->project->getObjFolder(), 0777, true);
			$this->file = $this->project->getProjectFolder() . "\\" . $this->project->getObjFolder() . "\\" . $t->getClassName() . ".php";
			if(FORCE_GEN_CLASS || !file_exists($this->file))
			{
				$this->open($t);
				$this->generateNameSpace($t);
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
		if($t->getTableType() == 'table')
		{
			$this->generateCheck($t);
			$this->generateSave($t);
			$this->generateKill($t);
		}
		$this->generateClassFooter();
	}
	// -------------------------------------------------------------------------
	protected function open(Table $t)
	{
		$this->fileHandle = fopen($this->file, "w");
		$this->addLine("<?php", 0);
	}
	// -------------------------------------------------------------------------
	protected function generateNameSpace(Table $t)
	{
		if(strlen($this->project->getNameSpace()) > 0)
		{
			$this->addLine("namespace " . $this->project->getNameSpace() . "\\" . $this->project->getObjFolder() . ";\n", 0);
			$this->addLine("use " . $this->project->getNameSpace() . "\\" . $this->project->getDbFolder() . "\\" . $t->getClassName() . "DB;", 0);
			// $this->addLine("use " . $this->project->getNameSpace() . "\\" . $this->project->getDbFolder() . "\\framework_dao\\DB;", 0);
			// $this->addLine("use " . $this->project->getNameSpace() . "\\" . $this->project->getDbFolder() . "\\framework_dao\\Collection;", 0);
			// $this->addLine("use " . $this->project->getNameSpace() . "\\" . $this->project->getDbFolder() . "\\framework_dao\\iface\\DAO;", 0);
			// $this->addLine("use " . $this->project->getNameSpace() . "\\" . $this->project->getDbFolder() . "\\framework_dao\\iface\\DataSource;", 0);
		}
	}
	// -------------------------------------------------------------------------
	protected function generateClassHead(Table $t)
	{
		$this->addLine("final class " . $t->getClassName() . " extends " . $t->getClassName() . "DB", 0);
		$this->addLine("{", 0);
		$this->addSpacer();
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
		$this->addLine(" * Klasa ma zawierać wszelkie metody nie zawierające zapytań SQL", 0);
		$this->addLine(" * {tutaj należy uzupełnić dokumentację klasy}", 0);
		$this->addLine(" * @author " . $this->project->getAuthor(), 0);
		$this->addLine(" * @package " . $this->project->getName(), 0);
		$this->addLine(" */", 0);
	}
	// -------------------------------------------------------------------------
	protected function generateCheck(Table $t)
	{
		$this->addLine("/**", 1);
		$this->addLine(" * Metoda sprawdza poprawność atrybutów obiektu klasy " . $t->getClassName(), 1);
		$this->addLine(" * na przykład przed zapisem rekordu w tabeli " . $t->getName(), 1);
		$this->addLine(" * @return bool", 1);
		$this->addLine(" */", 1);
		$this->addLine("protected function check(): bool", 1);
		$this->addLine("{", 1);
		$this->addLine("\$retval = true;", 2);
		$this->addLine("// TODO: dodaj sprawdzenie poprawności atrybutów obiektu ", 2);
		$this->addLine("return \$retval;", 2);
		$this->addLine("}", 1);
		$this->addSpacer();
	}
	// -------------------------------------------------------------------------
	protected function generateKill(Table $t)
	{
		$this->addLine("/**", 1);
		$this->addLine(" * Metoda usuwa obiekt klasy " . $t->getClassName(), 1);
		$this->addLine(" * umożliwiając wykonanie przed tym niezbędnych czynności", 1);
		$this->addLine(" * @return bool", 1);
		$this->addLine(" */", 1);
		$this->addLine("public function kill(): bool", 1);
		$this->addLine("{", 1);
		$this->addLine("// TODO: tą metodę można zmienić jeśli obiekt nie ma zostać usunięty jako rekord z tabeli " . $t->getName(), 2);
		$this->addLine("return \$this->destroy();", 2);
		$this->addLine("}", 1);
		$this->addSpacer();
	}
	// -------------------------------------------------------------------------
	protected function generateSave(Table $t)
	{
		$this->addLine("/**", 1);
		$this->addLine(" * Metoda zapisuje obiekt klasy " . $t->getClassName(), 1);
		$this->addLine(" * jako rekord w tabeli " . $t->getName(), 1);
		$this->addLine(" * (tworzy nowy lub modyfikuje istniejący", 1);
		$this->addLine(" * @return bool", 1);
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
		$this->addLine("public function save(): bool", 1);
		$this->addLine("{", 1);
		$this->addLine("\$retval = false;", 2);
		$this->addLine("// TODO: tu proszę ustawić atrybuty niezależne od ustawień aplikacji", 2);
		$this->addLine("if(\$this->check())", 2);
		$this->addLine("{", 2);
		$this->addLine("if(\$this->isReaded())", 3);
		$this->addLine("{", 3);
		$this->addLine("\$retval = \$this->update();", 4);
		$this->addLine("}", 3);
		$this->addLine("else", 3);
		$this->addLine("{", 3);
		$this->addLine("\$retval = \$this->create();", 4);
		$this->addLine("}", 3);
		$this->addLine("}", 2);
		$this->addLine("return \$retval;", 2);
		$this->addLine("}", 1);
		$this->addSpacer();
	}
}
?>
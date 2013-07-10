<?php
/**
 * Created on 25-05-2013 15:55:23
 * author Tomasz Gajewski
 * package frontoffice
 * error prefix
 *
 */
class MainFileGenerator
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
		$this->open();
		$this->generateDoc();
		$this->generateDaoFiles();
		$this->generateClassFiles();
		$this->close();
	}
	// -------------------------------------------------------------------------
	protected function generateClassFiles()
	{
		foreach($this->project->getTables() as $t)/* @var $t Table */
		{
			$txt = "include '" . $this->project->getObjFolder() . "/" . $t->getClassName() . ".php';";
			$this->addLine($txt, 0);
		}
	}
	// -------------------------------------------------------------------------
	protected function generateDaoFiles()
	{
		foreach($this->project->getTables() as $t)/* @var $t Table */
		{
			$txt = "include '" . $this->project->getDaoFolder() . "/" . $t->getClassName() . "DAO.php';";
			$this->addLine($txt, 0);
		}
	}
	// -------------------------------------------------------------------------
	protected function generateDoc()
	{
		$this->addLine("/**", 0);
		$this->addLine(" * Created on " . date("d-m-Y H:i:s"), 0);
		$this->addLine(" * Lista plików do includowania", 0);
		$this->addLine(" * NADPISANIA BEZ OSTRZEŻENIA ", 0);
		$this->addLine(" */", 0);
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
	protected function open()
	{
		@mkdir($this->project->getProjectFolder(), 0777, true);
		$this->fileHandle = fopen($this->project->getProjectFolder() . "\\" . "main.php", "w");
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
?>
<?php
/**
 * Created on 07-04-2013 19:31:40
 * author Tomasz Gajewski
 * package frontoffice
 * error prefix
 *
 */
class ReverseCreator
{
	// -------------------------------------------------------------------------
	public $indexTableSpace;
	public $schemaName;
	// -------------------------------------------------------------------------
	/**
	 * @var ReverseProxy
	 */
	protected $proxy = null;
	/**
	 * @var Project
	 */
	protected $project = null;
	protected $errorCount = 100;
	// -------------------------------------------------------------------------
	function __construct(Project $p, ReverseProxy $r)
	{
		$this->proxy = $r;
		$this->project = FilesGenerator::loadFromXML($p);
	}
	// -------------------------------------------------------------------------
	public function GO()
	{
		echo "starting...\n";
		foreach ($this->proxy->getTables() as $table)/* @var $table ReverseTable */
		{
			$this->addTable($table);
		}
		echo "generating...\n";
		$this->save();
		echo "end\n";
	}
	// -------------------------------------------------------------------------
	protected function addTable(ReverseTable $t)
	{
		$this->errorCount ++;
		$table = new Table();
		$table->setName($t->tableName);
		$table->setSchema($this->schemaName);
		$className = "";
		foreach ((explode("_", strtolower($t->tableName))) as $v)
		{
			$className .= ucfirst($v);
		}
		$table->setClassName($className);
		$table->setTableSpace($t->tableSpace);
		$table->setIndexTableSpace($this->indexTableSpace);
		$table->setErrorPrefix($this->project->getErrorPrefix().$this->errorCount);



		$pk = $this->proxy->getPrimaryKeys($t->tableName);
		$fk = $this->proxy->getForeginKeys($t->tableName);


		foreach ($this->proxy->getColumn($t->tableName) as $col)/* @var $col ReverseColumn */
		{
			$c = new Column();
			$c->setName($col->name);
			$classFieldName = "";
			foreach ((explode("_", strtolower($col->name))) as $v)
			{
				$classFieldName .= ucfirst($v);
			}
			$classFieldName = lcfirst($classFieldName);
			if(strtolower(substr($classFieldName,0,2)) == "id")
			{
				$classFieldName = "id".ucfirst(substr($classFieldName,2));
			}
			$c->setClassFieldName($classFieldName);
			$c->setType($col->type);
			$c->setSize($col->size);
			$c->setScale($col->scale);
			foreach ($pk as $x)/* @var $x ReversePrimaryKey */
			{
				if($x->name == $col->name)
				{
					$c->setPK();
					$c->setAutoGenerate($t->haveAutoNumberPKField);
				}
			}
			$table->addColumn($c);
			foreach ($fk as $x)/* @var $x ReverseForeginKey */
			{
				$tmp = new ForeginKey();
				$tmp->setTableName($x->refTableName);
				$tmp->setTableSchema($this->schemaName);
				foreach ($x->columns as $fkCol) /* @var $fkCol ConnectedColumn */
				{
					$tmp->addColumn($fkCol->fkColumnName, $fkCol->pkColumnName);
				}
				$table->addFK($tmp);
			}
		}
		if(count($pk) > 0)
		{
			echo " ADDED\n";
			$this->project->addTable($table);
		}
		else
		{
			echo " refused (NO PK)\n";
		}
	}
	// -------------------------------------------------------------------------
	protected function save()
	{
		FilesGenerator::GO($this->project);
	}
	// -------------------------------------------------------------------------
}
?>
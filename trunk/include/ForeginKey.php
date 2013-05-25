<?php
/**
 * Created on 10-03-2013 19:25:39
 * author Tomasz Gajewski
 * package frontoffice
 * error prefix
 *
 */
class ForeginKey
{
	// -------------------------------------------------------------------------
	protected $columns = array();
	// -------------------------------------------------------------------------
	/**
	 * @var Table
	 */
	protected $table = null;
	// -------------------------------------------------------------------------
	public function setTableName($name)
	{
		$this->tableName = $name;
	}
	// -------------------------------------------------------------------------
	public function setTableSchema($schema)
	{
		$this->tableSchema = $schema;
	}
	// -------------------------------------------------------------------------
	public function getTableName()
	{
		return $this->tableName;
	}
	// -------------------------------------------------------------------------
	public function getTableSchema()
	{
		return $this->tableSchema;
	}
	// -------------------------------------------------------------------------
	public function getTable()
	{
		if(is_null($this->table))
		{
			foreach(FilesGenerator::$project->getTables() as $t)/* @var $t Table */
			{
				if($this->getTableName() == $t->getName() and $this->getTableSchema() == $t->getSchema())
				{
					$this->table = $t;
					return $this->table;
				}
			}
		}
		return $this->table;
	}
	// -------------------------------------------------------------------------
	public function addColumn($fkColumnName, $pkColumnName)
	{
		$tmp = new ConnectedColumn();
		$tmp->fkColumnName = $fkColumnName;
		$tmp->pkColumnName = $pkColumnName;
		$this->columns[] = $tmp;
	}
	// -------------------------------------------------------------------------
	public function getColumn()
	{
		return $this->columns;
	}
	// -------------------------------------------------------------------------
	static function import(DOMElement $c)
	{
		$fk = new self();
		$fk->setTableName($c->getAttribute("tableName"));
		$fk->setTableSchema($c->getAttribute("tableSchema"));
		foreach ($c->getElementsByTagName("connectedColumn") as $value)/* @var $value DOMElement */
		{
			$fk->addColumn($value->getAttribute("fkColumnName"), $value->getAttribute("pkColumnName"));
		}
		return $fk;
	}
	// -------------------------------------------------------------------------
	public function export(DOMElement $c)
	{
		$c->setAttribute("tableName", $this->getTableName());
		$c->setAttribute("tableSchema", $this->getTableSchema());

		foreach ($this->columns as $columna) /* @var $columna ConnectedColumn */
		{
			$el = new DOMElement("connectedColumn");
			$c->appendChild($el);
			$el->setAttribute("fkColumnName", $columna->fkColumnName);
			$el->setAttribute("pkColumnName", $columna->pkColumnName);
		}
	}
	// -------------------------------------------------------------------------
}
?>
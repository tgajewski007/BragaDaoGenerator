<?php
/**
 * Created on 23-03-2013 08:41:47
 * author Tomasz Gajewski
 * package frontoffice
 * error prefix
 *
 */
interface DataSource extends Countable
{
	// -------------------------------------------------------------------------
	public function f($index);
	// -------------------------------------------------------------------------
	/**
	 * @param sting $SQL
	 * @return boolean
	 */
	public function query($SQL);
	// -------------------------------------------------------------------------
	/**
	 * @return boolean
	 */
	public function nextRecord();
	// -------------------------------------------------------------------------
	public function setParam($name, $val);
	// -------------------------------------------------------------------------
}
?>
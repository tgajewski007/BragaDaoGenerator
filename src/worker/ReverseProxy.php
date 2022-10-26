<?php
namespace braga\daogenerator\worker;
/**
 * Created on 07-04-2013 13:34:03
 * author Tomasz Gajewski
 * package frontoffice
 * error prefix
 */
interface ReverseProxy
{
	// -------------------------------------------------------------------------
	/**
	 *
	 * @return array
	 */
	public function getTables();
	/**
	 *
	 * @return array
	 */
	public function getColumn($tableName);
	/**
	 *
	 * @return array
	 */
	public function getPrimaryKeys($tableName);
	/**
	 *
	 * @return array
	 */
	public function getForeginKeys($tableName);
	// -------------------------------------------------------------------------
}

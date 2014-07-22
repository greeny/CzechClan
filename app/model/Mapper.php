<?php
/**
 * @author Tomáš Blatný
 */

namespace Tempeus\Model;

use LeanMapper\DefaultMapper;
use LeanMapper\Exception\InvalidStateException;
use LeanMapper\Row;
use Nette\Utils\Strings;

class Mapper extends DefaultMapper {
	protected $defaultEntityNamespace = '\\Tempeus\\Model';

	public function getTable($entityClass)
	{
		return $this->entityToTable($this->trimNamespace($entityClass));
	}

	public function getEntityClass($table, Row $row = NULL)
	{
		return $this->defaultEntityNamespace . '\\' . $this->tableToEntity($table);
	}

	public function getTableByRepositoryClass($repositoryClass)
	{
		$matches = array();
		if (preg_match('#([a-z0-9]+)repository$#i', $repositoryClass, $matches)) {
			return $this->entityToTable($matches[1]);
		}
		throw new InvalidStateException('Cannot determine table name.');
	}

	protected function tableToEntity($name)
	{
		return Strings::replace($name, '#_.#', function($matches) {
			return ucfirst(substr($matches[0], 1));
		});
	}

	protected function entityToTable($name)
	{
		return strtolower(Strings::replace($name, '#.[A-Z]#', function($matches) {
			return $matches[0][0].'_'.$matches[0][1];
		}));
	}
}

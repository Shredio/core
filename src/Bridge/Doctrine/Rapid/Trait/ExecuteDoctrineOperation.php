<?php declare(strict_types = 1);

namespace Shredio\Core\Bridge\Doctrine\Rapid\Trait;

trait ExecuteDoctrineOperation
{

	protected function executeSql(string $sql): void
	{
		$this->em->getConnection()->executeStatement($sql);
	}

}

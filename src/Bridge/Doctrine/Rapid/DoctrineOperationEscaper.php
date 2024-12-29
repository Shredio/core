<?php declare(strict_types = 1);

namespace Shredio\Core\Bridge\Doctrine\Rapid;

use Doctrine\ORM\EntityManagerInterface;
use Shredio\Core\Database\Rapid\DefaultOperationEscaper;
use Shredio\Core\Database\Rapid\OperationEscaper;

final readonly class DoctrineOperationEscaper implements OperationEscaper
{

	private DefaultOperationEscaper $decorated;

	public function __construct(EntityManagerInterface $em)
	{
		$this->decorated = new DefaultOperationEscaper($em->getConnection()->quote(...));
	}

	public function escapeValue(mixed $value): string
	{
		return $this->decorated->escapeValue($value);
	}

	public function escapeColumn(string $column): string
	{
		return $this->decorated->escapeColumn($column);
	}

}

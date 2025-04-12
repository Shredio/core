<?php declare(strict_types = 1);

namespace Shredio\Core\Bridge\Symfony\Error;

use Symfony\Component\ErrorHandler\Exception\FlattenException;

interface CustomProblemNormalizer
{

	/**
	 * @param FlattenException $exception
	 * @param mixed[] $data
	 * @return mixed[]
	 */
	public function normalize(FlattenException $exception, array $data): array;

}

<?php declare(strict_types = 1);

namespace Shredio\Core\Async;

/**
 * @template T
 */
interface Promise
{

	/**
	 * @return T
	 */
	public function await(): mixed;

}

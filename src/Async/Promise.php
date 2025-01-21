<?php declare(strict_types = 1);

namespace Shredio\Core\Async;

/**
 * @template-covariant T
 */
interface Promise
{

	/**
	 * @return T
	 */
	public function await(): mixed;

}

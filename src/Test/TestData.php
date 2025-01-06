<?php declare(strict_types = 1);

namespace Shredio\Core\Test;

use Shredio\Core\Test\Assert\HttpExpectation;
use Shredio\Core\Test\Authentication\Actor;

final readonly class TestData
{

	public HttpExpectation $expectation;

	/** @var string[] */
	private array $groups;

	/**
	 * @param HttpExpectation|int $expectation Expectation or status code
	 * @param string|string[] $groups
	 */
	public function __construct(
		public Actor $actor,
		HttpExpectation|int $expectation,
		string|array $groups = [],
	)
	{
		$this->expectation = is_int($expectation) ? new HttpExpectation($expectation) : $expectation;
		$this->groups = (array) $groups;
	}

	public function getAuthor(): Actor
	{
		return $this->actor->getAuthorActor();
	}

	public function hasGroup(string $group): bool
	{
		return in_array($group, $this->groups, true);
	}

}

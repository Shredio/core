<?php declare(strict_types = 1);

namespace Shredio\Core\Bridge\Symfony\Test;

use PHPUnit\Framework\TestCase;
use Shredio\Core\Test\Assert\HttpExpectation;
use Shredio\Core\Test\Authentication\Actor;
use Shredio\Core\Test\Authentication\ForNone;
use Shredio\Core\Test\TestData;

final class TestHelperAccessor
{

	private static ?TestHelper $singleton = null;
	private static ?TestCase $case = null;

	public static function get(TestCase $case): TestHelper
	{
		if ($singleton = self::$singleton) {
			if (self::$case === $case) {
				return $singleton;
			}

			$singleton->reset();
		}

		self::$case = $case;

		return self::$singleton = new TestHelper(static function () use ($case): ?TestData {
			$actor = null;
			$expectation = null;

			foreach ($case->providedData() as $data) {
				if ($data instanceof TestData) {
					return $data;
				} else if ($data instanceof Actor) { // backwards compatibility
					$actor = $data;
				} elseif ($data instanceof HttpExpectation) { // backwards compatibility
					$expectation = $data;
				}
			}

			// backwards compatibility
			if (!$actor && !$expectation) {
				return null;
			}

			return new TestData($actor ?? new ForNone(), $expectation ?? 200, ['BC']);
		});
	}

	public static function has(): bool
	{
		return self::$singleton !== null;
	}

}

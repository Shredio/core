<?php declare(strict_types = 1);

namespace Tests\Doctrine;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DriverManager;
use Doctrine\DBAL\Platforms\MySQLPlatform;
use Doctrine\DBAL\Platforms\SQLitePlatform;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\ORMSetup;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\TestWith;
use PHPUnit\Framework\TestCase;
use Shredio\Core\Bridge\Doctrine\Rapid\DoctrineRapidInserter;
use Tests\Doctrine\entity\Article;
use Tests\Doctrine\entity\Post;

final class RapidInserterTest extends TestCase
{

	private function createEntityManager(string $platform = 'mysql'): EntityManager
	{
		$configuration = ORMSetup::createAttributeMetadataConfiguration([__DIR__ . '/entity'], true);
		$connection = $this->createStub(Connection::class);
		$connection->method('quote')->willReturnCallback(function (string $value): string {
			return sprintf("'%s'", $value);
		});

		if ($platform === 'sqlite') {
			$platform = $this->createStub(SQLitePlatform::class);
		} else {
			$platform = $this->createStub(MySQLPlatform::class);
		}

		$connection->method('getDatabasePlatform')->willReturnCallback(fn () => $platform);

		return new EntityManager($connection, $configuration);
	}

	#[TestWith(['mysql'])]
	#[TestWith(['sqlite'])]
	public function testInsert(string $platform): void
	{
		$inserter = new DoctrineRapidInserter(Article::class, $this->createEntityManager($platform));
		$inserter->addRaw([
			'id' => 1,
			'title' => 'foo',
			'content' => 'bar',
		]);
		$inserter->addRaw([
			'id' => 2,
			'title' => 'baz',
			'content' => 'qux',
		]);

		$this->assertSame("INSERT INTO `articles` (`id`, `title`, `content`) VALUES ('1', 'foo', 'bar'),
('2', 'baz', 'qux');", $inserter->getSql());
	}

	public function testInsertWithCustomNames(): void
	{
		$inserter = new DoctrineRapidInserter(Post::class, $this->createEntityManager(), [
			DoctrineRapidInserter::Mode => DoctrineRapidInserter::ModeUpsert,
		]);
		$inserter->addRaw([
			'id' => 1,
			'content' => 'bar',
		]);

		$this->assertSame("INSERT INTO `posts` (`id`, `contents`) VALUES ('1', 'bar') ON DUPLICATE KEY UPDATE `contents` = VALUES(`contents`);", $inserter->getSql());
	}

	#[TestWith(['mysql'])]
	#[TestWith(['sqlite'])]
	public function testUpsert(string $platform): void
	{
		$inserter = new DoctrineRapidInserter(Article::class, $this->createEntityManager($platform), [
			DoctrineRapidInserter::Mode => DoctrineRapidInserter::ModeUpsert,
		]);
		$inserter->addRaw([
			'id' => 1,
			'title' => 'foo',
			'content' => 'bar',
		]);

		if ($platform === 'sqlite') {
			$expected = "INSERT INTO `articles` (`id`, `title`, `content`) VALUES ('1', 'foo', 'bar') ON CONFLICT(`id`) DO UPDATE SET `title` = excluded.`title`, `content` = excluded.`content`;";
		} else {
			$expected = "INSERT INTO `articles` (`id`, `title`, `content`) VALUES ('1', 'foo', 'bar') ON DUPLICATE KEY UPDATE `title` = VALUES(`title`), `content` = VALUES(`content`);";
		}

		$this->assertSame($expected, $inserter->getSql());
	}

	#[TestWith(['mysql'])]
	#[TestWith(['sqlite'])]
	public function testInsertNonExisting(string $platform): void
	{
		$inserter = new DoctrineRapidInserter(Article::class, $this->createEntityManager($platform), [
			DoctrineRapidInserter::Mode => DoctrineRapidInserter::ModeInsertNonExisting,
		]);
		$inserter->addRaw([
			'id' => 1,
			'title' => 'foo',
			'content' => 'bar',
		]);

		if ($platform === 'sqlite') {
			$expected = "INSERT INTO `articles` (`id`, `title`, `content`) VALUES ('1', 'foo', 'bar') ON CONFLICT(`id`) DO NOTHING;";
		} else {
			$expected = "INSERT INTO `articles` (`id`, `title`, `content`) VALUES ('1', 'foo', 'bar') ON DUPLICATE KEY UPDATE `id` = `id`;";
		}

		$this->assertSame($expected, $inserter->getSql());
	}

	#[TestWith(['mysql'])]
	#[TestWith(['sqlite'])]
	public function testUpsertColumnsToUpdate(string $platform): void
	{
		$inserter = new DoctrineRapidInserter(Article::class, $this->createEntityManager($platform), [
			DoctrineRapidInserter::Mode => DoctrineRapidInserter::ModeUpsert,
			DoctrineRapidInserter::ColumnsToUpdate => ['title'],
		]);
		$inserter->addRaw([
			'id' => 1,
			'title' => 'foo',
			'content' => 'bar',
		]);

		if ($platform === 'sqlite') {
			$expected = "INSERT INTO `articles` (`id`, `title`, `content`) VALUES ('1', 'foo', 'bar') ON CONFLICT(`id`) DO UPDATE SET `title` = excluded.`title`;";
		} else {
			$expected = "INSERT INTO `articles` (`id`, `title`, `content`) VALUES ('1', 'foo', 'bar') ON DUPLICATE KEY UPDATE `title` = VALUES(`title`);";
		}

		$this->assertSame($expected, $inserter->getSql());
	}

	public function testMissingFields(): void
	{
		$inserter = new DoctrineRapidInserter(Article::class, $this->createEntityManager());
		$inserter->addRaw([
			'id' => 1,
			'title' => 'foo',
			'content' => 'bar',
		]);

		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionMessage('Missing fields: title');

		$inserter->addRaw([
			'id' => 2,
			'content' => 'qux',
		]);
	}

	public function testExtraFields(): void
	{
		$inserter = new DoctrineRapidInserter(Article::class, $this->createEntityManager());
		$inserter->addRaw([
			'id' => 1,
			'title' => 'foo',
			'content' => 'bar',
		]);

		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionMessage('Extra fields: baz');

		$inserter->addRaw([
			'id' => 2,
			'title' => 'baz',
			'content' => 'qux',
			'baz' => 'quux',
		]);
	}

	public function testMissingAndExtraFields(): void
	{
		$inserter = new DoctrineRapidInserter(Article::class, $this->createEntityManager());
		$inserter->addRaw([
			'id' => 1,
			'title' => 'foo',
			'content' => 'bar',
		]);

		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionMessage('Missing fields: title, Extra fields: baz');

		$inserter->addRaw([
			'id' => 2,
			'content' => 'qux',
			'baz' => 'quux',
		]);
	}

	public function testInvalidOrder(): void
	{
		$inserter = new DoctrineRapidInserter(Article::class, $this->createEntityManager());
		$inserter->addRaw([
			'id' => 1,
			'title' => 'foo',
			'content' => 'bar',
		]);

		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionMessage('Data must have same order.');

		$inserter->addRaw([
			'title' => 'baz',
			'id' => 2,
			'content' => 'qux',
		]);
	}

}

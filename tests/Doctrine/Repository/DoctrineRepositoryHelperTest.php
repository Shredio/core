<?php declare(strict_types = 1);

namespace Tests\Doctrine\Repository;

use Doctrine\Deprecations\Deprecation;
use Doctrine\DBAL\DriverManager;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\ORMSetup;
use Doctrine\ORM\Tools\SchemaTool;
use Doctrine\Persistence\ManagerRegistry;
use PHPUnit\Framework\TestCase;
use Shredio\Core\Bridge\Doctrine\EntityManagerRegistry;
use Shredio\Core\Bridge\Doctrine\Repository\DoctrineRepositoryHelper;
use SortDirection;
use Tests\Doctrine\entity\Article;

final class DoctrineRepositoryHelperTest extends TestCase
{

	/** Doctrine ORM 3.7 deprecated string sort directions under this link. */
	private const string StringSortDirectionDeprecation = 'https://github.com/doctrine/orm/issues/11313';

	private DoctrineRepositoryHelper $helper;

	public function testFetchByOrdersByTheGivenDirection(): void
	{
		$rows = $this->helper->fetchBy(Article::class, orderBy: ['title' => SortDirection::Descending], select: ['id'])->toArray();

		$this->assertSame([['id' => 3], ['id' => 1], ['id' => 2]], $rows);
		$this->assertNoStringSortDirectionDeprecation();
	}

	public function testFindByOrdersByTheGivenDirection(): void
	{
		$articles = $this->helper->findBy(Article::class, [], ['title' => SortDirection::Ascending]);

		$this->assertSame([2, 1, 3], array_map(static fn (Article $article): int => $article->getId(), $articles));
		$this->assertNoStringSortDirectionDeprecation();
	}

	public function testFindOneByOrdersByTheGivenDirection(): void
	{
		$article = $this->helper->findOneBy(Article::class, [], ['title' => SortDirection::Descending]);

		$this->assertSame(3, $article?->getId());
		$this->assertNoStringSortDirectionDeprecation();
	}

	protected function setUp(): void
	{
		Deprecation::enableTrackingDeprecations();

		$configuration = ORMSetup::createAttributeMetadataConfiguration([dirname(__DIR__) . '/entity'], true);
		$connection = DriverManager::getConnection(['driver' => 'pdo_sqlite', 'memory' => true], $configuration);
		$entityManager = new EntityManager($connection, $configuration);
		new SchemaTool($entityManager)->createSchema([$entityManager->getClassMetadata(Article::class)]);

		$entityManager->persist(new Article(1, 'b', 'Second by title.'));
		$entityManager->persist(new Article(2, 'a', 'First by title.'));
		$entityManager->persist(new Article(3, 'c', 'Third by title.'));
		$entityManager->flush();
		$entityManager->clear();

		$registry = $this->createStub(ManagerRegistry::class);
		$registry->method('getManagerForClass')->willReturn($entityManager);

		$this->helper = new DoctrineRepositoryHelper(new EntityManagerRegistry($registry));
	}

	protected function tearDown(): void
	{
		Deprecation::disable();
	}

	private function assertNoStringSortDirectionDeprecation(): void
	{
		$this->assertSame(0, Deprecation::getTriggeredDeprecations()[self::StringSortDirectionDeprecation] ?? 0);
	}

}

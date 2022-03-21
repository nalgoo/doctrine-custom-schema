<?php
declare(strict_types=1);

namespace Nalgoo\Doctrine\CustomSchema\Tests;

use Doctrine\Common\Annotations\AnnotationRegistry;
use Doctrine\DBAL\DriverManager;
use Doctrine\DBAL\Platforms\MariaDb1027Platform;
use Doctrine\Migrations\Provider\OrmSchemaProvider;
use Doctrine\Migrations\Provider\SchemaProvider;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Tools\Setup;
use Nalgoo\Doctrine\CustomSchema\SchemaTool\CustomSchemaListener;
use PHPUnit\Framework\TestCase;

final class ForeignKeyTest extends TestCase
{
	private ?SchemaProvider $schemaProvider = null;

	public function setUp(): void
	{
		AnnotationRegistry::registerUniqueLoader('class_exists');

		if (!$this->schemaProvider) {
			$config = Setup::createAnnotationMetadataConfiguration([__DIR__], true, null, null, false);

			$connection = DriverManager::getConnection(
				[
					'url' => 'mysql://user:pass@localhost/db',
					'platform' => new MariaDb1027Platform(),
					'wrapperClass' => ConnectionMock::class,
				],
				$config,
			);

			$em = EntityManager::create($connection, $config);

			CustomSchemaListener::register($em);

			$this->schemaProvider = new OrmSchemaProvider($em);
		}
	}

	public function testClassAnnotation(): void
	{
		$schema = $this->schemaProvider->createSchema();

		$table = $schema->getTable('comment');

		$this->assertTrue($table->hasForeignKey('fk_post_reference'));

		$fk = $table->getForeignKey('fk_post_reference');

		$this->assertEqualsCanonicalizing(['post_id'], $fk->getLocalColumns());
		$this->assertEquals('post', $fk->getForeignTableName());
		$this->assertEqualsCanonicalizing(['id'], $fk->getForeignColumns());
		$this->assertEquals(null, $fk->onUpdate());
		$this->assertEquals('SET NULL', $fk->onDelete());
	}

	public function testPropertyAnnotation(): void
	{
		$schema = $this->schemaProvider->createSchema();

		$table = $schema->getTable('comment');

		$this->assertTrue($table->hasForeignKey('fk_comment_user_id'));

		$fk = $table->getForeignKey('fk_comment_user_id');

		$this->assertEqualsCanonicalizing(['user_id'], $fk->getLocalColumns());
		$this->assertEquals('user', $fk->getForeignTableName());
		$this->assertEqualsCanonicalizing(['id'], $fk->getForeignColumns());
		$this->assertEquals('CASCADE', $fk->onUpdate());
		$this->assertEquals('CASCADE', $fk->onDelete());
	}
}

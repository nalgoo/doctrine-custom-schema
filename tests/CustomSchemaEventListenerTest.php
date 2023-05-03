<?php
declare(strict_types=1);

namespace Nalgoo\Doctrine\CustomSchema\Tests;

use Doctrine\DBAL\DriverManager;
use Doctrine\DBAL\Platforms\MariaDBPlatform;
use Doctrine\Migrations\Provider\OrmSchemaProvider;
use Doctrine\Migrations\Provider\SchemaProvider;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\ORMSetup;
use Nalgoo\Doctrine\CustomSchema\CustomSchemaEventListener;
use Nalgoo\Doctrine\CustomSchema\Tests\Mocks\ConnectionMock;
use PHPUnit\Framework\TestCase;

final class CustomSchemaEventListenerTest extends TestCase
{
	private ?SchemaProvider $schemaProvider = null;

	public function setUp(): void
	{
		if (!$this->schemaProvider) {
			$config = ORMSetup::createAttributeMetadataConfiguration([__DIR__], true);

			$connection = DriverManager::getConnection(
				[
					'url' => 'mysql://user:pass@localhost/db',
					'platform' => new MariaDBPlatform(),
					'wrapperClass' => ConnectionMock::class,
				],
				$config,
			);

			$em = EntityManager::create($connection, $config);

			CustomSchemaEventListener::register($em)->renameIdentifiers();

			$this->schemaProvider = new OrmSchemaProvider($em);
		}
	}

	public function testClassAnnotation(): void
	{
		$schema = $this->schemaProvider->createSchema();

		$table = $schema->getTable('comment');

		$this->assertTrue($table->hasForeignKey('FK_post_reference'));

		$fk = $table->getForeignKey('FK_post_reference');

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

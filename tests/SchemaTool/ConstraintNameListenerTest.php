<?php

namespace Nalgoo\Doctrine\CustomSchema\Tests\SchemaTool;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Schema\SchemaException;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Tools\Event\GenerateSchemaEventArgs;
use Nalgoo\Doctrine\CustomSchema\Generators\ConstraintNameGenerator;
use Nalgoo\Doctrine\CustomSchema\SchemaTool\ConstraintNameListener;
use PHPUnit\Framework\TestCase;

class ConstraintNameListenerTest extends TestCase
{
	private $schema;

	public function setUp(): void
	{
		$this->schema = new Schema();

		$userTable = $this->schema->createTable('user');
		$userTable->addColumn('id', Types::INTEGER);
		$userTable->setPrimaryKey(['id']);

		$commentTable = $this->schema->createTable('comment');
		$commentTable->addColumn('id', Types::INTEGER);
		$commentTable->setPrimaryKey(['id']);
		$commentTable->addColumn('user_id', Types::INTEGER);
		$commentTable->addForeignKeyConstraint('user', ['user_id'], ['id']);
	}

	public function testPostGenerateSchema()
	{
		$constraintNameGenerator = new ConstraintNameGenerator();
		$constraintNameListener = new ConstraintNameListener($constraintNameGenerator);
		$generateSchemaEventArgs = new GenerateSchemaEventArgs($this->createMock(EntityManager::class), $this->schema);

		$commentTable = $this->schema->getTable('comment');

		$expectedForeignKeyName = 'fk_comment_user_id';

		// expected identifier should not exist before processing
		$this->assertFalse($commentTable->hasForeignKey($expectedForeignKeyName));

		// process Schema
		$constraintNameListener->postGenerateSchema($generateSchemaEventArgs);

		// now check if expected identifier exist
		$fk = $commentTable->getForeignKey($expectedForeignKeyName);
		$this->assertEquals($expectedForeignKeyName, $fk->getName());
	}
}

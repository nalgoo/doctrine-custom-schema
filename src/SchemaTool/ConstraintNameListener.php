<?php

namespace Nalgoo\Doctrine\CustomSchema\SchemaTool;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Tools\Event\GenerateSchemaEventArgs;
use Doctrine\ORM\Tools\ToolEvents;
use Nalgoo\Doctrine\CustomSchema\ConstraintNameGeneratorInterface;
use Nalgoo\Doctrine\CustomSchema\Generators\ConstraintNameGenerator;

class ConstraintNameListener
{
	public function __construct(
		private ConstraintNameGeneratorInterface $constraintNameGenerator,
	) {
	}

	public static function register(
		EntityManager $em,
		?ConstraintNameGeneratorInterface $constraintNameGenerator = null,
	): void	{
		$em->getEventManager()->addEventListener(
			ToolEvents::postGenerateSchema,
			new self($constraintNameGenerator ?? new ConstraintNameGenerator()),
		);
	}

	public function postGenerateSchema(GenerateSchemaEventArgs $eventArgs): void
	{
		$schema = $eventArgs->getSchema();

		foreach ($schema->getTables() as $table) {
			// remove and re-add  foreign keys
			foreach ($table->getForeignKeys() as $foreignKey) {
				$table->removeForeignKey($foreignKey->getName());

				$table->addForeignKeyConstraint(
					$foreignKey->getForeignTableName(),
					$foreignKey->getLocalColumns(),
					$foreignKey->getForeignColumns(),
					[],
					$this->constraintNameGenerator->getForeignKeyName(
						$table->getName(),
						$foreignKey->getLocalColumns(),
						$foreignKey->getForeignTableName(),
						$foreignKey->getForeignColumns(),
					),
				);
			}

			// rename NON-PRIMARY indexes
			foreach ($table->getIndexes() as $index) {
				if (!$index->isPrimary()) {
					$table->renameIndex(
						$index->getName(),
						$this->constraintNameGenerator->getIndexName(
							$table->getName(),
							$index->getColumns(),
							$index->isUnique(),
						),
					);
				}
			}
		}
	}
}
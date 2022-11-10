<?php

namespace Nalgoo\Doctrine\CustomSchema;

use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Tools\Event\GenerateSchemaEventArgs;
use Doctrine\ORM\Tools\Event\GenerateSchemaTableEventArgs;
use Doctrine\ORM\Tools\ToolEvents;
use Nalgoo\Doctrine\CustomSchema\Exceptions\ReflectionException;
use Nalgoo\Doctrine\CustomSchema\Generators\ConstraintNameGenerator;

class CustomSchemaEventListener
{
	private bool $renameIdentifiers = false;

	private array $ignoredForeignKeys = [];

	private ?string $onUpdate = null;

	public function __construct(
		private AnnotationParser $annotationParser,
		private ?ConstraintNameGeneratorInterface $constraintNameGenerator = null,
	) {
	}

	public static function register(
		EntityManager $em,
		?ConstraintNameGeneratorInterface $constraintNameGenerator = null,
		?AnnotationParser $annotationParser = null,
	): self	{
		$me = new self(
			$annotationParser ?? new AnnotationParser(new AnnotationReader()),
			$constraintNameGenerator,
		);

		$em->getEventManager()->addEventListener(
			[ToolEvents::postGenerateSchemaTable, ToolEvents::postGenerateSchema],
			$me,
		);

		return $me;
	}

	/**
	 * If enabled and ConstraintNameGenerator is supplied, all non-manually set Indexes and ForeignKeys identifiers will
	 * be renamed with ConstraintNameGenerator
	 */
	public function renameIdentifiers(bool $yesNo = true): self
	{
		$this->renameIdentifiers = $yesNo;
		if (!$this->constraintNameGenerator) {
			$this->constraintNameGenerator = new ConstraintNameGenerator();
		}

		return $this;
	}

	public function setOnUpdate(string $onUpdate = 'CASCADE'): self
	{
		$this->onUpdate = $onUpdate;

		return $this;
	}

	public function postGenerateSchemaTable(GenerateSchemaTableEventArgs $eventArgs): void
	{
		$reflectionClass = $eventArgs->getClassMetadata()->getReflectionClass();

		if (!$reflectionClass) {
			throw new ReflectionException('Could not get Reflection class');
		}

		$table = $eventArgs->getClassTable();

		$foreignKeys = $this->annotationParser->extractForeignKeys($reflectionClass);

		foreach ($foreignKeys as $foreignKey) {
			if ($foreignKey->getName()) {
				$name = $this->ignoredForeignKeys[] = $foreignKey->getName();
			} else {
				$name = $this->constraintNameGenerator?->getForeignKeyName(
					$table->getName(),
					$foreignKey->getLocalColumns(),
					$foreignKey->getForeignTableName(),
					$foreignKey->getForeignColumns(),
				);
			}

			$table->addForeignKeyConstraint(
				$foreignKey->getForeignTableName(),
				$foreignKey->getLocalColumns(),
				$foreignKey->getForeignColumns(),
				$foreignKey->getOptions(),
				$name,
			);
		}
	}

	/**
	 * @throws \Doctrine\DBAL\Schema\SchemaException
	 */
	public function postGenerateSchema(GenerateSchemaEventArgs $eventArgs): void
	{
		if ($this->renameIdentifiers) {
			$this->doRenameIdentifiers($eventArgs->getSchema());
		}

		if ($this->onUpdate) {
			$this->doSetOnUpdate($eventArgs->getSchema());
		}
	}

	/**
	 * @throws \Doctrine\DBAL\Schema\SchemaException
	 */
	private function doRenameIdentifiers(Schema $schema): void
	{
		foreach ($schema->getTables() as $table) {
			$foreignKeys = [];

			// remove foreign keys
			foreach ($table->getForeignKeys() as $foreignKey) {
				// but ignore foreign keys with already set identifiers
				if (!in_array($foreignKey->getName(), $this->ignoredForeignKeys)) {
					$foreignKeys[] = $foreignKey;
					$table->removeForeignKey($foreignKey->getName());
				}
			}

			// rename NON-PRIMARY indexes
			// can not use renameIndex() method, because it is case-insensitive
			foreach ($table->getIndexes() as $index) {
				if (!$index->isPrimary()) {
					$table->dropIndex($index->getName());

					if ($index->isUnique()) {
						$table->addUniqueIndex(
							$index->getColumns(),
							$this->constraintNameGenerator->getIndexName(
								$table->getName(),
								$index->getColumns(),
								true,
							),
							$index->getOptions(),
						);
					} else {
						$table->addIndex(
							$index->getColumns(),
							$this->constraintNameGenerator->getIndexName(
								$table->getName(),
								$index->getColumns(),
								false,
							),
							$index->getFlags(),
							$index->getOptions(),
						);
					}
				}
			}

			// re-add foreign keys
			foreach ($foreignKeys as $foreignKey) {
				$table->addForeignKeyConstraint(
					$foreignKey->getForeignTableName(),
					$foreignKey->getLocalColumns(),
					$foreignKey->getForeignColumns(),
					$foreignKey->getOptions(),
					$this->constraintNameGenerator->getForeignKeyName(
						$table->getName(),
						$foreignKey->getLocalColumns(),
						$foreignKey->getForeignTableName(),
						$foreignKey->getForeignColumns(),
					),
				);
			}
		}
	}

	private function doSetOnUpdate(Schema $schema): void
	{
		foreach ($schema->getTables() as $table) {
			foreach ($table->getForeignKeys() as $foreignKey) {
				if (!$foreignKey->onUpdate()) {
					$table->removeForeignKey($foreignKey->getName());
					$table->addForeignKeyConstraint(
						$foreignKey->getForeignTableName(),
						$foreignKey->getLocalColumns(),
						$foreignKey->getForeignColumns(),
						[...$foreignKey->getOptions(), 'onUpdate' => $this->onUpdate],
						$foreignKey->getName(),
					);
				}
			}
		}
	}
}

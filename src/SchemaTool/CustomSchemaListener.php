<?php
declare(strict_types=1);

namespace Nalgoo\Doctrine\CustomSchema\SchemaTool;

use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\DBAL\Schema\Table;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Tools\Event\GenerateSchemaTableEventArgs;
use Doctrine\ORM\Tools\ToolEvents;
use Nalgoo\Doctrine\CustomSchema\Annotations\Annotation;
use Nalgoo\Doctrine\CustomSchema\Annotations\CustomSchema;
use Nalgoo\Doctrine\CustomSchema\Annotations\ForeignKey;
use Nalgoo\Doctrine\CustomSchema\ConstraintNameGeneratorInterface;
use Nalgoo\Doctrine\CustomSchema\SchemaTool\Exceptions\ReflectionException;
use Nalgoo\Doctrine\CustomSchema\SchemaTool\Exceptions\SchemaToolException;

class CustomSchemaListener
{
	public function __construct(
		private AnnotationReader $annotationReader,
		private ?ConstraintNameGeneratorInterface $constraintNameGenerator = null
	) {
	}

	public static function register(
		EntityManager $em,
		?AnnotationReader $annotationReader = null,
		?ConstraintNameGeneratorInterface $constraintNameGenerator = null,
	): void	{
		$em->getEventManager()->addEventListener(
			ToolEvents::postGenerateSchemaTable,
			new self(
				$annotationReader ?? new AnnotationReader(),
				$constraintNameGenerator,
			),
		);
	}

	public function postGenerateSchemaTable(GenerateSchemaTableEventArgs $args): void
	{
		$reflectionClass = $args->getClassMetadata()->getReflectionClass();

		if (!$reflectionClass) {
			throw new ReflectionException('Could not get Reflection class');
		}

		$customSchemaAnnotation = $this->annotationReader->getClassAnnotation($reflectionClass, CustomSchema::class);

		if ($customSchemaAnnotation) {
			foreach ((array) $customSchemaAnnotation->value as $annotation) {
				$this->processAnnotation($args->getClassTable(), $annotation);
			}
		}

		foreach ($reflectionClass->getProperties() as $reflectionProperty) {
			$propertyAnnotations = $this->annotationReader->getPropertyAnnotations($reflectionProperty);

			if ($annotation = $this->getAnnotationByType($propertyAnnotations, Annotation::class)) {
				/** @var Column|null $columnAnnotation */
				$columnAnnotation = $this->getAnnotationByType($propertyAnnotations, Column::class);

				$this->processAnnotation(
					$args->getClassTable(),
					$annotation,
					$columnAnnotation?->name ?? $reflectionProperty->getName(),
				);
			}
		}
	}

	private function getAnnotationByType(array $annotations, string $type): ?object
	{
		foreach ($annotations as $annotation) {
			if ($annotation instanceof $type) {
				return $annotation;
			}
		}
		return null;
	}

	private function processAnnotation(Table $table, object $annotation, ?string $columnName = null): void
	{
		if ($annotation instanceof ForeignKey) {
			$this->processForeignKeyAnnotation($table, $annotation, $columnName);
		}
	}

	/**
	 * @throws \Doctrine\DBAL\Schema\SchemaException
	 */
	private function processForeignKeyAnnotation(Table $table, ForeignKey $annotation, ?string $columnName): void
	{
		$columns = (array) ($annotation->column ?? $columnName);

		if (!$columns) {
			throw new SchemaToolException('ForeignKey Annotation is missing `column` property');
		}

		$refColumns = (array) $annotation->refColumn;

		if (count($columns) !== count($refColumns)) {
			throw new SchemaToolException('Number of columns needs to be same as number of referenced columns');
		}

		$options = [
			'onUpdate' => str_replace('_', ' ', $annotation->onUpdate),
			'onDelete' => str_replace('_', ' ', $annotation->onDelete),
		];

		$name = $annotation->name
			?? $this->constraintNameGenerator->getForeignKeyName(
				$table->getName(),
				$columns,
				$annotation->refTable,
				$refColumns,
			);

		$table->addForeignKeyConstraint(
			$annotation->refTable,
			$columns,
			$refColumns,
			$options,
			$name,
		);
	}
}

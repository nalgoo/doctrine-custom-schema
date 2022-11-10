<?php

namespace Nalgoo\Doctrine\CustomSchema;

use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\DBAL\Schema\ForeignKeyConstraint;
use Doctrine\ORM\Mapping\Column;
use Nalgoo\Doctrine\CustomSchema\Annotations\Annotation;
use Nalgoo\Doctrine\CustomSchema\Annotations\CustomSchema;
use Nalgoo\Doctrine\CustomSchema\Annotations\ForeignKey;
use Nalgoo\Doctrine\CustomSchema\Exceptions\SchemaToolException;

class AnnotationParser
{
	public function __construct(
		private AnnotationReader $annotationReader,
	) {

	}

	/**
	 * @return ForeignKeyConstraint[]
	 */
	public function extractForeignKeys(\ReflectionClass $reflectionClass): array
	{
		$foreignKeys = [];

		$customSchemaAnnotation = $this->annotationReader->getClassAnnotation($reflectionClass, CustomSchema::class);

		if ($customSchemaAnnotation) {
			foreach ((array) $customSchemaAnnotation->value as $annotation) {
				$foreignKeys[] = $this->processAnnotation($annotation);
			}
		}

		foreach ($reflectionClass->getProperties() as $reflectionProperty) {
			$propertyAnnotations = $this->annotationReader->getPropertyAnnotations($reflectionProperty);

			if ($annotation = $this->getAnnotationByType($propertyAnnotations, Annotation::class)) {
				/** @var Column|null $columnAnnotation */
				$columnAnnotation = $this->getAnnotationByType($propertyAnnotations, Column::class);

				$foreignKeys[] = $this->processAnnotation(
					$annotation,
					$columnAnnotation?->name ?? $reflectionProperty->getName(),
				);
			}
		}

		return array_filter($foreignKeys);
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

	private function processAnnotation(object $annotation, ?string $columnName = null): ?ForeignKeyConstraint
	{
		if ($annotation instanceof ForeignKey) {
			return $this->processForeignKeyAnnotation($annotation, $columnName);
		}

		return null;
	}

	/**
	 * @throws \Doctrine\DBAL\Schema\SchemaException
	 */
	private function processForeignKeyAnnotation(ForeignKey $annotation, ?string $columnName): ForeignKeyConstraint
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

		$name = $annotation->name ?? null;

		return new ForeignKeyConstraint(
			$columns,
			$annotation->refTable,
			$refColumns,
			$name,
			$options,
		);
	}
}

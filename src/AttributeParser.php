<?php
declare(strict_types=1);

namespace Nalgoo\Doctrine\CustomSchema;

use Doctrine\DBAL\Schema\ForeignKeyConstraint;
use Doctrine\ORM\Mapping\Column;
use Nalgoo\Doctrine\CustomSchema\Attributes\CustomSchema;
use Nalgoo\Doctrine\CustomSchema\Attributes\ForeignKey;
use Nalgoo\Doctrine\CustomSchema\Exceptions\SchemaToolException;

class AttributeParser
{
	/**
	 * @return ForeignKeyConstraint[]
	 */
	public function extractForeignKeys(\ReflectionClass $reflectionClass): array
	{
		$foreignKeys = [];

		$customSchemaAttribute = $reflectionClass->getAttributes(CustomSchema::class);
		$customSchemaAttribute = reset($customSchemaAttribute);

		if ($customSchemaAttribute) {
			foreach ($customSchemaAttribute->newInstance()->value as $attribute) {
				/**@var ForeignKey $attribute*/
				$foreignKeys[] = $this->processForeignKeyAttribute($attribute);
			}
		}

		foreach ($reflectionClass->getProperties() as $reflectionProperty) {

			$foreignKeyAttribute = $reflectionProperty->getAttributes(ForeignKey::class);
			$foreignKeyAttribute = reset($foreignKeyAttribute);

			if ($foreignKeyAttribute) {
				/**@var \ReflectionAttribute $foreignKeyAttribute*/

				$columnAttribute = $reflectionProperty->getAttributes(Column::class);
				$columnAttribute = reset($columnAttribute);
				/**@var \ReflectionAttribute $columnAttribute*/

				$foreignKeys[] = $this->processForeignKeyAttribute(
					$foreignKeyAttribute->newInstance(),
					$columnAttribute->newInstance()?->name ?? $reflectionProperty->getName(),
				);
			}
		}

		return array_filter($foreignKeys);
	}

	private function processForeignKeyAttribute(ForeignKey $attribute, ?string $columnName = null): ForeignKeyConstraint
	{
		$columns = (array) ($attribute->column ?? $columnName);

		if (!$columns) {
			throw new SchemaToolException('ForeignKey Annotation is missing `column` property');
		}

		$refColumns = (array) $attribute->refColumn;

		if (count($columns) !== count($refColumns)) {
			throw new SchemaToolException('Number of columns needs to be same as number of referenced columns');
		}

		$options = [
			'onUpdate' => str_replace('_', ' ', $attribute->onUpdate),
			'onDelete' => str_replace('_', ' ', $attribute->onDelete),
		];

		$name = $attribute->name ?? null;

		return new ForeignKeyConstraint(
			$columns,
			$attribute->refTable,
			$refColumns,
			$name,
			$options,
		);
	}
}

<?php
declare(strict_types=1);

namespace Nalgoo\Doctrine\CustomSchema\Generators;

use Nalgoo\Doctrine\CustomSchema\ConstraintNameGeneratorInterface;
use Webmozart\Assert\Assert;

class ConstraintNameGenerator implements ConstraintNameGeneratorInterface
{
	public function getForeignKeyName(
		string $tableName,
		array  $columnNames,
		string $foreignTableName,
		array  $foreignColumnNames,
		int    $maxIdentifierLength = 63,
	): string {
		return $this->generateIdentifier('fk', [$tableName, ...$columnNames], $maxIdentifierLength);
	}

	public function getIndexName(
		string $tableName,
		array  $columnNames,
		bool   $isUnique,
		int    $maxIdentifierLength = 63,
	): string {
		return $this->generateIdentifier($isUnique ? 'uq' : 'ix', [$tableName, ...$columnNames], $maxIdentifierLength);
	}

	private function generateIdentifier(string $prefix, array $components, int $maxIdentifierLength): string
	{
		Assert::greaterThanEq($maxIdentifierLength, strlen($prefix) + 8);

		$generatedName = implode('_', [$prefix, ...$components]);

		if (strlen($generatedName) > $maxIdentifierLength) {
			$hash = base_convert((string) crc32(implode('.', $components)), 10, 36);
			$paddedHash = str_pad($hash, 7, '0', STR_PAD_LEFT);
			$generatedName = substr($generatedName, 0, $maxIdentifierLength - 8) . '_' . $paddedHash;
		}

		return $generatedName;
	}
}

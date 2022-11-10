<?php

namespace Nalgoo\Doctrine\CustomSchema\Tests\Generators;

use Nalgoo\Doctrine\CustomSchema\Generators\ConstraintNameGenerator;
use PHPUnit\Framework\TestCase;

class ConstraintNameGeneratorTest extends TestCase
{
	public function testGetForeignKeyName()
	{
		$generator = new ConstraintNameGenerator();

		$fk1 = $generator->getForeignKeyName('table1', ['a', 'b'],'table2', ['c', 'd']);
		$this->assertEquals('fk_table1_a_b', $fk1);

		$fk2 = $generator->getForeignKeyName(
			'table1',
			['astronomically_long_name', 'another_name_which_is_long_enough'],
			'table2',
			['c', 'd'],
		);
		$this->assertMatchesRegularExpression(
			'/^fk_table1_astronomically_long_name_another_name_which_i_([0-9a-z]{7})$/',
			$fk2,
		);
	}

	public function testGetIndexName()
	{
		$generator = new ConstraintNameGenerator();

		$fk1 = $generator->getIndexName('table1', ['a', 'b'], false);
		$this->assertEquals('ix_table1_a_b', $fk1);

		$fk2 = $generator->getIndexName('table1', ['a', 'b'], true);
		$this->assertEquals('uq_table1_a_b', $fk2);

		$fk3 = $generator->getIndexName(
			'table1',
			['astronomically_long_name', 'another_name_which_is_long_enough'],
			false,
		);
		$this->assertMatchesRegularExpression(
			'/^ix_table1_astronomically_long_name_another_name_which_i_([0-9a-z]{7})$/',
			$fk3,
		);
	}

	/**
	 * Test Exception when $maxIdentifierLength is lower than 10
	 */
	public function testGetForeignKeyNameException()
	{
		$generator = new ConstraintNameGenerator();
		$this->expectException(\InvalidArgumentException::class);
		$generator->getForeignKeyName('table', [], 'tbl', [], 9);
	}
}

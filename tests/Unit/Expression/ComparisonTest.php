<?php

declare(strict_types=1);

namespace Contributte\Criteria\Tests\Unit\Expression;

use Contributte\Criteria\Expression\Comparison;
use Contributte\Criteria\Expression\Expression;
use PHPUnit\Framework\TestCase;

final class ComparisonTest extends TestCase
{
	public function testImplementsExpression(): void
	{
		$comparison = new Comparison('field', Comparison::EQ, 'value');

		$this->assertInstanceOf(Expression::class, $comparison);
	}

	public function testGetField(): void
	{
		$comparison = new Comparison('name', Comparison::EQ, 'John');

		$this->assertSame('name', $comparison->getField());
	}

	public function testGetOperator(): void
	{
		$comparison = new Comparison('name', Comparison::NEQ, 'John');

		$this->assertSame(Comparison::NEQ, $comparison->getOperator());
	}

	public function testGetValue(): void
	{
		$comparison = new Comparison('age', Comparison::GT, 18);

		$this->assertSame(18, $comparison->getValue());
	}

	public function testGetValueWithNull(): void
	{
		$comparison = new Comparison('deletedAt', Comparison::IS_NULL, null);

		$this->assertNull($comparison->getValue());
	}

	public function testGetValueWithArray(): void
	{
		$values = ['active', 'pending'];
		$comparison = new Comparison('status', Comparison::IN, $values);

		$this->assertSame($values, $comparison->getValue());
	}

	/**
	 * @dataProvider operatorProvider
	 */
	public function testAllOperators(string $operator): void
	{
		$comparison = new Comparison('field', $operator, 'value');

		$this->assertSame($operator, $comparison->getOperator());
	}

	/**
	 * @return array<string, array{string}>
	 */
	public static function operatorProvider(): array
	{
		return [
			'EQ' => [Comparison::EQ],
			'NEQ' => [Comparison::NEQ],
			'LT' => [Comparison::LT],
			'LTE' => [Comparison::LTE],
			'GT' => [Comparison::GT],
			'GTE' => [Comparison::GTE],
			'IN' => [Comparison::IN],
			'NIN' => [Comparison::NIN],
			'CONTAINS' => [Comparison::CONTAINS],
			'STARTS_WITH' => [Comparison::STARTS_WITH],
			'ENDS_WITH' => [Comparison::ENDS_WITH],
			'IS_NULL' => [Comparison::IS_NULL],
			'IS_NOT_NULL' => [Comparison::IS_NOT_NULL],
		];
	}

	public function testWithRelationTraversal(): void
	{
		$comparison = new Comparison('author->name', Comparison::EQ, 'Jon Snow');

		$this->assertSame('author->name', $comparison->getField());
	}
}

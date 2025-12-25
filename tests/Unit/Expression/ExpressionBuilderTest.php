<?php

declare(strict_types=1);

namespace Contributte\Criteria\Tests\Unit\Expression;

use Contributte\Criteria\Expression\Comparison;
use Contributte\Criteria\Expression\CompositeExpression;
use Contributte\Criteria\Expression\ExpressionBuilder;
use PHPUnit\Framework\TestCase;

final class ExpressionBuilderTest extends TestCase
{
	private ExpressionBuilder $builder;

	protected function setUp(): void
	{
		$this->builder = new ExpressionBuilder();
	}

	public function testEq(): void
	{
		$comparison = $this->builder->eq('name', 'John');

		$this->assertInstanceOf(Comparison::class, $comparison);
		$this->assertSame('name', $comparison->getField());
		$this->assertSame(Comparison::EQ, $comparison->getOperator());
		$this->assertSame('John', $comparison->getValue());
	}

	public function testNeq(): void
	{
		$comparison = $this->builder->neq('status', 'deleted');

		$this->assertInstanceOf(Comparison::class, $comparison);
		$this->assertSame('status', $comparison->getField());
		$this->assertSame(Comparison::NEQ, $comparison->getOperator());
		$this->assertSame('deleted', $comparison->getValue());
	}

	public function testLt(): void
	{
		$comparison = $this->builder->lt('age', 18);

		$this->assertInstanceOf(Comparison::class, $comparison);
		$this->assertSame('age', $comparison->getField());
		$this->assertSame(Comparison::LT, $comparison->getOperator());
		$this->assertSame(18, $comparison->getValue());
	}

	public function testLte(): void
	{
		$comparison = $this->builder->lte('price', 100.00);

		$this->assertInstanceOf(Comparison::class, $comparison);
		$this->assertSame('price', $comparison->getField());
		$this->assertSame(Comparison::LTE, $comparison->getOperator());
		$this->assertSame(100.00, $comparison->getValue());
	}

	public function testGt(): void
	{
		$comparison = $this->builder->gt('rating', 4);

		$this->assertInstanceOf(Comparison::class, $comparison);
		$this->assertSame('rating', $comparison->getField());
		$this->assertSame(Comparison::GT, $comparison->getOperator());
		$this->assertSame(4, $comparison->getValue());
	}

	public function testGte(): void
	{
		$comparison = $this->builder->gte('quantity', 10);

		$this->assertInstanceOf(Comparison::class, $comparison);
		$this->assertSame('quantity', $comparison->getField());
		$this->assertSame(Comparison::GTE, $comparison->getOperator());
		$this->assertSame(10, $comparison->getValue());
	}

	public function testIn(): void
	{
		$values = ['active', 'pending', 'approved'];
		$comparison = $this->builder->in('status', $values);

		$this->assertInstanceOf(Comparison::class, $comparison);
		$this->assertSame('status', $comparison->getField());
		$this->assertSame(Comparison::IN, $comparison->getOperator());
		$this->assertSame($values, $comparison->getValue());
	}

	public function testNotIn(): void
	{
		$values = ['deleted', 'banned'];
		$comparison = $this->builder->notIn('status', $values);

		$this->assertInstanceOf(Comparison::class, $comparison);
		$this->assertSame('status', $comparison->getField());
		$this->assertSame(Comparison::NIN, $comparison->getOperator());
		$this->assertSame($values, $comparison->getValue());
	}

	public function testContains(): void
	{
		$comparison = $this->builder->contains('description', 'important');

		$this->assertInstanceOf(Comparison::class, $comparison);
		$this->assertSame('description', $comparison->getField());
		$this->assertSame(Comparison::CONTAINS, $comparison->getOperator());
		$this->assertSame('important', $comparison->getValue());
	}

	public function testStartsWith(): void
	{
		$comparison = $this->builder->startsWith('email', 'admin@');

		$this->assertInstanceOf(Comparison::class, $comparison);
		$this->assertSame('email', $comparison->getField());
		$this->assertSame(Comparison::STARTS_WITH, $comparison->getOperator());
		$this->assertSame('admin@', $comparison->getValue());
	}

	public function testEndsWith(): void
	{
		$comparison = $this->builder->endsWith('email', '@example.com');

		$this->assertInstanceOf(Comparison::class, $comparison);
		$this->assertSame('email', $comparison->getField());
		$this->assertSame(Comparison::ENDS_WITH, $comparison->getOperator());
		$this->assertSame('@example.com', $comparison->getValue());
	}

	public function testIsNull(): void
	{
		$comparison = $this->builder->isNull('deletedAt');

		$this->assertInstanceOf(Comparison::class, $comparison);
		$this->assertSame('deletedAt', $comparison->getField());
		$this->assertSame(Comparison::IS_NULL, $comparison->getOperator());
		$this->assertNull($comparison->getValue());
	}

	public function testIsNotNull(): void
	{
		$comparison = $this->builder->isNotNull('verifiedAt');

		$this->assertInstanceOf(Comparison::class, $comparison);
		$this->assertSame('verifiedAt', $comparison->getField());
		$this->assertSame(Comparison::IS_NOT_NULL, $comparison->getOperator());
		$this->assertNull($comparison->getValue());
	}

	public function testAndX(): void
	{
		$expr1 = $this->builder->eq('status', 'active');
		$expr2 = $this->builder->gt('age', 18);
		$expr3 = $this->builder->isNotNull('email');

		$composite = $this->builder->andX($expr1, $expr2, $expr3);

		$this->assertInstanceOf(CompositeExpression::class, $composite);
		$this->assertSame(CompositeExpression::TYPE_AND, $composite->getType());
		$this->assertCount(3, $composite->getExpressions());
	}

	public function testOrX(): void
	{
		$expr1 = $this->builder->eq('status', 'active');
		$expr2 = $this->builder->eq('status', 'pending');

		$composite = $this->builder->orX($expr1, $expr2);

		$this->assertInstanceOf(CompositeExpression::class, $composite);
		$this->assertSame(CompositeExpression::TYPE_OR, $composite->getType());
		$this->assertCount(2, $composite->getExpressions());
	}

	public function testNestedCompositeExpressions(): void
	{
		// (status = 'active' AND age > 18) OR (role = 'admin')
		$condition1 = $this->builder->andX(
			$this->builder->eq('status', 'active'),
			$this->builder->gt('age', 18)
		);
		$condition2 = $this->builder->eq('role', 'admin');

		$composite = $this->builder->orX($condition1, $condition2);

		$this->assertInstanceOf(CompositeExpression::class, $composite);
		$this->assertSame(CompositeExpression::TYPE_OR, $composite->getType());
		$this->assertCount(2, $composite->getExpressions());

		$firstExpression = $composite->getExpressions()[0];
		$this->assertInstanceOf(CompositeExpression::class, $firstExpression);
		$this->assertSame(CompositeExpression::TYPE_AND, $firstExpression->getType());
	}

	public function testEmptyAndX(): void
	{
		$composite = $this->builder->andX();

		$this->assertInstanceOf(CompositeExpression::class, $composite);
		$this->assertCount(0, $composite->getExpressions());
	}

	public function testEmptyOrX(): void
	{
		$composite = $this->builder->orX();

		$this->assertInstanceOf(CompositeExpression::class, $composite);
		$this->assertCount(0, $composite->getExpressions());
	}
}

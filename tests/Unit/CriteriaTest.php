<?php

declare(strict_types=1);

namespace Contributte\Criteria\Tests\Unit;

use Contributte\Criteria\Criteria;
use Contributte\Criteria\Expression\Comparison;
use Contributte\Criteria\Expression\CompositeExpression;
use Contributte\Criteria\Expression\ExpressionBuilder;
use Contributte\Criteria\Ordering;
use PHPUnit\Framework\TestCase;

final class CriteriaTest extends TestCase
{
	public function testCreate(): void
	{
		$criteria = Criteria::create();

		$this->assertInstanceOf(Criteria::class, $criteria);
		$this->assertNull($criteria->getWhereExpression());
		$this->assertSame([], $criteria->getOrderings());
		$this->assertNull($criteria->getFirstResult());
		$this->assertNull($criteria->getMaxResults());
	}

	public function testExprReturnsExpressionBuilder(): void
	{
		$expr = Criteria::expr();

		$this->assertInstanceOf(ExpressionBuilder::class, $expr);

		// Should return the same instance (singleton)
		$this->assertSame($expr, Criteria::expr());
	}

	public function testWhere(): void
	{
		$criteria = Criteria::create();
		$expression = Criteria::expr()->eq('name', 'John');

		$result = $criteria->where($expression);

		$this->assertSame($criteria, $result);
		$this->assertSame($expression, $criteria->getWhereExpression());
	}

	public function testWhereReplacesExistingExpression(): void
	{
		$criteria = Criteria::create();
		$expression1 = Criteria::expr()->eq('name', 'John');
		$expression2 = Criteria::expr()->eq('name', 'Jane');

		$criteria->where($expression1);
		$criteria->where($expression2);

		$this->assertSame($expression2, $criteria->getWhereExpression());
	}

	public function testAndWhere(): void
	{
		$criteria = Criteria::create();
		$expression1 = Criteria::expr()->eq('name', 'John');
		$expression2 = Criteria::expr()->gt('age', 18);

		$criteria->where($expression1)->andWhere($expression2);

		$whereExpression = $criteria->getWhereExpression();
		$this->assertInstanceOf(CompositeExpression::class, $whereExpression);
		$this->assertSame(CompositeExpression::TYPE_AND, $whereExpression->getType());
		$this->assertCount(2, $whereExpression->getExpressions());
	}

	public function testAndWhereOnEmptyCriteria(): void
	{
		$criteria = Criteria::create();
		$expression = Criteria::expr()->eq('name', 'John');

		$criteria->andWhere($expression);

		$this->assertSame($expression, $criteria->getWhereExpression());
	}

	public function testOrWhere(): void
	{
		$criteria = Criteria::create();
		$expression1 = Criteria::expr()->eq('status', 'active');
		$expression2 = Criteria::expr()->eq('status', 'pending');

		$criteria->where($expression1)->orWhere($expression2);

		$whereExpression = $criteria->getWhereExpression();
		$this->assertInstanceOf(CompositeExpression::class, $whereExpression);
		$this->assertSame(CompositeExpression::TYPE_OR, $whereExpression->getType());
		$this->assertCount(2, $whereExpression->getExpressions());
	}

	public function testOrWhereOnEmptyCriteria(): void
	{
		$criteria = Criteria::create();
		$expression = Criteria::expr()->eq('name', 'John');

		$criteria->orWhere($expression);

		$this->assertSame($expression, $criteria->getWhereExpression());
	}

	public function testOrderBySingleOrdering(): void
	{
		$criteria = Criteria::create();
		$ordering = Ordering::asc('name');

		$result = $criteria->orderBy($ordering);

		$this->assertSame($criteria, $result);
		$this->assertCount(1, $criteria->getOrderings());
		$this->assertSame($ordering, $criteria->getOrderings()[0]);
	}

	public function testOrderByMultipleOrderings(): void
	{
		$criteria = Criteria::create();
		$orderings = [
			Ordering::asc('lastName'),
			Ordering::desc('firstName'),
		];

		$criteria->orderBy($orderings);

		$this->assertCount(2, $criteria->getOrderings());
		$this->assertSame($orderings, $criteria->getOrderings());
	}

	public function testAddOrderBy(): void
	{
		$criteria = Criteria::create();
		$ordering1 = Ordering::asc('name');
		$ordering2 = Ordering::desc('createdAt');

		$criteria->orderBy($ordering1)->addOrderBy($ordering2);

		$this->assertCount(2, $criteria->getOrderings());
		$this->assertSame($ordering1, $criteria->getOrderings()[0]);
		$this->assertSame($ordering2, $criteria->getOrderings()[1]);
	}

	public function testSetFirstResult(): void
	{
		$criteria = Criteria::create();

		$result = $criteria->setFirstResult(10);

		$this->assertSame($criteria, $result);
		$this->assertSame(10, $criteria->getFirstResult());
	}

	public function testSetFirstResultNull(): void
	{
		$criteria = Criteria::create()
			->setFirstResult(10)
			->setFirstResult(null);

		$this->assertNull($criteria->getFirstResult());
	}

	public function testSetMaxResults(): void
	{
		$criteria = Criteria::create();

		$result = $criteria->setMaxResults(25);

		$this->assertSame($criteria, $result);
		$this->assertSame(25, $criteria->getMaxResults());
	}

	public function testSetMaxResultsNull(): void
	{
		$criteria = Criteria::create()
			->setMaxResults(25)
			->setMaxResults(null);

		$this->assertNull($criteria->getMaxResults());
	}

	public function testFluentInterface(): void
	{
		$criteria = Criteria::create()
			->where(Criteria::expr()->eq('status', 'active'))
			->andWhere(Criteria::expr()->gt('age', 18))
			->orderBy(Ordering::desc('createdAt'))
			->addOrderBy(Ordering::asc('name'))
			->setFirstResult(0)
			->setMaxResults(10);

		$this->assertInstanceOf(Criteria::class, $criteria);
		$this->assertInstanceOf(CompositeExpression::class, $criteria->getWhereExpression());
		$this->assertCount(2, $criteria->getOrderings());
		$this->assertSame(0, $criteria->getFirstResult());
		$this->assertSame(10, $criteria->getMaxResults());
	}
}

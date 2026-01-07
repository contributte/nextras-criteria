<?php declare(strict_types = 1);

namespace Tests\Cases\Unit;

use Contributte\Criteria\Criteria;
use Contributte\Criteria\Expression\CompositeExpression;
use Contributte\Criteria\Expression\ExpressionBuilder;
use Contributte\Criteria\Ordering;
use Contributte\Tester\Toolkit;
use Tester\Assert;

require_once __DIR__ . '/../../bootstrap.php';

// Create
Toolkit::test(function (): void {
	$criteria = Criteria::create();

	Assert::type(Criteria::class, $criteria);
	Assert::null($criteria->getWhereExpression());
	Assert::same([], $criteria->getOrderings());
	Assert::null($criteria->getFirstResult());
	Assert::null($criteria->getMaxResults());
});

// Expr returns ExpressionBuilder
Toolkit::test(function (): void {
	$expr = Criteria::expr();

	Assert::type(ExpressionBuilder::class, $expr);
	Assert::same($expr, Criteria::expr());
});

// Where
Toolkit::test(function (): void {
	$criteria = Criteria::create();
	$expression = Criteria::expr()->eq('name', 'John');

	$result = $criteria->where($expression);

	Assert::same($criteria, $result);
	Assert::same($expression, $criteria->getWhereExpression());
});

// Where replaces existing expression
Toolkit::test(function (): void {
	$criteria = Criteria::create();
	$expression1 = Criteria::expr()->eq('name', 'John');
	$expression2 = Criteria::expr()->eq('name', 'Jane');

	$criteria->where($expression1);
	$criteria->where($expression2);

	Assert::same($expression2, $criteria->getWhereExpression());
});

// AndWhere
Toolkit::test(function (): void {
	$criteria = Criteria::create();
	$expression1 = Criteria::expr()->eq('name', 'John');
	$expression2 = Criteria::expr()->gt('age', 18);

	$criteria->where($expression1)->andWhere($expression2);

	$whereExpression = $criteria->getWhereExpression();
	Assert::type(CompositeExpression::class, $whereExpression);
	Assert::same(CompositeExpression::TYPE_AND, $whereExpression->getType());
	Assert::count(2, $whereExpression->getExpressions());
});

// AndWhere on empty criteria
Toolkit::test(function (): void {
	$criteria = Criteria::create();
	$expression = Criteria::expr()->eq('name', 'John');

	$criteria->andWhere($expression);

	Assert::same($expression, $criteria->getWhereExpression());
});

// OrWhere
Toolkit::test(function (): void {
	$criteria = Criteria::create();
	$expression1 = Criteria::expr()->eq('status', 'active');
	$expression2 = Criteria::expr()->eq('status', 'pending');

	$criteria->where($expression1)->orWhere($expression2);

	$whereExpression = $criteria->getWhereExpression();
	Assert::type(CompositeExpression::class, $whereExpression);
	Assert::same(CompositeExpression::TYPE_OR, $whereExpression->getType());
	Assert::count(2, $whereExpression->getExpressions());
});

// OrWhere on empty criteria
Toolkit::test(function (): void {
	$criteria = Criteria::create();
	$expression = Criteria::expr()->eq('name', 'John');

	$criteria->orWhere($expression);

	Assert::same($expression, $criteria->getWhereExpression());
});

// OrderBy single ordering
Toolkit::test(function (): void {
	$criteria = Criteria::create();
	$ordering = Ordering::asc('name');

	$result = $criteria->orderBy($ordering);

	Assert::same($criteria, $result);
	Assert::count(1, $criteria->getOrderings());
	Assert::same($ordering, $criteria->getOrderings()[0]);
});

// OrderBy multiple orderings
Toolkit::test(function (): void {
	$criteria = Criteria::create();
	$orderings = [
		Ordering::asc('lastName'),
		Ordering::desc('firstName'),
	];

	$criteria->orderBy($orderings);

	Assert::count(2, $criteria->getOrderings());
	Assert::same($orderings, $criteria->getOrderings());
});

// AddOrderBy
Toolkit::test(function (): void {
	$criteria = Criteria::create();
	$ordering1 = Ordering::asc('name');
	$ordering2 = Ordering::desc('createdAt');

	$criteria->orderBy($ordering1)->addOrderBy($ordering2);

	Assert::count(2, $criteria->getOrderings());
	Assert::same($ordering1, $criteria->getOrderings()[0]);
	Assert::same($ordering2, $criteria->getOrderings()[1]);
});

// SetFirstResult
Toolkit::test(function (): void {
	$criteria = Criteria::create();

	$result = $criteria->setFirstResult(10);

	Assert::same($criteria, $result);
	Assert::same(10, $criteria->getFirstResult());
});

// SetFirstResult null
Toolkit::test(function (): void {
	$criteria = Criteria::create()
		->setFirstResult(10)
		->setFirstResult(null);

	Assert::null($criteria->getFirstResult());
});

// SetMaxResults
Toolkit::test(function (): void {
	$criteria = Criteria::create();

	$result = $criteria->setMaxResults(25);

	Assert::same($criteria, $result);
	Assert::same(25, $criteria->getMaxResults());
});

// SetMaxResults null
Toolkit::test(function (): void {
	$criteria = Criteria::create()
		->setMaxResults(25)
		->setMaxResults(null);

	Assert::null($criteria->getMaxResults());
});

// Fluent interface
Toolkit::test(function (): void {
	$criteria = Criteria::create()
		->where(Criteria::expr()->eq('status', 'active'))
		->andWhere(Criteria::expr()->gt('age', 18))
		->orderBy(Ordering::desc('createdAt'))
		->addOrderBy(Ordering::asc('name'))
		->setFirstResult(0)
		->setMaxResults(10);

	Assert::type(Criteria::class, $criteria);
	Assert::type(CompositeExpression::class, $criteria->getWhereExpression());
	Assert::count(2, $criteria->getOrderings());
	Assert::same(0, $criteria->getFirstResult());
	Assert::same(10, $criteria->getMaxResults());
});

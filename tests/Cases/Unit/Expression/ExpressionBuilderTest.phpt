<?php declare(strict_types = 1);

namespace Tests\Cases\Unit\Expression;

use Contributte\Criteria\Expression\Comparison;
use Contributte\Criteria\Expression\CompositeExpression;
use Contributte\Criteria\Expression\ExpressionBuilder;
use Contributte\Tester\Toolkit;
use Tester\Assert;

require_once __DIR__ . '/../../../bootstrap.php';

// Eq
Toolkit::test(function (): void {
	$builder = new ExpressionBuilder();
	$comparison = $builder->eq('name', 'John');

	Assert::type(Comparison::class, $comparison);
	Assert::same('name', $comparison->getField());
	Assert::same(Comparison::EQ, $comparison->getOperator());
	Assert::same('John', $comparison->getValue());
});

// Neq
Toolkit::test(function (): void {
	$builder = new ExpressionBuilder();
	$comparison = $builder->neq('status', 'deleted');

	Assert::type(Comparison::class, $comparison);
	Assert::same('status', $comparison->getField());
	Assert::same(Comparison::NEQ, $comparison->getOperator());
	Assert::same('deleted', $comparison->getValue());
});

// Lt
Toolkit::test(function (): void {
	$builder = new ExpressionBuilder();
	$comparison = $builder->lt('age', 18);

	Assert::type(Comparison::class, $comparison);
	Assert::same('age', $comparison->getField());
	Assert::same(Comparison::LT, $comparison->getOperator());
	Assert::same(18, $comparison->getValue());
});

// Lte
Toolkit::test(function (): void {
	$builder = new ExpressionBuilder();
	$comparison = $builder->lte('price', 100.00);

	Assert::type(Comparison::class, $comparison);
	Assert::same('price', $comparison->getField());
	Assert::same(Comparison::LTE, $comparison->getOperator());
	Assert::same(100.00, $comparison->getValue());
});

// Gt
Toolkit::test(function (): void {
	$builder = new ExpressionBuilder();
	$comparison = $builder->gt('rating', 4);

	Assert::type(Comparison::class, $comparison);
	Assert::same('rating', $comparison->getField());
	Assert::same(Comparison::GT, $comparison->getOperator());
	Assert::same(4, $comparison->getValue());
});

// Gte
Toolkit::test(function (): void {
	$builder = new ExpressionBuilder();
	$comparison = $builder->gte('quantity', 10);

	Assert::type(Comparison::class, $comparison);
	Assert::same('quantity', $comparison->getField());
	Assert::same(Comparison::GTE, $comparison->getOperator());
	Assert::same(10, $comparison->getValue());
});

// In
Toolkit::test(function (): void {
	$builder = new ExpressionBuilder();
	$values = ['active', 'pending', 'approved'];
	$comparison = $builder->in('status', $values);

	Assert::type(Comparison::class, $comparison);
	Assert::same('status', $comparison->getField());
	Assert::same(Comparison::IN, $comparison->getOperator());
	Assert::same($values, $comparison->getValue());
});

// NotIn
Toolkit::test(function (): void {
	$builder = new ExpressionBuilder();
	$values = ['deleted', 'banned'];
	$comparison = $builder->notIn('status', $values);

	Assert::type(Comparison::class, $comparison);
	Assert::same('status', $comparison->getField());
	Assert::same(Comparison::NIN, $comparison->getOperator());
	Assert::same($values, $comparison->getValue());
});

// Contains
Toolkit::test(function (): void {
	$builder = new ExpressionBuilder();
	$comparison = $builder->contains('description', 'important');

	Assert::type(Comparison::class, $comparison);
	Assert::same('description', $comparison->getField());
	Assert::same(Comparison::CONTAINS, $comparison->getOperator());
	Assert::same('important', $comparison->getValue());
});

// StartsWith
Toolkit::test(function (): void {
	$builder = new ExpressionBuilder();
	$comparison = $builder->startsWith('email', 'admin@');

	Assert::type(Comparison::class, $comparison);
	Assert::same('email', $comparison->getField());
	Assert::same(Comparison::STARTS_WITH, $comparison->getOperator());
	Assert::same('admin@', $comparison->getValue());
});

// EndsWith
Toolkit::test(function (): void {
	$builder = new ExpressionBuilder();
	$comparison = $builder->endsWith('email', '@example.com');

	Assert::type(Comparison::class, $comparison);
	Assert::same('email', $comparison->getField());
	Assert::same(Comparison::ENDS_WITH, $comparison->getOperator());
	Assert::same('@example.com', $comparison->getValue());
});

// IsNull
Toolkit::test(function (): void {
	$builder = new ExpressionBuilder();
	$comparison = $builder->isNull('deletedAt');

	Assert::type(Comparison::class, $comparison);
	Assert::same('deletedAt', $comparison->getField());
	Assert::same(Comparison::IS_NULL, $comparison->getOperator());
	Assert::null($comparison->getValue());
});

// IsNotNull
Toolkit::test(function (): void {
	$builder = new ExpressionBuilder();
	$comparison = $builder->isNotNull('verifiedAt');

	Assert::type(Comparison::class, $comparison);
	Assert::same('verifiedAt', $comparison->getField());
	Assert::same(Comparison::IS_NOT_NULL, $comparison->getOperator());
	Assert::null($comparison->getValue());
});

// AndX
Toolkit::test(function (): void {
	$builder = new ExpressionBuilder();
	$expr1 = $builder->eq('status', 'active');
	$expr2 = $builder->gt('age', 18);
	$expr3 = $builder->isNotNull('email');

	$composite = $builder->andX($expr1, $expr2, $expr3);

	Assert::type(CompositeExpression::class, $composite);
	Assert::same(CompositeExpression::TYPE_AND, $composite->getType());
	Assert::count(3, $composite->getExpressions());
});

// OrX
Toolkit::test(function (): void {
	$builder = new ExpressionBuilder();
	$expr1 = $builder->eq('status', 'active');
	$expr2 = $builder->eq('status', 'pending');

	$composite = $builder->orX($expr1, $expr2);

	Assert::type(CompositeExpression::class, $composite);
	Assert::same(CompositeExpression::TYPE_OR, $composite->getType());
	Assert::count(2, $composite->getExpressions());
});

// Nested composite expressions
Toolkit::test(function (): void {
	$builder = new ExpressionBuilder();
	$condition1 = $builder->andX(
		$builder->eq('status', 'active'),
		$builder->gt('age', 18)
	);
	$condition2 = $builder->eq('role', 'admin');

	$composite = $builder->orX($condition1, $condition2);

	Assert::type(CompositeExpression::class, $composite);
	Assert::same(CompositeExpression::TYPE_OR, $composite->getType());
	Assert::count(2, $composite->getExpressions());

	$firstExpression = $composite->getExpressions()[0];
	Assert::type(CompositeExpression::class, $firstExpression);
	Assert::same(CompositeExpression::TYPE_AND, $firstExpression->getType());
});

// Empty andX
Toolkit::test(function (): void {
	$builder = new ExpressionBuilder();
	$composite = $builder->andX();

	Assert::type(CompositeExpression::class, $composite);
	Assert::count(0, $composite->getExpressions());
});

// Empty orX
Toolkit::test(function (): void {
	$builder = new ExpressionBuilder();
	$composite = $builder->orX();

	Assert::type(CompositeExpression::class, $composite);
	Assert::count(0, $composite->getExpressions());
});

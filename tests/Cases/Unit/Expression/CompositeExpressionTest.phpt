<?php declare(strict_types = 1);

namespace Tests\Cases\Unit\Expression;

use Contributte\Criteria\Expression\Comparison;
use Contributte\Criteria\Expression\CompositeExpression;
use Contributte\Criteria\Expression\Expression;
use Contributte\Tester\Toolkit;
use InvalidArgumentException;
use Tester\Assert;

require_once __DIR__ . '/../../../bootstrap.php';

// Implements Expression
Toolkit::test(function (): void {
	$composite = new CompositeExpression(CompositeExpression::TYPE_AND, []);

	Assert::type(Expression::class, $composite);
});

// And type
Toolkit::test(function (): void {
	$composite = new CompositeExpression(CompositeExpression::TYPE_AND, []);

	Assert::same(CompositeExpression::TYPE_AND, $composite->getType());
});

// Or type
Toolkit::test(function (): void {
	$composite = new CompositeExpression(CompositeExpression::TYPE_OR, []);

	Assert::same(CompositeExpression::TYPE_OR, $composite->getType());
});

// Invalid type throws exception
Toolkit::test(function (): void {
	Assert::exception(function (): void {
		new CompositeExpression('INVALID', []);
	}, InvalidArgumentException::class, 'Invalid composite expression type: INVALID');
});

// GetExpressions
Toolkit::test(function (): void {
	$expr1 = new Comparison('name', Comparison::EQ, 'John');
	$expr2 = new Comparison('age', Comparison::GT, 18);

	$composite = new CompositeExpression(CompositeExpression::TYPE_AND, [$expr1, $expr2]);

	$expressions = $composite->getExpressions();
	Assert::count(2, $expressions);
	Assert::same($expr1, $expressions[0]);
	Assert::same($expr2, $expressions[1]);
});

// Empty expressions
Toolkit::test(function (): void {
	$composite = new CompositeExpression(CompositeExpression::TYPE_OR, []);

	Assert::same([], $composite->getExpressions());
});

// Nested composite expressions
Toolkit::test(function (): void {
	$inner = new CompositeExpression(
		CompositeExpression::TYPE_AND,
		[
			new Comparison('status', Comparison::EQ, 'active'),
			new Comparison('verified', Comparison::EQ, true),
		]
	);

	$outer = new CompositeExpression(
		CompositeExpression::TYPE_OR,
		[
			$inner,
			new Comparison('role', Comparison::EQ, 'admin'),
		]
	);

	Assert::same(CompositeExpression::TYPE_OR, $outer->getType());
	Assert::count(2, $outer->getExpressions());

	$firstExpr = $outer->getExpressions()[0];
	Assert::type(CompositeExpression::class, $firstExpr);
	Assert::same(CompositeExpression::TYPE_AND, $firstExpr->getType());
});

// Invalid expression throws exception
Toolkit::test(function (): void {
	Assert::exception(function (): void {
		new CompositeExpression(CompositeExpression::TYPE_AND, ['not an expression']);
	}, InvalidArgumentException::class, 'All expressions must implement Expression interface.');
});

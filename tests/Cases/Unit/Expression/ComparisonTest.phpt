<?php declare(strict_types = 1);

namespace Tests\Cases\Unit\Expression;

use Contributte\Criteria\Expression\Comparison;
use Contributte\Criteria\Expression\Expression;
use Contributte\Tester\Toolkit;
use Tester\Assert;

require_once __DIR__ . '/../../../bootstrap.php';

// Implements Expression
Toolkit::test(function (): void {
	$comparison = new Comparison('field', Comparison::EQ, 'value');

	Assert::type(Expression::class, $comparison);
});

// GetField
Toolkit::test(function (): void {
	$comparison = new Comparison('name', Comparison::EQ, 'John');

	Assert::same('name', $comparison->getField());
});

// GetOperator
Toolkit::test(function (): void {
	$comparison = new Comparison('name', Comparison::NEQ, 'John');

	Assert::same(Comparison::NEQ, $comparison->getOperator());
});

// GetValue
Toolkit::test(function (): void {
	$comparison = new Comparison('age', Comparison::GT, 18);

	Assert::same(18, $comparison->getValue());
});

// GetValue with null
Toolkit::test(function (): void {
	$comparison = new Comparison('deletedAt', Comparison::IS_NULL, null);

	Assert::null($comparison->getValue());
});

// GetValue with array
Toolkit::test(function (): void {
	$values = ['active', 'pending'];
	$comparison = new Comparison('status', Comparison::IN, $values);

	Assert::same($values, $comparison->getValue());
});

// All operators
Toolkit::test(function (): void {
	$operators = [
		Comparison::EQ,
		Comparison::NEQ,
		Comparison::LT,
		Comparison::LTE,
		Comparison::GT,
		Comparison::GTE,
		Comparison::IN,
		Comparison::NIN,
		Comparison::CONTAINS,
		Comparison::STARTS_WITH,
		Comparison::ENDS_WITH,
		Comparison::IS_NULL,
		Comparison::IS_NOT_NULL,
	];

	foreach ($operators as $operator) {
		$comparison = new Comparison('field', $operator, 'value');
		Assert::same($operator, $comparison->getOperator());
	}
});

// With relation traversal
Toolkit::test(function (): void {
	$comparison = new Comparison('author->name', Comparison::EQ, 'Jon Snow');

	Assert::same('author->name', $comparison->getField());
});

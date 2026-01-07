<?php declare(strict_types = 1);

namespace Tests\Cases\Unit;

use Contributte\Criteria\Ordering;
use Contributte\Tester\Toolkit;
use InvalidArgumentException;
use Tester\Assert;

require_once __DIR__ . '/../../bootstrap.php';

// Constructor with ASC
Toolkit::test(function (): void {
	$ordering = new Ordering('name', Ordering::ASC);

	Assert::same('name', $ordering->getField());
	Assert::same(Ordering::ASC, $ordering->getDirection());
});

// Constructor with DESC
Toolkit::test(function (): void {
	$ordering = new Ordering('createdAt', Ordering::DESC);

	Assert::same('createdAt', $ordering->getField());
	Assert::same(Ordering::DESC, $ordering->getDirection());
});

// Default direction is ASC
Toolkit::test(function (): void {
	$ordering = new Ordering('name');

	Assert::same(Ordering::ASC, $ordering->getDirection());
});

// Invalid direction throws exception
Toolkit::test(function (): void {
	Assert::exception(function (): void {
		new Ordering('name', 'INVALID');
	}, InvalidArgumentException::class, 'Invalid ordering direction: INVALID');
});

// Asc factory
Toolkit::test(function (): void {
	$ordering = Ordering::asc('name');

	Assert::same('name', $ordering->getField());
	Assert::same(Ordering::ASC, $ordering->getDirection());
});

// Desc factory
Toolkit::test(function (): void {
	$ordering = Ordering::desc('createdAt');

	Assert::same('createdAt', $ordering->getField());
	Assert::same(Ordering::DESC, $ordering->getDirection());
});

// With relation traversal
Toolkit::test(function (): void {
	$ordering = Ordering::asc('author->lastName');

	Assert::same('author->lastName', $ordering->getField());
});

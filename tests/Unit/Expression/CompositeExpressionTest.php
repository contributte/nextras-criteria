<?php

declare(strict_types=1);

namespace Contributte\Criteria\Tests\Unit\Expression;

use Contributte\Criteria\Expression\Comparison;
use Contributte\Criteria\Expression\CompositeExpression;
use Contributte\Criteria\Expression\Expression;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

final class CompositeExpressionTest extends TestCase
{
	public function testImplementsExpression(): void
	{
		$composite = new CompositeExpression(CompositeExpression::TYPE_AND, []);

		$this->assertInstanceOf(Expression::class, $composite);
	}

	public function testAndType(): void
	{
		$composite = new CompositeExpression(CompositeExpression::TYPE_AND, []);

		$this->assertSame(CompositeExpression::TYPE_AND, $composite->getType());
	}

	public function testOrType(): void
	{
		$composite = new CompositeExpression(CompositeExpression::TYPE_OR, []);

		$this->assertSame(CompositeExpression::TYPE_OR, $composite->getType());
	}

	public function testInvalidTypeThrowsException(): void
	{
		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionMessage('Invalid composite expression type: INVALID');

		new CompositeExpression('INVALID', []);
	}

	public function testGetExpressions(): void
	{
		$expr1 = new Comparison('name', Comparison::EQ, 'John');
		$expr2 = new Comparison('age', Comparison::GT, 18);

		$composite = new CompositeExpression(CompositeExpression::TYPE_AND, [$expr1, $expr2]);

		$expressions = $composite->getExpressions();
		$this->assertCount(2, $expressions);
		$this->assertSame($expr1, $expressions[0]);
		$this->assertSame($expr2, $expressions[1]);
	}

	public function testEmptyExpressions(): void
	{
		$composite = new CompositeExpression(CompositeExpression::TYPE_OR, []);

		$this->assertSame([], $composite->getExpressions());
	}

	public function testNestedCompositeExpressions(): void
	{
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

		$this->assertSame(CompositeExpression::TYPE_OR, $outer->getType());
		$this->assertCount(2, $outer->getExpressions());

		$firstExpr = $outer->getExpressions()[0];
		$this->assertInstanceOf(CompositeExpression::class, $firstExpr);
		$this->assertSame(CompositeExpression::TYPE_AND, $firstExpr->getType());
	}

	public function testInvalidExpressionThrowsException(): void
	{
		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionMessage('All expressions must implement Expression interface');

		new CompositeExpression(CompositeExpression::TYPE_AND, ['not an expression']);
	}
}

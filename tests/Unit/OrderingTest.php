<?php

declare(strict_types=1);

namespace Contributte\Criteria\Tests\Unit;

use Contributte\Criteria\Ordering;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

final class OrderingTest extends TestCase
{
	public function testConstructorWithAsc(): void
	{
		$ordering = new Ordering('name', Ordering::ASC);

		$this->assertSame('name', $ordering->getField());
		$this->assertSame(Ordering::ASC, $ordering->getDirection());
	}

	public function testConstructorWithDesc(): void
	{
		$ordering = new Ordering('createdAt', Ordering::DESC);

		$this->assertSame('createdAt', $ordering->getField());
		$this->assertSame(Ordering::DESC, $ordering->getDirection());
	}

	public function testDefaultDirectionIsAsc(): void
	{
		$ordering = new Ordering('name');

		$this->assertSame(Ordering::ASC, $ordering->getDirection());
	}

	public function testInvalidDirectionThrowsException(): void
	{
		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionMessage('Invalid ordering direction: INVALID');

		new Ordering('name', 'INVALID');
	}

	public function testAscFactory(): void
	{
		$ordering = Ordering::asc('name');

		$this->assertSame('name', $ordering->getField());
		$this->assertSame(Ordering::ASC, $ordering->getDirection());
	}

	public function testDescFactory(): void
	{
		$ordering = Ordering::desc('createdAt');

		$this->assertSame('createdAt', $ordering->getField());
		$this->assertSame(Ordering::DESC, $ordering->getDirection());
	}

	public function testWithRelationTraversal(): void
	{
		$ordering = Ordering::asc('author->lastName');

		$this->assertSame('author->lastName', $ordering->getField());
	}
}

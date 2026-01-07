<?php declare(strict_types = 1);

namespace Contributte\Criteria\Expression;

/**
 * Fluent expression builder for creating comparison and composite expressions.
 */
final class ExpressionBuilder
{

	/**
	 * Creates an AND composite expression.
	 */
	public function andX(Expression ...$expressions): CompositeExpression
	{
		return new CompositeExpression(CompositeExpression::TYPE_AND, $expressions);
	}

	/**
	 * Creates an OR composite expression.
	 */
	public function orX(Expression ...$expressions): CompositeExpression
	{
		return new CompositeExpression(CompositeExpression::TYPE_OR, $expressions);
	}

	/**
	 * Creates an equality comparison.
	 */
	public function eq(string $field, mixed $value): Comparison
	{
		return new Comparison($field, Comparison::EQ, $value);
	}

	/**
	 * Creates a non-equality comparison.
	 */
	public function neq(string $field, mixed $value): Comparison
	{
		return new Comparison($field, Comparison::NEQ, $value);
	}

	/**
	 * Creates a less-than comparison.
	 */
	public function lt(string $field, mixed $value): Comparison
	{
		return new Comparison($field, Comparison::LT, $value);
	}

	/**
	 * Creates a less-than-or-equal comparison.
	 */
	public function lte(string $field, mixed $value): Comparison
	{
		return new Comparison($field, Comparison::LTE, $value);
	}

	/**
	 * Creates a greater-than comparison.
	 */
	public function gt(string $field, mixed $value): Comparison
	{
		return new Comparison($field, Comparison::GT, $value);
	}

	/**
	 * Creates a greater-than-or-equal comparison.
	 */
	public function gte(string $field, mixed $value): Comparison
	{
		return new Comparison($field, Comparison::GTE, $value);
	}

	/**
	 * Creates an IN comparison.
	 *
	 * @param array<mixed> $values
	 */
	public function in(string $field, array $values): Comparison
	{
		return new Comparison($field, Comparison::IN, $values);
	}

	/**
	 * Creates a NOT IN comparison.
	 *
	 * @param array<mixed> $values
	 */
	public function notIn(string $field, array $values): Comparison
	{
		return new Comparison($field, Comparison::NIN, $values);
	}

	/**
	 * Creates a CONTAINS (LIKE %value%) comparison.
	 */
	public function contains(string $field, string $value): Comparison
	{
		return new Comparison($field, Comparison::CONTAINS, $value);
	}

	/**
	 * Creates a STARTS WITH (LIKE value%) comparison.
	 */
	public function startsWith(string $field, string $value): Comparison
	{
		return new Comparison($field, Comparison::STARTS_WITH, $value);
	}

	/**
	 * Creates an ENDS WITH (LIKE %value) comparison.
	 */
	public function endsWith(string $field, string $value): Comparison
	{
		return new Comparison($field, Comparison::ENDS_WITH, $value);
	}

	/**
	 * Creates an IS NULL comparison.
	 */
	public function isNull(string $field): Comparison
	{
		return new Comparison($field, Comparison::IS_NULL, null);
	}

	/**
	 * Creates an IS NOT NULL comparison.
	 */
	public function isNotNull(string $field): Comparison
	{
		return new Comparison($field, Comparison::IS_NOT_NULL, null);
	}

}

<?php declare(strict_types = 1);

namespace Contributte\Criteria\Expression;

/**
 * Comparison expression for field comparisons.
 */
final class Comparison implements Expression
{

	public const EQ = '=';
	public const NEQ = '!=';
	public const LT = '<';
	public const LTE = '<=';
	public const GT = '>';
	public const GTE = '>=';
	public const IN = 'IN';
	public const NIN = 'NOT IN';
	public const CONTAINS = 'CONTAINS';
	public const STARTS_WITH = 'STARTS_WITH';
	public const ENDS_WITH = 'ENDS_WITH';
	public const IS_NULL = 'IS NULL';
	public const IS_NOT_NULL = 'IS NOT NULL';

	public function __construct(
		private readonly string $field,
		private readonly string $operator,
		private readonly mixed $value,
	)
	{
	}

	public function getField(): string
	{
		return $this->field;
	}

	public function getOperator(): string
	{
		return $this->operator;
	}

	public function getValue(): mixed
	{
		return $this->value;
	}

}

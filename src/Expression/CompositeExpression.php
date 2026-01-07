<?php declare(strict_types = 1);

namespace Contributte\Criteria\Expression;

use InvalidArgumentException;

/**
 * Composite expression for combining expressions with AND/OR.
 */
final class CompositeExpression implements Expression
{

	public const TYPE_AND = 'AND';
	public const TYPE_OR = 'OR';

	/** @var Expression[] */
	private array $expressions = [];

	/**
	 * @param mixed[] $expressions
	 */
	public function __construct(
		private readonly string $type,
		array $expressions,
	)
	{
		if ($type !== self::TYPE_AND && $type !== self::TYPE_OR) {
			throw new InvalidArgumentException(sprintf('Invalid composite expression type: %s', $type));
		}

		foreach ($expressions as $expression) {
			if (!$expression instanceof Expression) {
				throw new InvalidArgumentException('All expressions must implement Expression interface.');
			}

			$this->expressions[] = $expression;
		}
	}

	public function getType(): string
	{
		return $this->type;
	}

	/**
	 * @return Expression[]
	 */
	public function getExpressions(): array
	{
		return $this->expressions;
	}

}

<?php declare(strict_types = 1);

namespace Contributte\Criteria;

use Contributte\Criteria\Expression\CompositeExpression;
use Contributte\Criteria\Expression\Expression;
use Contributte\Criteria\Expression\ExpressionBuilder;

/**
 * Criteria for filtering collections with expressions, ordering, and pagination.
 *
 * Inspired by Doctrine Criteria API, designed for Nextras ORM.
 */
final class Criteria
{

	private static ?ExpressionBuilder $expressionBuilder = null;

	private ?Expression $expression = null;

	/** @var Ordering[] */
	private array $orderings = [];

	private ?int $firstResult = null;

	private ?int $maxResults = null;

	/**
	 * Creates a new Criteria instance.
	 */
	public static function create(): self
	{
		return new self();
	}

	/**
	 * Returns the expression builder for creating expressions.
	 */
	public static function expr(): ExpressionBuilder
	{
		if (self::$expressionBuilder === null) {
			self::$expressionBuilder = new ExpressionBuilder();
		}

		return self::$expressionBuilder;
	}

	/**
	 * Sets the filtering expression (replaces any existing expression).
	 */
	public function where(Expression $expression): self
	{
		$this->expression = $expression;

		return $this;
	}

	/**
	 * Adds an AND condition to the existing expression.
	 */
	public function andWhere(Expression $expression): self
	{
		if ($this->expression === null) {
			return $this->where($expression);
		}

		$this->expression = new CompositeExpression(
			CompositeExpression::TYPE_AND,
			[$this->expression, $expression]
		);

		return $this;
	}

	/**
	 * Adds an OR condition to the existing expression.
	 */
	public function orWhere(Expression $expression): self
	{
		if ($this->expression === null) {
			return $this->where($expression);
		}

		$this->expression = new CompositeExpression(
			CompositeExpression::TYPE_OR,
			[$this->expression, $expression]
		);

		return $this;
	}

	/**
	 * Returns the current filtering expression.
	 */
	public function getWhereExpression(): ?Expression
	{
		return $this->expression;
	}

	/**
	 * Sets the ordering for results.
	 *
	 * @param Ordering|Ordering[] $orderings
	 */
	public function orderBy(Ordering|array $orderings): self
	{
		$this->orderings = is_array($orderings) ? $orderings : [$orderings];

		return $this;
	}

	/**
	 * Adds an additional ordering.
	 */
	public function addOrderBy(Ordering $ordering): self
	{
		$this->orderings[] = $ordering;

		return $this;
	}

	/**
	 * Returns the orderings.
	 *
	 * @return Ordering[]
	 */
	public function getOrderings(): array
	{
		return $this->orderings;
	}

	/**
	 * Sets the first result offset (for pagination).
	 */
	public function setFirstResult(?int $firstResult): self
	{
		$this->firstResult = $firstResult;

		return $this;
	}

	/**
	 * Returns the first result offset.
	 */
	public function getFirstResult(): ?int
	{
		return $this->firstResult;
	}

	/**
	 * Sets the maximum number of results (for pagination).
	 */
	public function setMaxResults(?int $maxResults): self
	{
		$this->maxResults = $maxResults;

		return $this;
	}

	/**
	 * Returns the maximum number of results.
	 */
	public function getMaxResults(): ?int
	{
		return $this->maxResults;
	}

}

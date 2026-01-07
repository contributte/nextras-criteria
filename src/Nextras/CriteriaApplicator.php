<?php declare(strict_types = 1);

namespace Contributte\Criteria\Nextras;

use Contributte\Criteria\Criteria;
use Contributte\Criteria\Expression\Comparison;
use Contributte\Criteria\Expression\CompositeExpression;
use Contributte\Criteria\Expression\Expression;
use Contributte\Criteria\Ordering;
use InvalidArgumentException;
use Nextras\Orm\Collection\Expression\LikeExpression;
use Nextras\Orm\Collection\ICollection;
use Nextras\Orm\Entity\IEntity;

/**
 * Applies Criteria to Nextras ORM ICollection.
 *
 * @template TEntity of IEntity
 */
final class CriteriaApplicator
{

	/**
	 * Applies criteria to a collection and returns the filtered collection.
	 *
	 * @template T of IEntity
	 * @param ICollection<T> $collection
	 * @return ICollection<T>
	 */
	public function apply(ICollection $collection, Criteria $criteria): ICollection
	{
		$expression = $criteria->getWhereExpression();

		if ($expression !== null) {
			$conditions = $this->convertExpression($expression);
			$collection = $collection->findBy($conditions);
		}

		foreach ($criteria->getOrderings() as $ordering) {
			$collection = $collection->orderBy(
				$ordering->getField(),
				$ordering->getDirection() === Ordering::ASC
					? ICollection::ASC
					: ICollection::DESC
			);
		}

		$firstResult = $criteria->getFirstResult();
		$maxResults = $criteria->getMaxResults();

		if ($maxResults !== null || $firstResult !== null) {
			$collection = $collection->limitBy(
				$maxResults ?? PHP_INT_MAX,
				$firstResult ?? 0
			);
		}

		return $collection;
	}

	/**
	 * Converts an expression to Nextras findBy conditions.
	 *
	 * @return array<mixed>
	 */
	private function convertExpression(Expression $expression): array
	{
		if ($expression instanceof Comparison) {
			return $this->convertComparison($expression);
		}

		if ($expression instanceof CompositeExpression) {
			return $this->convertCompositeExpression($expression);
		}

		throw new InvalidArgumentException(sprintf(
			'Unsupported expression type: %s',
			$expression::class
		));
	}

	/**
	 * Converts a comparison expression to Nextras findBy conditions.
	 *
	 * @return array<mixed>
	 */
	private function convertComparison(Comparison $comparison): array
	{
		$field = $comparison->getField();
		$operator = $comparison->getOperator();
		$value = $comparison->getValue();

		return match ($operator) {
			Comparison::EQ => [$field => $value],
			Comparison::NEQ => [$field . '!=' => $value],
			Comparison::LT => [$field . '<' => $value],
			Comparison::LTE => [$field . '<=' => $value],
			Comparison::GT => [$field . '>' => $value],
			Comparison::GTE => [$field . '>=' => $value],
			Comparison::IN => [$field => $value],
			Comparison::NIN => [$field . '!=' => $value],
			Comparison::CONTAINS => [$field . '~' => LikeExpression::contains($this->assertString($value))],
			Comparison::STARTS_WITH => [$field . '~' => LikeExpression::startsWith($this->assertString($value))],
			Comparison::ENDS_WITH => [$field . '~' => LikeExpression::endsWith($this->assertString($value))],
			Comparison::IS_NULL => [$field => null],
			Comparison::IS_NOT_NULL => [$field . '!=' => null],
			default => throw new InvalidArgumentException(sprintf(
				'Unsupported comparison operator: %s',
				$operator
			)),
		};
	}

	private function assertString(mixed $value): string
	{
		if (!is_string($value)) {
			throw new InvalidArgumentException('LIKE expressions require a string value.');
		}

		return $value;
	}

	/**
	 * Converts a composite expression to Nextras findBy conditions.
	 *
	 * @return array<mixed>
	 */
	private function convertCompositeExpression(CompositeExpression $expression): array
	{
		$expressions = $expression->getExpressions();

		if (count($expressions) === 0) {
			return [];
		}

		$conditions = [];

		foreach ($expressions as $expr) {
			$converted = $this->convertExpression($expr);
			$conditions = array_merge($conditions, $converted);
		}

		if ($expression->getType() === CompositeExpression::TYPE_OR) {
			return [ICollection::OR, ...$this->convertExpressionsForOr($expressions)];
		}

		return $conditions;
	}

	/**
	 * Converts expressions for OR condition (each expression becomes a separate array).
	 *
	 * @param Expression[] $expressions
	 * @return array<mixed>
	 */
	private function convertExpressionsForOr(array $expressions): array
	{
		$result = [];

		foreach ($expressions as $expr) {
			$result[] = $this->convertExpression($expr);
		}

		return $result;
	}

}

<?php declare(strict_types = 1);

namespace Contributte\Criteria;

use InvalidArgumentException;

/**
 * Represents an ordering directive for sorting query results.
 */
final class Ordering
{

	public const ASC = 'ASC';
	public const DESC = 'DESC';

	public function __construct(
		private readonly string $field,
		private readonly string $direction = self::ASC,
	)
	{
		if ($direction !== self::ASC && $direction !== self::DESC) {
			throw new InvalidArgumentException(sprintf('Invalid ordering direction: %s', $direction));
		}
	}

	/**
	 * Creates an ascending ordering.
	 */
	public static function asc(string $field): self
	{
		return new self($field, self::ASC);
	}

	/**
	 * Creates a descending ordering.
	 */
	public static function desc(string $field): self
	{
		return new self($field, self::DESC);
	}

	public function getField(): string
	{
		return $this->field;
	}

	public function getDirection(): string
	{
		return $this->direction;
	}

}

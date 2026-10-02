<?php declare(strict_types = 1);

namespace Tests\Cases\Unit\Nextras;

use Contributte\Criteria\Criteria;
use Contributte\Criteria\Expression\Comparison;
use Contributte\Criteria\Nextras\CriteriaApplicator;
use Contributte\Criteria\Ordering;
use Contributte\Tester\Toolkit;
use InvalidArgumentException;
use Nextras\Orm\Collection\ICollection;
use Tester\Assert;
use Tests\Fixtures\Model\ModelFactory;
use Tests\Fixtures\Model\User;

require_once __DIR__ . '/../../../bootstrap.php';

/**
 * @param ICollection<User> $collection
 * @return list<string>
 */
function names(ICollection $collection): array
{
	$names = [];

	foreach ($collection as $user) {
		$names[] = $user->name;
	}

	return $names;
}

/**
 * @return list<string>
 */
function apply(Criteria $criteria): array
{
	$repository = ModelFactory::createUsersRepository();

	return names((new CriteriaApplicator())->apply($repository->findAll()->orderBy('id'), $criteria));
}

// Empty criteria
Toolkit::test(function (): void {
	Assert::same(['John', 'Jane', 'Jack', 'Jill', 'Joe'], apply(Criteria::create()));
});

// Comparison operators
Toolkit::test(function (): void {
	$expr = Criteria::expr();

	Assert::same(['John', 'Jane', 'Joe'], apply(Criteria::create()->where($expr->eq('status', 'active'))));
	Assert::same(['Jack', 'Jill'], apply(Criteria::create()->where($expr->neq('status', 'active'))));
	Assert::same(['Jane', 'Jill'], apply(Criteria::create()->where($expr->lt('age', 30))));
	Assert::same(['John', 'Jane', 'Jill'], apply(Criteria::create()->where($expr->lte('age', 30))));
	Assert::same(['Jack', 'Joe'], apply(Criteria::create()->where($expr->gt('age', 30))));
	Assert::same(['John', 'Jack', 'Joe'], apply(Criteria::create()->where($expr->gte('age', 30))));
	Assert::same(['Jack', 'Jill'], apply(Criteria::create()->where($expr->in('status', ['banned', 'pending']))));
	Assert::same(['John', 'Jane', 'Joe'], apply(Criteria::create()->where($expr->notIn('status', ['banned', 'pending']))));
	Assert::same(['Jill'], apply(Criteria::create()->where($expr->contains('email', 'min@'))));
	Assert::same(['Jill'], apply(Criteria::create()->where($expr->startsWith('email', 'admin@'))));
	Assert::same(['Jane', 'Joe'], apply(Criteria::create()->where($expr->endsWith('email', '@example.org'))));
	Assert::same(['Jane', 'Jack', 'Joe'], apply(Criteria::create()->where($expr->isNull('note'))));
	Assert::same(['John', 'Jill'], apply(Criteria::create()->where($expr->isNotNull('note'))));
});

// A AND B
Toolkit::test(function (): void {
	$expr = Criteria::expr();

	$criteria = Criteria::create()
		->where($expr->eq('status', 'active'))
		->andWhere($expr->gte('age', 18));

	Assert::same(['John', 'Joe'], apply($criteria));
});

// AND with the same field and operator used twice
Toolkit::test(function (): void {
	$expr = Criteria::expr();

	$criteria = Criteria::create()
		->where($expr->andX(
			$expr->neq('status', 'banned'),
			$expr->neq('status', 'pending'),
		));

	Assert::same(['John', 'Jane', 'Joe'], apply($criteria));
});

// A OR B
Toolkit::test(function (): void {
	$expr = Criteria::expr();

	$criteria = Criteria::create()
		->where($expr->orX(
			$expr->eq('status', 'banned'),
			$expr->lt('age', 18),
		));

	Assert::same(['Jane', 'Jack'], apply($criteria));
});

// (A AND B) OR C
Toolkit::test(function (): void {
	$expr = Criteria::expr();

	$criteria = Criteria::create()
		->where($expr->eq('status', 'active'))
		->andWhere($expr->gte('age', 18))
		->orWhere($expr->eq('status', 'pending'));

	Assert::same(['John', 'Jill', 'Joe'], apply($criteria));
});

// (A OR B) AND C
Toolkit::test(function (): void {
	$expr = Criteria::expr();

	$criteria = Criteria::create()
		->where($expr->orX(
			$expr->eq('status', 'active'),
			$expr->eq('status', 'banned'),
		))
		->andWhere($expr->gte('age', 40));

	Assert::same(['Jack', 'Joe'], apply($criteria));
});

// Empty composite expression
Toolkit::test(function (): void {
	Assert::same(['John', 'Jane', 'Jack', 'Jill', 'Joe'], apply(Criteria::create()->where(Criteria::expr()->andX())));
	Assert::same(['John', 'Jane', 'Jack', 'Jill', 'Joe'], apply(Criteria::create()->where(Criteria::expr()->orX())));
});

// Ordering
Toolkit::test(function (): void {
	$repository = ModelFactory::createUsersRepository();

	$criteria = Criteria::create()
		->orderBy([
			Ordering::asc('status'),
			Ordering::desc('age'),
		]);

	Assert::same(['Joe', 'John', 'Jane', 'Jack', 'Jill'], names((new CriteriaApplicator())->apply($repository->findAll(), $criteria)));
});

// Pagination
Toolkit::test(function (): void {
	Assert::same(['Jane', 'Jack'], apply(Criteria::create()->setFirstResult(1)->setMaxResults(2)));
	Assert::same(['John', 'Jane'], apply(Criteria::create()->setMaxResults(2)));
	Assert::same(['Jill', 'Joe'], apply(Criteria::create()->setFirstResult(3)));
});

// LIKE requires a string value
Toolkit::test(function (): void {
	Assert::exception(
		fn () => apply(Criteria::create()->where(new Comparison('email', Comparison::CONTAINS, 1))),
		InvalidArgumentException::class,
		'LIKE expressions require a string value.',
	);
});

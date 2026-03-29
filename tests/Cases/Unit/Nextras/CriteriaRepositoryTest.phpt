<?php declare(strict_types = 1);

namespace Tests\Cases\Unit\Nextras;

use Contributte\Criteria\Criteria;
use Contributte\Criteria\Nextras\CriteriaApplicator;
use Contributte\Criteria\Nextras\CriteriaRepository;
use Contributte\Criteria\Ordering;
use Contributte\Tester\Toolkit;
use Nextras\Orm\Collection\ICollection;
use Nextras\Orm\Entity\IEntity;
use Tester\Assert;

require_once __DIR__ . '/../../../bootstrap.php';

/**
 * @implements ICollection<IEntity>
 */
class StubCollection implements ICollection
{

	/** @var array<mixed> */
	public array $findByCalls = [];

	/** @var array<mixed> */
	public array $orderByCalls = [];

	/** @var array<mixed> */
	public array $limitByCalls = [];

	public function findBy(array $conds): ICollection
	{
		$this->findByCalls[] = $conds;

		return $this;
	}

	public function getBy(array $conds): ?IEntity
	{
		return null;
	}

	public function getByChecked(array $conds): IEntity
	{
		throw new \Nextras\Orm\Exception\NoResultException();
	}

	public function getById($id): ?IEntity
	{
		return null;
	}

	public function getByIdChecked($id): IEntity
	{
		throw new \Nextras\Orm\Exception\NoResultException();
	}

	public function orderBy($expression, string $direction = self::ASC): ICollection
	{
		$this->orderByCalls[] = [$expression, $direction];

		return $this;
	}

	public function resetOrderBy(): ICollection
	{
		return $this;
	}

	public function limitBy(int $limit, int|null $offset = null): ICollection
	{
		$this->limitByCalls[] = [$limit, $offset];

		return $this;
	}

	public function fetch(): ?IEntity
	{
		return null;
	}

	public function fetchChecked(): IEntity
	{
		throw new \Nextras\Orm\Exception\NoResultException();
	}

	public function fetchAll(): array
	{
		return [];
	}

	public function fetchPairs(string|null $key = null, string|null $value = null): array
	{
		return [];
	}

	public function getIterator(): \Iterator
	{
		return new \ArrayIterator([]);
	}

	public function toMemoryCollection(): \Nextras\Orm\Collection\MemoryCollection
	{
		throw new \LogicException('Not implemented');
	}

	public function setRelationshipMapper(\Nextras\Orm\Mapper\IRelationshipMapper|null $mapper): ICollection
	{
		return $this;
	}

	public function getRelationshipMapper(): ?\Nextras\Orm\Mapper\IRelationshipMapper
	{
		return null;
	}

	public function setRelationshipParent(IEntity $parent): ICollection
	{
		return $this;
	}

	public function countStored(): int
	{
		return 0;
	}

	public function count(): int
	{
		return 0;
	}

	public function subscribeOnEntityFetch(callable $callback): void
	{
	}

}

class StubRepository
{

	/** @use CriteriaRepository<IEntity> */
	use CriteriaRepository;

	private StubCollection $collection;

	public function __construct()
	{
		$this->collection = new StubCollection();
	}

	/** @return ICollection<IEntity> */
	public function findAll(): ICollection
	{
		return $this->collection;
	}

	public function getCollection(): StubCollection
	{
		return $this->collection;
	}

}

// findByCriteria returns ICollection
Toolkit::test(function (): void {
	$repository = new StubRepository();
	$criteria = Criteria::create();

	$result = $repository->findByCriteria($criteria);

	Assert::type(ICollection::class, $result);
});

// findByCriteria applies where expression
Toolkit::test(function (): void {
	$repository = new StubRepository();
	$criteria = Criteria::create()
		->where(Criteria::expr()->eq('status', 'active'));

	$repository->findByCriteria($criteria);

	Assert::count(1, $repository->getCollection()->findByCalls);
	Assert::same(['status' => 'active'], $repository->getCollection()->findByCalls[0]);
});

// findByCriteria applies ordering
Toolkit::test(function (): void {
	$repository = new StubRepository();
	$criteria = Criteria::create()
		->orderBy(Ordering::desc('createdAt'));

	$repository->findByCriteria($criteria);

	Assert::count(1, $repository->getCollection()->orderByCalls);
	Assert::same('createdAt', $repository->getCollection()->orderByCalls[0][0]);
	Assert::same(ICollection::DESC, $repository->getCollection()->orderByCalls[0][1]);
});

// findByCriteria applies pagination
Toolkit::test(function (): void {
	$repository = new StubRepository();
	$criteria = Criteria::create()
		->setFirstResult(10)
		->setMaxResults(25);

	$repository->findByCriteria($criteria);

	Assert::count(1, $repository->getCollection()->limitByCalls);
	Assert::same(25, $repository->getCollection()->limitByCalls[0][0]);
	Assert::same(10, $repository->getCollection()->limitByCalls[0][1]);
});

// getByCriteria returns null when no entity found
Toolkit::test(function (): void {
	$repository = new StubRepository();
	$criteria = Criteria::create()
		->where(Criteria::expr()->eq('id', 999));

	$result = $repository->getByCriteria($criteria);

	Assert::null($result);
});

// getByCriteriaChecked throws when no entity found
Toolkit::test(function (): void {
	$repository = new StubRepository();
	$criteria = Criteria::create()
		->where(Criteria::expr()->eq('id', 999));

	Assert::exception(
		fn () => $repository->getByCriteriaChecked($criteria),
		\Nextras\Orm\Exception\NoResultException::class,
	);
});

// setCriteriaApplicator allows custom applicator
Toolkit::test(function (): void {
	$repository = new StubRepository();
	$applicator = new CriteriaApplicator();

	$repository->setCriteriaApplicator($applicator);

	$criteria = Criteria::create();
	$result = $repository->findByCriteria($criteria);

	Assert::type(ICollection::class, $result);
});

// findByCriteria with full criteria (where + ordering + pagination)
Toolkit::test(function (): void {
	$repository = new StubRepository();
	$criteria = Criteria::create()
		->where(Criteria::expr()->eq('status', 'active'))
		->orderBy(Ordering::desc('createdAt'))
		->setFirstResult(0)
		->setMaxResults(10);

	$result = $repository->findByCriteria($criteria);

	Assert::type(ICollection::class, $result);
	Assert::count(1, $repository->getCollection()->findByCalls);
	Assert::count(1, $repository->getCollection()->orderByCalls);
	Assert::count(1, $repository->getCollection()->limitByCalls);
});

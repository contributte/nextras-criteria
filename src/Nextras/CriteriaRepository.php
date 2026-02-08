<?php declare(strict_types = 1);

namespace Contributte\Criteria\Nextras;

use Contributte\Criteria\Criteria;
use Nextras\Orm\Collection\ICollection;
use Nextras\Orm\Entity\IEntity;
use Nextras\Orm\Repository\Repository;

/**
 * Trait for Nextras ORM repositories to add criteria-based querying.
 *
 * Use this trait in your repository classes that extend Nextras\Orm\Repository\Repository.
 *
 * @phpstan-require-extends Repository
 * @template TEntity of IEntity
 */
trait CriteriaRepository
{

	private ?CriteriaApplicator $criteriaApplicator = null;

	/**
	 * Sets a custom criteria applicator.
	 */
	public function setCriteriaApplicator(CriteriaApplicator $applicator): void
	{
		$this->criteriaApplicator = $applicator;
	}

	/**
	 * Finds entities matching the given criteria.
	 *
	 * @return ICollection<TEntity>
	 */
	public function findByCriteria(Criteria $criteria): ICollection
	{
		/** @var ICollection<TEntity> $collection */
		$collection = $this->findAll();

		return $this->getCriteriaApplicator()->apply($collection, $criteria);
	}

	/**
	 * Finds a single entity matching the given criteria, or null.
	 *
	 * @return TEntity|null
	 */
	public function getByCriteria(Criteria $criteria): ?IEntity
	{
		return $this->findByCriteria($criteria)->fetch();
	}

	/**
	 * Finds a single entity matching the given criteria, or throws.
	 *
	 * @return TEntity
	 */
	public function getByCriteriaChecked(Criteria $criteria): IEntity
	{
		return $this->findByCriteria($criteria)->fetchChecked();
	}

	/**
	 * Returns the criteria applicator instance.
	 */
	private function getCriteriaApplicator(): CriteriaApplicator
	{
		if ($this->criteriaApplicator === null) {
			$this->criteriaApplicator = new CriteriaApplicator();
		}

		return $this->criteriaApplicator;
	}

}

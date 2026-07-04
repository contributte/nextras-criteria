![](https://heatbadger.vercel.app/github/readme/contributte/nextras-criteria/)

<p align=center>
  <a href="https://github.com/contributte/nextras-criteria/actions"><img src="https://badgen.net/github/checks/contributte/nextras-criteria/master?cache=300"></a>
  <a href="https://coveralls.io/r/contributte/nextras-criteria"><img src="https://badgen.net/coveralls/c/github/contributte/nextras-criteria?cache=300"></a>
  <a href="https://packagist.org/packages/contributte/nextras-criteria"><img src="https://badgen.net/packagist/dm/contributte/nextras-criteria"></a>
  <a href="https://packagist.org/packages/contributte/nextras-criteria"><img src="https://badgen.net/packagist/v/contributte/nextras-criteria"></a>
</p>
<p align=center>
  <a href="https://packagist.org/packages/contributte/nextras-criteria"><img src="https://badgen.net/packagist/php/contributte/nextras-criteria"></a>
  <a href="https://github.com/contributte/nextras-criteria"><img src="https://badgen.net/github/license/contributte/nextras-criteria"></a>
  <a href="https://bit.ly/cttfo"><img src="https://badgen.net/badge/support/forum/yellow"></a>
  <a href="https://contributte.org/partners.html"><img src="https://badgen.net/badge/sponsor/donations/F96854"></a>
</p>

<p align=center>
Website <a href="https://contributte.org">contributte.org</a> | Contact <a href="https://f3l1x.io">f3l1x.io</a> | Twitter <a href="https://twitter.com/contributte">@contributte</a>
</p>

Criteria pattern for [Nextras ORM](https://nextras.org/orm), inspired by [Doctrine Criteria API](https://www.doctrine-project.org/projects/doctrine-collections/en/latest/expressions.html).

## Versions

| State       | Version | Branch   | Nette  | PHP     |
|-------------|---------|----------|--------|---------|
| dev         | `^0.1`  | `master` | `3.2+` | `>=8.2` |
| stable      | `^0.1`  | `master` | `3.2+` | `>=8.2` |

## Installation

To install the latest version of `contributte/nextras-criteria` use [Composer](https://getcomposer.org).

```
composer require contributte/nextras-criteria
```

## Content

- [Usage](#usage)
- [Expression Builder](#expression-builder)
- [Ordering](#ordering)
- [Pagination](#pagination)
- [Examples](#examples)

## Usage

### Basic usage

```php
use Contributte\Criteria\Criteria;
use Contributte\Criteria\Ordering;
use Contributte\Criteria\Nextras\CriteriaApplicator;

// Create criteria
$criteria = Criteria::create()
    ->where(Criteria::expr()->eq('status', 'active'))
    ->andWhere(Criteria::expr()->gt('age', 18))
    ->orderBy(Ordering::desc('createdAt'))
    ->setMaxResults(10);

// Apply to Nextras collection
$applicator = new CriteriaApplicator();
$users = $applicator->apply($orm->users->findAll(), $criteria);
```

### Advanced usage

```php
use Contributte\Criteria\Criteria;
use Contributte\Criteria\Ordering;
use Contributte\Criteria\Nextras\CriteriaApplicator;

class UserRepository
{
    private CriteriaApplicator $applicator;

    public function __construct(
        private UserOrmRepository $ormRepository,
    ) {
        $this->applicator = new CriteriaApplicator();
    }

    public function findActiveAdults(int $page, int $perPage): array
    {
        $criteria = Criteria::create()
            ->where(Criteria::expr()->andX(
                Criteria::expr()->eq('status', 'active'),
                Criteria::expr()->gte('age', 18),
                Criteria::expr()->isNotNull('verifiedAt')
            ))
            ->orderBy([
                Ordering::desc('createdAt'),
                Ordering::asc('lastName'),
            ])
            ->setFirstResult(($page - 1) * $perPage)
            ->setMaxResults($perPage);

        return $this->applicator
            ->apply($this->ormRepository->findAll(), $criteria)
            ->fetchAll();
    }
}
```

## Expression Builder

The `Criteria::expr()` method returns an `ExpressionBuilder` for creating filter expressions.

### Comparison operators

```php
$expr = Criteria::expr();

// Equality
$expr->eq('name', 'John');           // name = 'John'
$expr->neq('status', 'deleted');     // status != 'deleted'

// Comparison
$expr->lt('age', 18);                // age < 18
$expr->lte('price', 100);            // price <= 100
$expr->gt('rating', 4);              // rating > 4
$expr->gte('quantity', 10);          // quantity >= 10

// IN / NOT IN
$expr->in('status', ['active', 'pending']);
$expr->notIn('role', ['banned', 'suspended']);

// LIKE patterns
$expr->contains('description', 'keyword');   // LIKE '%keyword%'
$expr->startsWith('email', 'admin@');        // LIKE 'admin@%'
$expr->endsWith('email', '@example.com');    // LIKE '%@example.com'

// NULL checks
$expr->isNull('deletedAt');
$expr->isNotNull('verifiedAt');
```

### Composite expressions (AND/OR)

```php
$expr = Criteria::expr();

// AND condition
$criteria = Criteria::create()->where(
    $expr->andX(
        $expr->eq('status', 'active'),
        $expr->gt('age', 18),
        $expr->isNotNull('email')
    )
);

// OR condition
$criteria = Criteria::create()->where(
    $expr->orX(
        $expr->eq('role', 'admin'),
        $expr->eq('role', 'moderator')
    )
);

// Nested conditions: (status = 'active' AND age > 18) OR role = 'admin'
$criteria = Criteria::create()->where(
    $expr->orX(
        $expr->andX(
            $expr->eq('status', 'active'),
            $expr->gt('age', 18)
        ),
        $expr->eq('role', 'admin')
    )
);
```

### Fluent where methods

```php
$criteria = Criteria::create()
    ->where(Criteria::expr()->eq('status', 'active'))
    ->andWhere(Criteria::expr()->gt('age', 18))
    ->orWhere(Criteria::expr()->eq('role', 'admin'));
```

### Relationship traversal

Nextras ORM supports filtering by related entity properties using `->` notation:

```php
// Filter books by author's name
$criteria = Criteria::create()
    ->where(Criteria::expr()->eq('author->name', 'Jon Snow'));

// Order by related entity
$criteria = Criteria::create()
    ->orderBy(Ordering::asc('author->lastName'));
```

## Ordering

```php
use Contributte\Criteria\Ordering;

// Single ordering
$criteria = Criteria::create()
    ->orderBy(Ordering::desc('createdAt'));

// Multiple orderings
$criteria = Criteria::create()
    ->orderBy([
        Ordering::asc('lastName'),
        Ordering::asc('firstName'),
    ]);

// Add ordering
$criteria = Criteria::create()
    ->orderBy(Ordering::desc('priority'))
    ->addOrderBy(Ordering::asc('name'));
```

## Pagination

```php
$criteria = Criteria::create()
    ->setFirstResult(20)  // offset
    ->setMaxResults(10);  // limit
```

## Examples

### Reusable criteria

Create reusable criteria specifications:

```php
class ActiveUserCriteria
{
    public static function create(): Criteria
    {
        return Criteria::create()
            ->where(Criteria::expr()->andX(
                Criteria::expr()->eq('status', 'active'),
                Criteria::expr()->isNull('deletedAt')
            ));
    }
}

// Combine criteria
$criteria = ActiveUserCriteria::create()
    ->andWhere(Criteria::expr()->gte('age', 18))
    ->orderBy(Ordering::desc('createdAt'));
```

> [!TIP]
> Take a look at more examples in [contributte/playground](https://github.com/contributte/playground).

## Development

See [how to contribute](https://contributte.org/contributing.html) to this package.

This package is currently maintaining by these authors.

<a href="https://github.com/f3l1x">
  <img width="80" height="80" src="https://avatars2.githubusercontent.com/u/538058?v=3&s=80">
</a>

-----

Consider to [support](https://contributte.org/partners.html) **contributte** development team.
Also thank you for using this package.

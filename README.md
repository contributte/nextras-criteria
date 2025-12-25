# Nextras Criteria

Criteria pattern for [Nextras ORM](https://nextras.org/orm), inspired by [Doctrine Criteria API](https://www.doctrine-project.org/projects/doctrine-collections/en/latest/expressions.html).

## Installation

```bash
composer require contributte/nextras-criteria
```

## Usage

### Basic Example

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

### Expression Builder

The `Criteria::expr()` method returns an `ExpressionBuilder` for creating filter expressions:

#### Comparison Operators

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

#### Composite Expressions (AND/OR)

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

#### Fluent Where Methods

```php
$criteria = Criteria::create()
    ->where(Criteria::expr()->eq('status', 'active'))
    ->andWhere(Criteria::expr()->gt('age', 18))
    ->orWhere(Criteria::expr()->eq('role', 'admin'));
```

### Ordering

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

### Pagination

```php
$criteria = Criteria::create()
    ->setFirstResult(20)  // offset
    ->setMaxResults(10);  // limit
```

### Relationship Traversal

Nextras ORM supports filtering by related entity properties using `->` notation:

```php
// Filter books by author's name
$criteria = Criteria::create()
    ->where(Criteria::expr()->eq('author->name', 'Jon Snow'));

// Order by related entity
$criteria = Criteria::create()
    ->orderBy(Ordering::asc('author->lastName'));
```

### Complete Example

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

    public function searchByEmail(string $domain): array
    {
        $criteria = Criteria::create()
            ->where(Criteria::expr()->endsWith('email', '@' . $domain))
            ->orderBy(Ordering::asc('email'));

        return $this->applicator
            ->apply($this->ormRepository->findAll(), $criteria)
            ->fetchAll();
    }
}
```

### Reusable Criteria

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

class AdultCriteria
{
    public static function create(): Criteria
    {
        return Criteria::create()
            ->where(Criteria::expr()->gte('age', 18));
    }
}

// Combine criteria
$criteria = ActiveUserCriteria::create()
    ->andWhere(Criteria::expr()->gte('age', 18))
    ->orderBy(Ordering::desc('createdAt'));
```

## API Reference

### Criteria

| Method | Description |
|--------|-------------|
| `Criteria::create()` | Creates a new Criteria instance |
| `Criteria::expr()` | Returns the ExpressionBuilder singleton |
| `where(Expression)` | Sets the filtering expression |
| `andWhere(Expression)` | Adds an AND condition |
| `orWhere(Expression)` | Adds an OR condition |
| `getWhereExpression()` | Returns the current expression |
| `orderBy(Ordering\|Ordering[])` | Sets ordering |
| `addOrderBy(Ordering)` | Adds additional ordering |
| `getOrderings()` | Returns orderings |
| `setFirstResult(?int)` | Sets offset |
| `getFirstResult()` | Returns offset |
| `setMaxResults(?int)` | Sets limit |
| `getMaxResults()` | Returns limit |

### ExpressionBuilder

| Method | Operator |
|--------|----------|
| `eq($field, $value)` | `=` |
| `neq($field, $value)` | `!=` |
| `lt($field, $value)` | `<` |
| `lte($field, $value)` | `<=` |
| `gt($field, $value)` | `>` |
| `gte($field, $value)` | `>=` |
| `in($field, $values)` | `IN` |
| `notIn($field, $values)` | `NOT IN` |
| `contains($field, $value)` | `LIKE %value%` |
| `startsWith($field, $value)` | `LIKE value%` |
| `endsWith($field, $value)` | `LIKE %value` |
| `isNull($field)` | `IS NULL` |
| `isNotNull($field)` | `IS NOT NULL` |
| `andX(...$expressions)` | `AND` composite |
| `orX(...$expressions)` | `OR` composite |

### Ordering

| Method | Description |
|--------|-------------|
| `Ordering::asc($field)` | Ascending order |
| `Ordering::desc($field)` | Descending order |
| `new Ordering($field, $direction)` | Custom direction |

## Testing

```bash
composer test
```

## License

BSD-3-Clause

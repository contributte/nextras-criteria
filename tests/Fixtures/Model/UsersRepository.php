<?php declare(strict_types = 1);

namespace Tests\Fixtures\Model;

use Nextras\Orm\Repository\Repository;

/**
 * @extends Repository<User>
 */
class UsersRepository extends Repository
{

	/**
	 * @return list<class-string<User>>
	 */
	public static function getEntityClassNames(): array
	{
		return [User::class];
	}

}

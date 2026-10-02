<?php declare(strict_types = 1);

namespace Tests\Fixtures\Model;

use Nette\Caching\Cache;
use Nette\Caching\Storages\MemoryStorage;
use Nextras\Orm\Model\SimpleModelFactory;

final class ModelFactory
{

	public static function createUsersRepository(): UsersRepository
	{
		$repository = new UsersRepository(new UsersMapper());

		$factory = new SimpleModelFactory(new Cache(new MemoryStorage()), [
			'users' => $repository,
		]);
		$factory->create();

		$users = [
			['John', 'john@example.com', 'active', 30, 'admin'],
			['Jane', 'jane@example.org', 'active', 17, null],
			['Jack', 'jack@example.com', 'banned', 45, null],
			['Jill', 'admin@example.com', 'pending', 22, 'moderator'],
			['Joe', 'joe@example.org', 'active', 65, null],
		];

		foreach ($users as [$name, $email, $status, $age, $note]) {
			$user = new User();
			$user->name = $name;
			$user->email = $email;
			$user->status = $status;
			$user->age = $age;
			$user->note = $note;
			$repository->persist($user);
		}

		$repository->flush();

		return $repository;
	}

}

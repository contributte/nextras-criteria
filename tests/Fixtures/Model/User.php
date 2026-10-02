<?php declare(strict_types = 1);

namespace Tests\Fixtures\Model;

use Nextras\Orm\Entity\Entity;

/**
 * @property int $id {primary}
 * @property string $name
 * @property string $email
 * @property string $status
 * @property int $age
 * @property string|null $note
 */
class User extends Entity
{

}

<?php
declare(strict_types=1);

namespace App\User\Database\Repository;

use App\User\Database\Entity\User;
use Doctrine\ORM\EntityRepository;

/**
 * @method User|NULL find($id, ?int $lockMode = null, ?int $lockVersion = null)
 * @method User|NULL findOneBy(array<string, mixed> $criteria, array<string, string>|NULL $orderBy = null)
 * @method User[] findAll()
 * @method User[] findBy(array<string, mixed> $criteria, array<string, string>|NULL $orderBy = null, ?int $limit = null, ?int $offset = null)
 *
 * @extends EntityRepository<UserRepository>
 */
class UserRepository extends EntityRepository {}

<?php
declare(strict_types=1);

namespace App\User\Database\Repository;

use App\User\Database\Entity\User;
use Doctrine\ORM\EntityRepository;

/**
 * @method User|NULL find($id, ?int $lockMode = NULL, ?int $lockVersion = NULL)
 * @method User|NULL findOneBy(array<string, mixed> $criteria, array<string, string>|NULL $orderBy = NULL)
 * @method User[] findAll()
 * @method User[] findBy(array<string, mixed> $criteria, array<string, string>|NULL $orderBy = NULL, ?int $limit = NULL, ?int $offset = NULL)
 *
 * @extends EntityRepository<UserRepository>
 */
class UserRepository extends EntityRepository {}

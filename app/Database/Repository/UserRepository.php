<?php
declare(strict_types=1);

namespace App\Database\Repository;

use Doctrine\ORM\EntityRepository;

/**
 * @method \App\Database\Entity\User|NULL find($id, ?int $lockMode = NULL, ?int $lockVersion = NULL)
 * @method \App\Database\Entity\User|NULL findOneBy(array $criteria, array $orderBy = NULL)
 * @method \App\Database\Entity\User[] findAll()
 * @method \App\Database\Entity\User[] findBy(array $criteria, array $orderBy = NULL, ?int $limit = NULL, ?int $offset = NULL)
 * @extends EntityRepository<UserRepository>
 */
class UserRepository extends EntityRepository
{
}
<?php
declare(strict_types=1);

namespace App\User\Database\Repository;

use App\User\Database\Entity\User;
use Doctrine\ORM\EntityRepository;

/**
 * @method User|null find($id, ?int $lockMode = null, ?int $lockVersion = null)
 * @method User|null findOneBy(array<string, mixed> $criteria, array<string, string>|null $orderBy = null)
 * @method User[] findAll()
 * @method User[] findBy(array<string, mixed> $criteria, array<string, string>|null $orderBy = null, ?int $limit = null, ?int $offset = null)
 *
 * @extends EntityRepository<UserRepository>
 */
class UserRepository extends EntityRepository {}

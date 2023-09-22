<?php
/**
 * Copyright (c) 2023 Strategio Digital s.r.o.
 * @author Jiří Zapletal (https://strategio.dev, jz@strategio.dev)
 */
declare(strict_types=1);

namespace App\Database\Repository;

use Doctrine\ORM\EntityRepository;

/**
 * @extends EntityRepository<UserRepository>
 */
class UserRepository extends EntityRepository
{
}
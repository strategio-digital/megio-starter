<?php
/**
 * Copyright (c) 2022 Strategio Digital s.r.o.
 * @author Jiří Zapletal (https://strategio.dev, jz@strategio.dev)
 */
declare(strict_types=1);

namespace App\Database;

use App\Database\Entity\User;
use App\Database\Repository\UserRepository;

class EntityManager extends \Saas\Database\EntityManager
{
    public function getUserRepo(): UserRepository
    {
        return $this->getRepository(User::class); // @phpstan-ignore-line
    }
}
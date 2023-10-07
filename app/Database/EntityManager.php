<?php
declare(strict_types=1);

namespace App\Database;

use App\Database\Entity\User;
use App\Database\Repository\UserRepository;

class EntityManager extends \Megio\Database\EntityManager
{
    public function getUserRepo(): UserRepository
    {
        return $this->getRepository(User::class); // @phpstan-ignore-line
    }
}
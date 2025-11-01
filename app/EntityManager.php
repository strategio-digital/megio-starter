<?php
declare(strict_types=1);

namespace App;

use App\User\Database\Entity\User;
use App\User\Database\Repository\UserRepository;

use function assert;

class EntityManager extends \Megio\Database\EntityManager
{
    public function getUserRepo(): UserRepository
    {
        $repo = $this->getRepository(User::class);
        assert($repo instanceof UserRepository === true);
        return $repo;
    }
}

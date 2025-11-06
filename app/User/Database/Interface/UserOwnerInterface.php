<?php
declare(strict_types=1);

namespace App\User\Database\Interface;

use App\User\Database\Entity\User;

interface UserOwnerInterface
{
    public function getUser(): User;

    public function setUser(User $user): self;

    public function belongsTo(User $user): bool;
}

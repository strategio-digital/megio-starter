<?php
declare(strict_types=1);

namespace App\User\Database\Interface;

use App\User\Database\Entity\User;

interface UserOwnerInterface
{
    public function getOwner(): User;

    public function setOwner(User $user): self;

    public function belongsTo(User $user): bool;
}

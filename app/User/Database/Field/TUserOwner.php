<?php
declare(strict_types=1);

namespace App\User\Database\Field;

use App\User\Database\Entity\User;
use Doctrine\ORM\Mapping as ORM;
use Megio\Database\Interface\IAuthenticable;

trait TUserOwner
{
    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(name: 'user_id', referencedColumnName: 'id', nullable: false)]
    private User $user;

    public function getUser(): User
    {
        return $this->user;
    }

    public function setUser(User $user): self
    {
        $this->user = $user;
        return $this;
    }

    public function belongsTo(?IAuthenticable $user): bool
    {
        return $this->user->getId() === $user?->getId();
    }
}

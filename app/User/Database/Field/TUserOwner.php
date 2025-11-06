<?php
declare(strict_types=1);

namespace App\User\Database\Field;

use App\User\Database\Entity\User;
use Doctrine\ORM\Mapping as ORM;
use Megio\Database\Interface\IAuthenticable;

trait TUserOwner
{
    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(name: 'owner_id', referencedColumnName: 'id', nullable: false)]
    private User $owner;

    public function getOwner(): User
    {
        return $this->owner;
    }

    public function setOwner(User $owner): self
    {
        $this->owner = $owner;
        return $this;
    }

    public function belongsTo(?IAuthenticable $user): bool
    {
        return $this->owner->getId() === $user?->getId();
    }
}

<?php
declare(strict_types=1);

namespace App\User\Database\Entity;

use App\User\Database\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Megio\Database\Entity\Auth\Role;
use Megio\Database\Field\TCreatedAt;
use Megio\Database\Field\TEmail;
use Megio\Database\Field\TId;
use Megio\Database\Field\TLastLogin;
use Megio\Database\Field\TPassword;
use Megio\Database\Field\TUpdatedAt;
use Megio\Database\Interface\IAuthenticable;
use Megio\Database\Interface\ICrudable;
use Megio\Database\Interface\IJoinable;
use Megio\Database\Method\TResourceMethods;
use Megio\Database\Method\TRoleMethods;

#[ORM\Table(name: '`user`')]
#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\HasLifecycleCallbacks]
class User implements ICrudable, IAuthenticable, IJoinable
{
    use TId;
    use TCreatedAt;
    use TUpdatedAt;
    use TEmail;
    use TPassword;
    use TLastLogin;
    use TRoleMethods;
    use TResourceMethods;

    public const string TABLE_NAME = 'user';

    /** @var Collection<int, Role> */
    #[ORM\ManyToMany(targetEntity: Role::class)]
    #[ORM\JoinTable(name: 'user_has_role')]
    #[ORM\JoinColumn(name: "user_id", referencedColumnName: "id", onDelete: 'CASCADE')]
    #[ORM\InverseJoinColumn(name: "role_id", referencedColumnName: "id", onDelete: 'CASCADE')]
    private Collection $roles;

    #[ORM\Column(options: ['default' => false])]
    private bool $isActive = false;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $activationToken = null;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $resetPasswordToken = null;

    #[ORM\Column(options: ['default' => false])]
    private bool $isSoftDeleted = false;

    public function __construct()
    {
        $this->roles = new ArrayCollection();
    }

    public function getJoinableLabel(): array
    {
        return [
            'fields' => ['email'],
            'format' => '%s',
        ];
    }

    public function isActive(): bool
    {
        return $this->isActive;
    }

    public function setIsActive(bool $isActive): void
    {
        $this->isActive = $isActive;
    }

    public function getActivationToken(): ?string
    {
        return $this->activationToken;
    }

    public function setActivationToken(?string $activationToken): void
    {
        $this->activationToken = $activationToken;
    }

    public function isSoftDeleted(): bool
    {
        return $this->isSoftDeleted;
    }

    public function setIsSoftDeleted(bool $isSoftDeleted): void
    {
        $this->isSoftDeleted = $isSoftDeleted;
    }

    public function getResetPasswordToken(): ?string
    {
        return $this->resetPasswordToken;
    }

    public function setResetPasswordToken(?string $resetPasswordToken): void
    {
        $this->resetPasswordToken = $resetPasswordToken;
    }

    /**
     * @return Collection<int, Role>
     */
    public function getRoles(): Collection
    {
        return $this->roles;
    }
}

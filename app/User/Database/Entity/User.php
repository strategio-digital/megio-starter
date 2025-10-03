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
use Megio\Database\Method\TResourceMethods;
use Megio\Database\Method\TRoleMethods;

#[ORM\Table(name: '`user`')]
#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\HasLifecycleCallbacks]
class User implements ICrudable, IAuthenticable
{
    use TId;
    use TCreatedAt;
    use TUpdatedAt;
    use TEmail;
    use TPassword;
    use TLastLogin;
    use TRoleMethods;
    use TResourceMethods;

    /** @var Collection<int, Role> */
    #[ORM\ManyToMany(targetEntity: Role::class)]
    #[ORM\JoinTable(name: 'user_has_role')]
    #[ORM\JoinColumn(name: "user_id", referencedColumnName: "id", onDelete: 'CASCADE')]
    #[ORM\InverseJoinColumn(name: "role_id", referencedColumnName: "id", onDelete: 'CASCADE')]
    protected Collection $roles;

    public function __construct()
    {
        $this->roles = new ArrayCollection();
    }
}

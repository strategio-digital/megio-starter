<?php
/**
 * Copyright (c) 2023 Strategio Digital s.r.o.
 * @author Jiří Zapletal (https://strategio.dev, jz@strategio.dev)
 */
declare(strict_types=1);

namespace App\Database\Entity;

use App\Database\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Saas\Database\Entity\Auth\Role;
use Saas\Database\Field\TCreatedAt;
use Saas\Database\Field\TEmail;
use Saas\Database\Field\TId;
use Saas\Database\Field\TLastLogin;
use Saas\Database\Field\TPassword;
use Saas\Database\Field\TUpdatedAt;
use Saas\Database\Interface\IAuthenticable;
use Saas\Database\Interface\ICrudable;
use Saas\Database\Method\TResourceMethods;
use Saas\Database\Method\TRoleMethods;

#[ORM\Table(name: '`user`')]
#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\HasLifecycleCallbacks]
class User implements ICrudable, IAuthenticable
{
    use TId, TCreatedAt, TUpdatedAt, TEmail, TPassword, TLastLogin;
    use TRoleMethods, TResourceMethods;
    
    /** @var string[] */
    public array $invisibleFields = ['id', 'updatedAt'];
    
    /** @var string[] */
    public array $showAllFields = ['email', 'lastLogin', 'createdAt', 'updatedAt'];
    
    /** @var string[] */
    public array $showOneFields = ['email', 'lastLogin', 'createdAt', 'updatedAt'];
    
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
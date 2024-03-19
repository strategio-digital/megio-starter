<?php
declare(strict_types=1);

namespace App\Recipe;

use App\Database\Entity\User;
use App\Database\EntityManager;
use Megio\Collection\FieldBuilder\Field\Select;
use Megio\Collection\FieldBuilder\Field\Text;
use Megio\Collection\FieldBuilder\Rule\MaxRule;
use Megio\Collection\FieldBuilder\Rule\NullableRule;
use Megio\Collection\FieldBuilder\Rule\UniqueRule;
use Megio\Collection\CollectionRecipe;
use Megio\Collection\FieldBuilder\Field\Email;
use Megio\Collection\FieldBuilder\Field\Password;
use Megio\Collection\FieldBuilder\FieldBuilder;
use Megio\Collection\FieldBuilder\Rule\EqualRule;
use Megio\Collection\FieldBuilder\Rule\MinRule;
use Megio\Collection\FieldBuilder\Rule\RequiredRule;

class UserRecipe extends CollectionRecipe
{
    public function __construct(protected EntityManager $em)
    {
    }
    
    public function source(): string
    {
        return User::class;
    }
    
    public function name(): string
    {
        return 'user';
    }
    
    public function readOne(): array
    {
        return ['email', 'lastLogin', 'createdAt', 'updatedAt'];
    }
    
    public function readAll(): array
    {
        return ['email', 'lastLogin', 'createdAt'];
    }
    
    public function create(FieldBuilder $builder): FieldBuilder
    {
        $roles = $this->em->getAuthRoleRepo()->findAll();
        $items = array_map(fn($role) => new Select\Item($role->getId(), $role->getName()), $roles);
        
        return $builder
            //->ignoreDoctrineRules()
            //->ignoreRules(['email' => ['unique']])
            
            ->add(new Email('email', 'E-mail', [
                new RequiredRule(),
                new UniqueRule(User::class, 'email')
            ]))
            ->add(new Password('password', 'Heslo', [
                new RequiredRule(),
                new MinRule(6),
                new MaxRule(32)
            ]))
            ->add(new Password('password_check', 'Heslo znovu', [
                new EqualRule('password'),
            ], [], false))
            ->add(new Select('role', 'Role', $items, [
                new NullableRule(),
            ]));
    }
    
    public function update(FieldBuilder $builder): FieldBuilder
    {
        return $builder
            ->add(new Text('id', 'ID', [], ['disabled' => true], false))
            ->add(new Email('email', 'E-mail'))
            ->add(new Password('password', 'Heslo'));
    }
}
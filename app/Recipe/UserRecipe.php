<?php
declare(strict_types=1);

namespace App\Recipe;

use App\Database\Entity\User;
use App\Database\EntityManager;
use Megio\Collection\FieldBuilder\Field\SelectField;
use Megio\Collection\FieldBuilder\Field\TextField;
use Megio\Collection\FieldBuilder\Rule\MaxRule;
use Megio\Collection\FieldBuilder\Rule\NullableRule;
use Megio\Collection\FieldBuilder\Rule\UniqueRule;
use Megio\Collection\CollectionRecipe;
use Megio\Collection\FieldBuilder\Field\EmailField;
use Megio\Collection\FieldBuilder\Field\PasswordField;
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
        $items = array_map(fn($role) => new SelectField\Item($role->getId(), $role->getName()), $roles);
        
        return $builder
            //->ignoreDoctrineRules()
            //->ignoreRules(['email' => ['unique']])
            
            ->add(new EmailField('email', 'E-mail', [
                new RequiredRule(),
                new UniqueRule(User::class, 'email')
            ]))
            ->add(new PasswordField('password', 'Heslo', [
                new RequiredRule(),
                new MinRule(6),
                new MaxRule(32)
            ]))
            ->add(new PasswordField('password_check', 'Heslo znovu', [
                new EqualRule('password'),
            ], [], false))
            ->add(new SelectField('role', 'Role', $items, [
                new NullableRule(),
            ]));
    }
    
    public function update(FieldBuilder $builder): FieldBuilder
    {
        return $builder
            ->add(new TextField('id', 'ID', [], ['disabled' => true], false))
            ->add(new EmailField('email', 'E-mail'))
            ->add(new PasswordField('password', 'Heslo'));
    }
}
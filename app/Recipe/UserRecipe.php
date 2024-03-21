<?php
declare(strict_types=1);

namespace App\Recipe;

use App\Database\Entity\User;
use App\Database\EntityManager;
use Megio\Collection\ReadBuilder\Column\TextColumn;
use Megio\Collection\ReadBuilder\ReadBuilder;
use Megio\Collection\WriteBuilder\Field\SelectField;
use Megio\Collection\WriteBuilder\Field\TextField;
use Megio\Collection\WriteBuilder\Rule\MaxRule;
use Megio\Collection\WriteBuilder\Rule\NullableRule;
use Megio\Collection\WriteBuilder\Rule\UniqueRule;
use Megio\Collection\CollectionRecipe;
use Megio\Collection\WriteBuilder\Field\EmailField;
use Megio\Collection\WriteBuilder\Field\PasswordField;
use Megio\Collection\WriteBuilder\WriteBuilder;
use Megio\Collection\WriteBuilder\Rule\EqualRule;
use Megio\Collection\WriteBuilder\Rule\MinRule;
use Megio\Collection\WriteBuilder\Rule\RequiredRule;

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
    
    public function read(ReadBuilder $builder): ReadBuilder
    {
        return $builder->buildByDbSchema(['password']);
    }
    
    public function readAll(ReadBuilder $builder): ReadBuilder
    {
        return $builder->buildByDbSchema(['password']);
    }
    
    public function create(WriteBuilder $builder): WriteBuilder
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
            ], [], false, false))
            ->add(new SelectField('role', 'Role', $items, [
                new NullableRule(),
            ]));
    }
    
    public function update(WriteBuilder $builder): WriteBuilder
    {
        return $builder
            ->add(new TextField('id', 'ID', [], [], true))
            ->add(new EmailField('email', 'E-mail'))
            ->add(new PasswordField('password', 'Heslo'));
    }
}
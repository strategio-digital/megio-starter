<?php
declare(strict_types=1);

namespace App\Recipe;

use App\Database\Entity\User;
use Megio\Collection\Builder\Field\Text;
use Megio\Collection\Builder\Rule\MaxRule;
use Megio\Collection\CollectionRecipe;
use Megio\Collection\Builder\Field\Email;
use Megio\Collection\Builder\Field\Password;
use Megio\Collection\Builder\Builder;
use Megio\Collection\Builder\Rule\EqualRule;
use Megio\Collection\Builder\Rule\MinRule;
use Megio\Collection\Builder\Rule\RequiredRule;

class UserRecipe extends CollectionRecipe
{
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
    
    public function create(Builder $builder): Builder
    {
        return $builder
            ->add(new Email('email', 'E-mail', [new RequiredRule()]))
            ->add(new Password('password', 'Heslo', [
                new RequiredRule(),
                new MinRule(6),
                new MaxRule(32)
            ]))
            ->add(new Password('password_check', 'Heslo znovu', [
                new EqualRule('password'),
            ], [], false));
    }
    
    public function update(Builder $builder): Builder
    {
        return $builder
            ->add(new Text('id', 'ID', [], ['disabled' => true], false))
            ->add(new Email('email', 'E-mail'))
            ->add(new Password('password', 'Heslo'));
    }
}
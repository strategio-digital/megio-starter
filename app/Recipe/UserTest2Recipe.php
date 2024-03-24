<?php
declare(strict_types=1);

namespace App\Recipe;

use App\Database\Entity\User;
use Megio\Collection\CollectionRecipe;
use Megio\Collection\WriteBuilder\Field\PasswordField;
use Megio\Collection\WriteBuilder\WriteBuilder;

class UserTest2Recipe extends CollectionRecipe
{
    public function source(): string
    {
        return User::class;
    }
    
    public function key(): string
    {
        return 'user-test-2';
    }
    
    public function create(WriteBuilder $builder): WriteBuilder
    {
        return $builder
            //->ignoreRules(['email' => [UniqueRule::class]])
            ->buildByDbSchema(exclude: ['lastLogin'], persist: true)
            ->add(new PasswordField(name: 'password_check', label: 'Kontrola hesla', mapToEntity: false))
        ;
    }
}
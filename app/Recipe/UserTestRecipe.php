<?php
declare(strict_types=1);

namespace App\Recipe;

use App\Database\Entity\User;
use Megio\Collection\CollectionRecipe;
use Megio\Collection\ReadBuilder\Column\TextColumn;
use Megio\Collection\ReadBuilder\ReadBuilder;

class UserTestRecipe extends CollectionRecipe
{
    public function source(): string
    {
        return User::class;
    }
    
    public function name(): string
    {
        return 'user-test';
    }
    
    public function read(ReadBuilder $builder): ReadBuilder
    {
        return $builder
            ->add(new TextColumn('email', 'E-mail'))
            ->add(new TextColumn('updatedAt', 'Aktualizováno'));
    }
}
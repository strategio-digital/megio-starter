<?php
declare(strict_types=1);

namespace App\Recipe;

use App\Database\Entity\User;
use Megio\Collection\CollectionRecipe;
use Megio\Collection\ReadBuilder\Column\StringColumn;
use Megio\Collection\ReadBuilder\ReadBuilder;

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
    
    public function read(ReadBuilder $builder): ReadBuilder
    {
        return $builder
            ->add(new StringColumn(key: 'email', name: 'E-mail'))
            ->add(new StringColumn(key: 'updatedAt', name: 'Aktualizováno'));
    }
}
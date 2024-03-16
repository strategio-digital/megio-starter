<?php
declare(strict_types=1);

namespace App\Recipe;

use App\Database\Entity\User;
use Megio\Collection\CollectionRecipe;

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
    public function showOneColumns(): array
    {
        return ['email'];
    }
    
    public function showAllColumns(): array
    {
        return ['email'];
    }
}
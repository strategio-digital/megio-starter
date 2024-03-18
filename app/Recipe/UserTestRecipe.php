<?php
declare(strict_types=1);

namespace App\Recipe;

use App\Database\Entity\User;
use Megio\Collection\CollectionRecipe;
use Megio\Collection\Builder\Field\Email;
use Megio\Collection\Builder\Field\Password;
use Megio\Collection\Builder\Field\Text;
use Megio\Collection\Builder\Builder;

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
    
    public function readOne(): array
    {
        return ['email'];
    }
    
    public function readAll(): array
    {
        return ['email'];
    }
}
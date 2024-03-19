<?php
declare(strict_types=1);

namespace App\Recipe;

use App\Database\Entity\User;
use Megio\Collection\CollectionRecipe;
use Megio\Collection\FieldBuilder\Field\Email;
use Megio\Collection\FieldBuilder\Field\Password;
use Megio\Collection\FieldBuilder\Field\Text;
use Megio\Collection\FieldBuilder\FieldBuilder;

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
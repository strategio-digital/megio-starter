<?php
declare(strict_types=1);

namespace App\Recipe\Blog;

use App\Database\Entity\Blog\Author;
use Megio\Collection\CollectionRecipe;
use Megio\Collection\ReadBuilder\Column\StringColumn;
use Megio\Collection\ReadBuilder\ReadBuilder;
use Megio\Collection\RecipeRequest;

class AuthorRecipe extends CollectionRecipe
{
    public function source(): string
    {
        return Author::class;
    }
    
    public function key(): string
    {
        return 'blog-author';
    }
    
    public function readAll(ReadBuilder $builder, RecipeRequest $request): ReadBuilder
    {
        return $builder->buildByDbSchema(persist: true)
            ->add(new StringColumn('profile', 'P'));
    }
}
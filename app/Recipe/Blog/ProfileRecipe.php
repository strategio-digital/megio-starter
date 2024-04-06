<?php
declare(strict_types=1);

namespace App\Recipe\Blog;

use App\Database\Entity\Blog\Author;
use App\Database\Entity\Blog\Profile;
use Megio\Collection\CollectionRecipe;
use Megio\Collection\RecipeRequest;
use Megio\Collection\WriteBuilder\Field\OneToOneSelectField;
use Megio\Collection\WriteBuilder\WriteBuilder;

class ProfileRecipe extends CollectionRecipe
{
    public function source(): string
    {
        return Profile::class;
    }
    
    public function key(): string
    {
        return 'blog-profile';
    }
    
    public function update(WriteBuilder $builder, RecipeRequest $request): WriteBuilder
    {
        return $builder
            ->buildByDbSchema(exclude: ['author'], persist: true)
            ->add(new OneToOneSelectField(
                name: 'author',
                label: 'Author',
                reverseEntity: Author::class,
                primaryKey: 'id'
            ));
    }
}
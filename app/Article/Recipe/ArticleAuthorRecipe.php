<?php
declare(strict_types=1);

namespace App\Article\Recipe;

use App\Article\Database\Entity\ArticleAuthor;
use Megio\Collection\CollectionRecipe;

class ArticleAuthorRecipe extends CollectionRecipe
{
    public function source(): string
    {
        return ArticleAuthor::class;
    }
    
    public function key(): string
    {
        return 'article-author';
    }
}
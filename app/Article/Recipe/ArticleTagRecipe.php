<?php
declare(strict_types=1);

namespace App\Article\Recipe;

use App\Article\Database\Entity\ArticleTag;
use Megio\Collection\CollectionRecipe;

class ArticleTagRecipe extends CollectionRecipe
{
    public function source(): string
    {
        return ArticleTag::class;
    }
    
    public function key(): string
    {
        return 'article-tag';
    }
}
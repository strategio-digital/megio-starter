<?php
declare(strict_types=1);

namespace App\Article\Recipe;

use App\Article\Database\Entity\Article;
use Megio\Collection\CollectionRecipe;

class ArticleRecipe extends CollectionRecipe
{
    public function source(): string
    {
        return Article::class;
    }
    
    public function key(): string
    {
        return 'article';
    }
}
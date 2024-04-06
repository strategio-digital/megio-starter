<?php
declare(strict_types=1);

namespace App\Recipe\Blog;

use App\Database\Entity\Blog\Article;
use Megio\Collection\CollectionRecipe;

class ArticleRecipe extends CollectionRecipe
{
    public function source(): string
    {
        return Article::class;
    }
    
    public function key(): string
    {
        return 'blog-article';
    }
}
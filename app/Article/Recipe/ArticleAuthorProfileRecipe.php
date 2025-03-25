<?php
declare(strict_types=1);

namespace App\Article\Recipe;

use App\Article\Database\Entity\ArticleAuthorProfile;
use Megio\Collection\CollectionRecipe;

class ArticleAuthorProfileRecipe extends CollectionRecipe
{
    public function source(): string
    {
        return ArticleAuthorProfile::class;
    }
    
    public function key(): string
    {
        return 'article-author-profile';
    }
}
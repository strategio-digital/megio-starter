<?php
declare(strict_types=1);

namespace App\Database\Entity\Blog;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Megio\Database\Field\TCreatedAt;
use Megio\Database\Field\TId;
use Megio\Database\Field\TUpdatedAt;
use Megio\Database\Interface\ICrudable;
use Megio\Database\Interface\IJoinable;

#[ORM\Entity]
#[ORM\Table(name: '`blog_article_tag`')]
#[ORM\HasLifecycleCallbacks]
class Tag implements ICrudable, IJoinable
{
    use TId, TCreatedAt, TUpdatedAt;
    
    ## DONE
    #[ORM\Column(length: 32)]
    protected string $name;
    
    ## DONE
    #[ORM\Column(length: 32, unique: true)]
    protected string $slug;
    
    ## TODO:
    /** @var Collection<int, Article> */
    #[ORM\ManyToMany(targetEntity: Article::class, mappedBy: 'tags')]
    protected Collection $articles;
    
    public function __construct() {
        $this->articles = new ArrayCollection();
    }
    
    /**
     * @return array{fields: string[], format: string}
     */
    public function getJoinableLabel(): array
    {
        return [
            'fields' => ['name'],
            'format' => '%s'
        ];
    }
}
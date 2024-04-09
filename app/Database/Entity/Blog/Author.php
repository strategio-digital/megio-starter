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
#[ORM\Table(name: '`blog_author`')]
#[ORM\HasLifecycleCallbacks]
class Author implements ICrudable, IJoinable
{
    use TId, TCreatedAt, TUpdatedAt;
    
    ## DONE
    #[ORM\Column(length: 64)]
    protected string $firstName;
    
    ## DONE
    #[ORM\Column(length: 64)]
    protected string $lastName;
    
    ## DONE
    #[ORM\OneToOne(mappedBy: 'author', targetEntity: Profile::class)]
    protected ?Profile $profile = null;
    
    ## DONE
    /** @var Collection<int, Article> */
    #[ORM\OneToMany(mappedBy: 'author', targetEntity: Article::class, fetch: 'LAZY')]
    protected Collection $articles;
    
    public function __construct()
    {
        $this->articles = new ArrayCollection();
    }
    
    public function getProfile(): ?Profile
    {
        return $this->profile;
    }
    
    /**
     * @return array{fields: string[], format: string}
     */
    public function getJoinableLabel(): array
    {
        return [
            'fields' => ['firstName', 'lastName'],
            'format' => '%s %s'
        ];
    }
}
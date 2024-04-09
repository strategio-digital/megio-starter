<?php
declare(strict_types=1);

namespace App\Database\Entity\Blog;

use Doctrine\ORM\Mapping as ORM;
use Megio\Database\Field\TCreatedAt;
use Megio\Database\Field\TId;
use Megio\Database\Field\TUpdatedAt;
use Megio\Database\Interface\ICrudable;
use Megio\Database\Interface\IJoinable;

#[ORM\Entity]
#[ORM\Table(name: '`blog_author_profile`')]
#[ORM\HasLifecycleCallbacks]
class Profile implements ICrudable, IJoinable
{
    use TId, TCreatedAt, TUpdatedAt;
    
    ## DONE
    #[ORM\Column(length: 64, unique: true)]
    protected string $nickname;
    
    ## DONE
    #[ORM\Column(type: 'text')]
    protected string $biography;
    
    ## DONE
    #[ORM\OneToOne(inversedBy: 'profile', targetEntity: Author::class)]
    #[ORM\JoinColumn(name: 'author_id', referencedColumnName: 'id')]
    protected ?Author $author = null;
    
    /**
     * @return array{fields: string[], format: string}
     */
    public function getJoinableLabel(): array
    {
        return [
            'fields' => ['nickname'],
            'format' => '%s'
        ];
    }
}
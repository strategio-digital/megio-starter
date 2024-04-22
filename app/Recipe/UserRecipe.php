<?php
declare(strict_types=1);

namespace App\Recipe;

use App\Database\Entity\User;
use App\Database\EntityManager;
use Megio\Collection\ReadBuilder\Column\EmailColumn;
use Megio\Collection\ReadBuilder\ReadBuilder;
use Megio\Collection\Formatter\CallableFormatter;
use Megio\Collection\RecipeRequest;
use Megio\Collection\WriteBuilder\Field\Base\EmptyValue;
use Megio\Collection\WriteBuilder\Field\TextField;
use Megio\Collection\WriteBuilder\Field\ToManySelectField;
use Megio\Collection\WriteBuilder\Rule\MaxRule;
use Megio\Collection\WriteBuilder\Rule\UniqueRule;
use Megio\Collection\CollectionRecipe;
use Megio\Collection\WriteBuilder\Field\EmailField;
use Megio\Collection\WriteBuilder\Field\PasswordField;
use Megio\Collection\WriteBuilder\WriteBuilder;
use Megio\Collection\WriteBuilder\Rule\EqualRule;
use Megio\Collection\WriteBuilder\Rule\MinRule;
use Megio\Collection\WriteBuilder\Rule\RequiredRule;
use Megio\Database\Entity\Auth\Role;

class UserRecipe extends CollectionRecipe
{
    public function __construct(protected EntityManager $em)
    {
    }
    
    public function source(): string
    {
        return User::class;
    }
    
    public function key(): string
    {
        return 'user';
    }
    
    public function read(ReadBuilder $builder, RecipeRequest $request): ReadBuilder
    {
        return $builder
            ->buildByDbSchema(exclude: ['password', 'email'], persist: true)
            ->add(new EmailColumn(key: 'email', name: 'E-mail', formatters: [
                new CallableFormatter(fn($value) => 'mailto:' . $value),
            ]));
    }
    
    public function readAll(ReadBuilder $builder, RecipeRequest $request): ReadBuilder
    {
        return $builder
            ->buildByDbSchema(exclude: ['password', 'email'], persist: true)
            ->add(col: new EmailColumn(key: 'email', name: 'E-mail'), moveBeforeKey: 'lastLogin');
    }
    
    public function create(WriteBuilder $builder, RecipeRequest $request): WriteBuilder
    {
        return $builder
            ->add(new EmailField(name: 'email', label: 'E-mail', rules: [
                new RequiredRule(),
                new UniqueRule(targetEntity: User::class, columnName: 'email', message: 'Tento e-mail je již použit.')
            ]))
            ->add(new PasswordField(name: 'password', label: 'Heslo', rules: [
                new RequiredRule(),
                new MinRule(6),
                new MaxRule(32)
            ]))
            ->add(new PasswordField(name: 'password_check', label: 'Heslo znovu', rules: [
                new RequiredRule(),
                new EqualRule('password'),
            ], mapToEntity: false))
            ->add(new ToManySelectField(name: 'roles', label: 'Role', reverseEntity: Role::class));
    }
    
    public function update(WriteBuilder $builder, RecipeRequest $request): WriteBuilder
    {
        $pwf = new PasswordField(name: 'password', label: 'Heslo');
        
        // Do not show password on form rendering
        if ($request->isFormRendering()) {
            $pwf->setValue(new EmptyValue());
        }
        
        return $builder
            ->add(new TextField(name: 'id', label: 'ID', attrs: ['fullWidth' => true], disabled: true))
            ->add(new EmailField(name: 'email', label: 'E-mail'))
            ->add($pwf)
            ->add(new ToManySelectField(
                name: 'roles',
                label: 'Role',
                reverseEntity: Role::class,
                attrs: ['fullWidth' => true]
            ));
    }
}
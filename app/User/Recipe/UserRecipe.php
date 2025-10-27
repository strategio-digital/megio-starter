<?php
declare(strict_types=1);

namespace App\User\Recipe;

use App\User\Database\Entity\User;
use Megio\Collection\CollectionRecipe;
use Megio\Collection\CollectionRequest;
use Megio\Collection\Formatter\CallableFormatter;
use Megio\Collection\ReadBuilder\Column\EmailColumn;
use Megio\Collection\ReadBuilder\Column\ToManyColumn;
use Megio\Collection\ReadBuilder\ReadBuilder;
use Megio\Collection\SearchBuilder\Searchable;
use Megio\Collection\SearchBuilder\SearchBuilder;
use Megio\Collection\WriteBuilder\Field\Base\EmptyValue;
use Megio\Collection\WriteBuilder\Field\EmailField;
use Megio\Collection\WriteBuilder\Field\PasswordField;
use Megio\Collection\WriteBuilder\Field\TextField;
use Megio\Collection\WriteBuilder\Field\ToManySelectField;
use Megio\Collection\WriteBuilder\Rule\EqualRule;
use Megio\Collection\WriteBuilder\Rule\MaxRule;
use Megio\Collection\WriteBuilder\Rule\MinRule;
use Megio\Collection\WriteBuilder\Rule\RequiredRule;
use Megio\Collection\WriteBuilder\Rule\UniqueRule;
use Megio\Collection\WriteBuilder\WriteBuilder;
use Megio\Database\Entity\Auth\Role;

class UserRecipe extends CollectionRecipe
{
    public function source(): string
    {
        return User::class;
    }

    public function key(): string
    {
        return 'user';
    }

    public function search(SearchBuilder $builder, CollectionRequest $request): SearchBuilder
    {
        return $builder
            ->keepDefaults()
            ->addSearchable(new Searchable(column: 'email', operator: 'LIKE', formatter: function (mixed $value): string {
                assert(is_string($value));
                return '%' . $value . '%';
            }))
            ->addSearchable(new Searchable(column: 'name', relation: 'roles'));
    }

    public function read(ReadBuilder $builder, CollectionRequest $request): ReadBuilder
    {
        return $builder
            ->buildByDbSchema(exclude: ['password', 'email'], persist: true)
            ->add(new EmailColumn(key: 'email', name: 'Email', formatters: [
                new CallableFormatter(function (mixed $value): string {
                    assert(is_string($value));
                    return 'mailto:' . $value;
                }),
            ]));
    }

    public function readAll(ReadBuilder $builder, CollectionRequest $request): ReadBuilder
    {
        return $builder
            ->buildByDbSchema(exclude: ['password', 'email'], persist: true)
            ->add(col: new EmailColumn(key: 'email', name: 'Email', sortable: true), moveBeforeKey: 'lastLogin')
            ->add(col: new ToManyColumn(key: 'roles', name: 'Roles'));
    }

    public function create(WriteBuilder $builder, CollectionRequest $request): WriteBuilder
    {
        return $builder
            ->add(new EmailField(name: 'email', label: 'Email', rules: [
                new RequiredRule(),
                new UniqueRule(targetEntity: User::class, columnName: 'email', message: 'This email is already in use.'),
            ]))
            ->add(new PasswordField(name: 'password', label: 'Password', rules: [
                new RequiredRule(),
                new MinRule(6),
                new MaxRule(32),
            ]))
            ->add(new PasswordField(name: 'password_check', label: 'Password again', rules: [
                new RequiredRule(),
                new EqualRule('password'),
            ], mapToEntity: false))
            ->add(new ToManySelectField(name: 'roles', label: 'Roles', reverseEntity: Role::class));
    }

    public function update(WriteBuilder $builder, CollectionRequest $request): WriteBuilder
    {
        $pwf = new PasswordField(name: 'password', label: 'Password');

        // Do not show password on form rendering
        if ($request->isFormRendering()) {
            $pwf->setValue(new EmptyValue());
        }

        return $builder
            ->add(new TextField(name: 'id', label: 'ID', attrs: ['fullWidth' => true], disabled: true))
            ->add(new EmailField(name: 'email', label: 'Email'))
            ->add($pwf)
            ->add(new ToManySelectField(
                name: 'roles',
                label: 'Roles',
                reverseEntity: Role::class,
                attrs: ['fullWidth' => true],
            ));
    }
}

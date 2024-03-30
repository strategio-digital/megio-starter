<?php
declare(strict_types=1);

namespace App\Recipe;

use App\Database\Entity\User;
use App\Database\EntityManager;
use Megio\Collection\ReadBuilder\Column\EmailColumn;
use Megio\Collection\ReadBuilder\ReadBuilder;
use Megio\Collection\Formatter\CallableFormatter;
use Megio\Collection\WriteBuilder\Field\Base\EmptyValue;
use Megio\Collection\WriteBuilder\Field\SelectField;
use Megio\Collection\WriteBuilder\Field\TextField;
use Megio\Collection\WriteBuilder\Rule\CallableRule;
use Megio\Collection\WriteBuilder\Rule\MaxRule;
use Megio\Collection\WriteBuilder\Rule\NullableRule;
use Megio\Collection\WriteBuilder\Rule\UniqueRule;
use Megio\Collection\CollectionRecipe;
use Megio\Collection\WriteBuilder\Field\EmailField;
use Megio\Collection\WriteBuilder\Field\PasswordField;
use Megio\Collection\WriteBuilder\Serializer\CallableSerializer;
use Megio\Collection\WriteBuilder\WriteBuilder;
use Megio\Collection\WriteBuilder\Rule\EqualRule;
use Megio\Collection\WriteBuilder\Rule\MinRule;
use Megio\Collection\WriteBuilder\Rule\RequiredRule;
use Symfony\Component\HttpFoundation\Request;

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
    
    public function read(ReadBuilder $builder, Request $request): ReadBuilder
    {
        return $builder
            ->buildByDbSchema(exclude: ['password', 'email'], persist: true)
            ->add(new EmailColumn(key: 'email', name: 'E-mail', formatters: [
                new CallableFormatter(fn($value) => 'mailto:' . $value),
            ]));
    }
    
    public function readAll(ReadBuilder $builder, Request $request): ReadBuilder
    {
        return $builder
            ->buildByDbSchema(exclude: ['password'], persist: true)
            //->ignoreFormatters(['createdAt' => [DateTimeFormatter::class]])
            ->add(col: new EmailColumn(key: 'email', name: 'E-mail'));
    }
    
    public function create(WriteBuilder $builder, Request $request): WriteBuilder
    {
        $roles = $this->em->getAuthRoleRepo()->findAll();
        $items = array_map(fn($role) => new SelectField\Item($role->getId(), $role->getName()), $roles);
        
        return $builder
            ->ignoreRules(['email' => [CallableRule::class]])
            ->add(new EmailField(name: 'email', label: 'E-mail', rules: [
                new RequiredRule(),
                new UniqueRule(entityClassName: User::class, columnName: 'email', message: 'Tento e-mail je již použit.'),
                new CallableRule(fn($value) => $value === 'jz@strategio.dev', 'E-mail není jz@strategio.dev.'),
            ], serializers: [
                new CallableSerializer(function ($value) {
                    return $value;
                })
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
            ->add(new SelectField(name: 'role', label: 'Role', items: $items, rules: [
                new NullableRule(),
                new RequiredRule(),
            ]));
    }
    
    public function update(WriteBuilder $builder, Request $request): WriteBuilder
    {
        $pwf = new PasswordField(name: 'password', label: 'Heslo');
        
        // Do not show password on form rendering
        if ($request->attributes->get('_route') === 'megio.collections.form.updating') {
            $pwf->setValue(new EmptyValue());
        }
        
        return $builder
            ->add(new TextField(name: 'id', label: 'ID', disabled: true))
            ->add(new EmailField(name: 'email', label: 'E-mail'))
            ->add($pwf);
    }
}
<?php
declare(strict_types=1);

namespace App\User\Recipe;

use App\User\Database\Entity\User;
use App\User\Recipe\Formatter\SubstrFormatter;
use Megio\Collection\CollectionRecipe;
use Megio\Collection\CollectionRequest;
use Megio\Collection\Formatter\CallableFormatter;
use Megio\Collection\ReadBuilder\Column\BooleanColumn;
use Megio\Collection\ReadBuilder\Column\DateTimeColumn;
use Megio\Collection\ReadBuilder\Column\EmailColumn;
use Megio\Collection\ReadBuilder\Column\StringColumn;
use Megio\Collection\ReadBuilder\Column\ToManyColumn;
use Megio\Collection\ReadBuilder\ReadBuilder;
use Megio\Collection\SearchBuilder\Searchable;
use Megio\Collection\SearchBuilder\SearchBuilder;
use Megio\Collection\WriteBuilder\Field\Base\EmptyValue;
use Megio\Collection\WriteBuilder\Field\EmailField;
use Megio\Collection\WriteBuilder\Field\PasswordField;
use Megio\Collection\WriteBuilder\Field\TextField;
use Megio\Collection\WriteBuilder\Field\ToggleBtnField;
use Megio\Collection\WriteBuilder\Field\ToManySelectField;
use Megio\Collection\WriteBuilder\Rule\EqualRule;
use Megio\Collection\WriteBuilder\Rule\MaxRule;
use Megio\Collection\WriteBuilder\Rule\MinRule;
use Megio\Collection\WriteBuilder\Rule\NullableRule;
use Megio\Collection\WriteBuilder\Rule\RequiredRule;
use Megio\Collection\WriteBuilder\Rule\UniqueRule;
use Megio\Collection\WriteBuilder\WriteBuilder;
use Megio\Database\Entity\Auth\Role;
use Megio\Translation\Translator;

use function assert;
use function is_string;

class UserRecipe extends CollectionRecipe
{
    public function __construct(
        private readonly Translator $translator,
    ) {}

    public function source(): string
    {
        return User::class;
    }

    public function key(): string
    {
        return 'user';
    }

    public function search(
        SearchBuilder $builder,
        CollectionRequest $request,
    ): SearchBuilder {
        return $builder
            ->keepDefaults()
            ->addSearchable(
                new Searchable(
                    column: 'email',
                    operator: 'LIKE',
                    formatter: function (
                        mixed $value,
                    ): string {
                        assert(is_string($value) === true);
                        return '%' . $value . '%';
                    },
                ),
            )
            ->addSearchable(new Searchable(
                column: 'name',
                relation: 'roles',
            ));
    }

    public function read(
        ReadBuilder $builder,
        CollectionRequest $request,
    ): ReadBuilder {
        return $builder
            ->buildByDbSchema(exclude: [
                'password',
                'email',
                'lastLogin',
                'isActive',
                'isSoftDeleted',
                'activationToken',
                'resetPasswordToken',
            ], persist: true)
            ->add(
                new EmailColumn(
                    key: 'email',
                    name: $this->translator->translate('user.field.email'),
                    formatters: [
                        new CallableFormatter(function (
                            mixed $value,
                        ): string {
                            assert(is_string($value) === true);
                            return 'mailto:' . $value;
                        }),
                    ],
                ),
            )
            ->add(new DateTimeColumn(
                key: 'lastLogin',
                name: $this->translator->translate('user.field.last_login'),
            ))
            ->add(new BooleanColumn(
                key: 'isActive',
                name: $this->translator->translate('user.field.active'),
            ))
            ->add(new StringColumn(
                key: 'activationToken',
                name: $this->translator->translate('user.field.activation_token'),
            ))
            ->add(new BooleanColumn(
                key: 'isSoftDeleted',
                name: $this->translator->translate('user.field.soft_deleted'),
            ));
    }

    public function readAll(
        ReadBuilder $builder,
        CollectionRequest $request,
    ): ReadBuilder {
        return $builder
            ->buildByDbSchema(exclude: [
                'password',
                'email',
                'activationToken',
                'resetPasswordToken',
                'isActive',
                'isSoftDeleted',
            ], persist: true)
            ->add(col: new EmailColumn(
                key: 'email',
                name: $this->translator->translate('user.field.email'),
                sortable: true,
            ), moveBeforeKey: 'lastLogin')
            ->add(col: new BooleanColumn(
                key: 'isActive',
                name: $this->translator->translate('user.field.active'),
            ))
            ->add(col: new BooleanColumn(
                key: 'isSoftDeleted',
                name: $this->translator->translate('user.field.soft_deleted'),
            ))
            ->add(col: new ToManyColumn(
                key: 'roles',
                name: $this->translator->translate('user.field.roles'),
            ))
            ->add(
                col: new StringColumn(
                    key: 'activationToken',
                    name: $this->translator->translate('user.field.activation_token'),
                    formatters: [
                        new SubstrFormatter(),
                    ],
                ),
            )
            ->add(
                col: new StringColumn(
                    key: 'resetPasswordToken',
                    name: $this->translator->translate('user.field.reset_password_token'),
                    formatters: [
                        new SubstrFormatter(),
                    ],
                ),
            );

    }

    public function create(
        WriteBuilder $builder,
        CollectionRequest $request,
    ): WriteBuilder {
        return $builder
            ->add(
                new EmailField(
                    name: 'email',
                    label: $this->translator->translate('user.field.email'),
                    rules: [
                        new RequiredRule(),
                        new UniqueRule(
                            targetEntity: User::class,
                            columnName: 'email',
                            message: $this->translator->translate('user.validation.email_exists'),
                        ),
                    ],
                    attrs: ['fullWidth' => true],
                ),
            )
            ->add(
                new PasswordField(
                    name: 'password',
                    label: $this->translator->translate('user.field.password'),
                    rules: [
                        new RequiredRule(),
                        new MinRule(6),
                        new MaxRule(32),
                    ],
                ),
            )
            ->add(
                new PasswordField(
                    name: 'password_check',
                    label: $this->translator->translate('user.field.password_again'),
                    rules: [
                        new RequiredRule(),
                        new EqualRule('password'),
                    ],
                    mapToEntity: false,
                ),
            )
            ->add(new ToggleBtnField(
                name: 'isActive',
                label: $this->translator->translate('user.field.active'),
            ))
            ->add(new ToggleBtnField(
                name: 'isSoftDeleted',
                label: $this->translator->translate('user.field.soft_deleted'),
            ))
            ->add(
                new ToManySelectField(
                    name: 'roles',
                    label: $this->translator->translate('user.field.roles'),
                    reverseEntity: Role::class,
                    attrs: ['fullWidth' => true],
                ),
            );
    }

    public function update(
        WriteBuilder $builder,
        CollectionRequest $request,
    ): WriteBuilder {
        $pwf = new PasswordField(name: 'password', label: $this->translator->translate('user.field.password'));

        // Do not show password on form rendering
        if ($request->isFormRendering() === true) {
            $pwf->setValue(new EmptyValue());
        }

        return $builder
            ->add(new TextField(
                name: 'id',
                label: $this->translator->translate('app.field.id'),
                attrs: ['fullWidth' => true],
                disabled: true,
            ))
            ->add(new EmailField(
                name: 'email',
                label: $this->translator->translate('user.field.email'),
            ))
            ->add($pwf)
            ->add(new ToggleBtnField(
                name: 'isActive',
                label: $this->translator->translate('user.field.active'),
            ))
            ->add(new ToggleBtnField(
                name: 'isSoftDeleted',
                label: $this->translator->translate('user.field.soft_deleted'),
            ))
            ->add(
                new TextField(
                    name: 'activationToken',
                    label: $this->translator->translate('user.field.activation_token'),
                    rules: [
                        new NullableRule(),
                    ],
                    attrs: ['fullWidth' => true],
                ),
            )
            ->add(
                new TextField(
                    name: 'resetPasswordToken',
                    label: $this->translator->translate('user.field.reset_password_token'),
                    rules: [
                        new NullableRule(),
                    ],
                    attrs: ['fullWidth' => true],
                ),
            )
            ->add(
                new ToManySelectField(
                    name: 'roles',
                    label: $this->translator->translate('user.field.roles'),
                    reverseEntity: Role::class,
                    attrs: ['fullWidth' => true],
                ),
            );
    }
}

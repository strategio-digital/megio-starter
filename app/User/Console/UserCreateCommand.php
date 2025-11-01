<?php
declare(strict_types=1);

namespace App\User\Console;

use App\User\Database\Entity\User;
use Megio\Database\Entity\Auth\Resource;
use Megio\Database\Entity\Auth\Role;
use Megio\Database\EntityManager;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

use function assert;
use function is_string;

#[AsCommand(name: 'app:user:create', description: 'Create a new user account.', aliases: ['user'])]
class UserCreateCommand extends Command
{
    public function __construct(private readonly EntityManager $em)
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addArgument('email', InputArgument::REQUIRED, 'E-mail');
        $this->addArgument('password', InputArgument::REQUIRED, 'Password');
        $this->addOption('role', 'r', InputArgument::OPTIONAL, 'Create role with all permissions');
    }

    protected function execute(
        InputInterface $input,
        OutputInterface $output,
    ): int {
        $email = $input->getArgument('email');
        $passwd = $input->getArgument('password');
        $roleName = $input->getOption('role');

        assert(is_string($email) === true);
        assert(is_string($passwd) === true);
        assert(is_string($roleName) === true || $roleName === null);

        $user = new User();

        if ($roleName !== null) {
            $role = $this->em->getAuthRoleRepo()->findOneBy(['name' => $roleName]);

            if ($role === null) {
                $role = new Role();
                $role->setName($roleName);

                $resources = $this->em->getRepository(Resource::class)->findAll();
                foreach ($resources as $resource) {
                    $role->addResource($resource);
                }

                $this->em->persist($role);
            }

            $user->addRole($role);
        }

        $user->setEmail($email);
        $user->setPassword($passwd);

        $this->em->persist($user);
        $this->em->flush();

        $output->writeln('<info>User successfully created.</info>');

        return Command::SUCCESS;
    }
}

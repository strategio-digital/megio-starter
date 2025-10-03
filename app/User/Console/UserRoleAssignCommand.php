<?php
declare(strict_types=1);

namespace App\User\Console;

use Megio\Database\Entity\Auth\Resource;
use Megio\Database\Entity\Auth\Role;
use Megio\Database\EntityManager;
use Megio\Database\Enum\ResourceType;
use Megio\Database\Manager\AuthResourceManager;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'app:user:role:assign', description: 'Assign authenticated routes to user role.')]
class UserRoleAssignCommand extends Command
{
    public const string DEFAULT_ROLE_NAME = 'user';

    public function __construct(
        private readonly EntityManager $em,
        private readonly AuthResourceManager $authResourceManager,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $role = $this->em->getAuthRoleRepo()->findOneBy(['name' => self::DEFAULT_ROLE_NAME]);

        if ($role === null) {
            $role = new Role();
            $role->setName(self::DEFAULT_ROLE_NAME);
            $this->em->persist($role);
            $output->writeln('<info>Created user role.</info>');
        }

        $routeNames = $this->authResourceManager->routerResources();
        $assignedCount = 0;

        foreach ($routeNames as $routeName) {
            $resource = $this->em->getAuthResourceRepo()->findOneBy(['name' => $routeName]);

            if ($resource === null) {
                $resource = new Resource();
                $resource->setName($routeName);
                $resource->setType(ResourceType::ROUTER);
                $this->em->persist($resource);
            }

            if ($role->getResources()->contains($resource) === false) {
                $role->addResource($resource);
                $assignedCount++;
                $output->writeln("<info>Assigned route '{$routeName}' to user role.</info>");
            }
        }

        $this->em->flush();

        if ($assignedCount === 0) {
            $output->writeln('<comment>No new authenticated routes to assign to user role.</comment>');
            return Command::SUCCESS;
        }

        $output->writeln("<info>Successfully assigned {$assignedCount} authenticated routes to user role.</info>");
        return Command::SUCCESS;
    }
}

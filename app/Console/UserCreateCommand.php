<?php
/**
 * Copyright (c) 2023 Strategio Digital s.r.o.
 * @author Jiří Zapletal (https://strategio.dev, jz@strategio.dev)
 */
declare(strict_types=1);

namespace App\Console;

use App\Database\Entity\User;
use Saas\Database\Entity\Auth\Resource;
use Saas\Database\Entity\Auth\Role;
use Saas\Database\EntityManager;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'app:user:create', description: 'Create a new user account', aliases: ['user'])]
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
    
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $email = $input->getArgument('email');
        $passwd = $input->getArgument('password');
        $roleName = $input->getOption('role');
        
        $user = new User();
        
        if ($roleName) {
            /** @var Role|null $role */
            $role = $this->em->getAuthRoleRepo()->findOneBy(['name' => $roleName]);
            
            if (!$role) {
                $role = new Role();
                $role->setName($roleName);
                
                /** @var Resource[] $resources */
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
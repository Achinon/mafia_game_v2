<?php

namespace App\Command;

use App\Entity\PostFactory;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use App\Utils\Utils;
use App\Domain\RoleInterface;
use App\Domain\Roles;
use App\Entity\Role;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use App\Service\RoleLoader;

#[AsCommand(
  name: 'app:populate-roles-table',
)]
class PopulateRolesTableCommand extends Command
{
    public function __construct(private readonly RoleLoader $role_loader)
    {
        parent::__construct();


        $this->setDescription(sprintf('Add to the database records based on %s', RoleLoader::NamespaceLoaded));
    }

    /**
     * @throws \Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->role_loader->load();

        return Command::SUCCESS;
    }
}

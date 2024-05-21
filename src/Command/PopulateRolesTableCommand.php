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

#[AsCommand(
  name: 'app:populate-roles-table',
  description: 'Add to the database records based on App/Domain/Roles',
)]
class PopulateRolesTableCommand extends Command
{
    public function __construct(private readonly EntityManagerInterface $entity_manager)
    {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $classes = $this->getClassesInNamespace();
        $namespace = "App\\Domain\\Roles";
        foreach ($classes as $className) {
            /** @var RoleInterface|string $class */
            $class = "$namespace\\$className";
            $description = $class::getDescription();

            $role = new Role();
            $role->setName($className)
                ->setDescription($description);

            $this->entity_manager->persist($role);
            try {
                $this->entity_manager->flush();
                $io->success(sprintf('Role "%s" has been added to the database.', $class));
            } catch (\Exception $e) {
                $io->warning(sprintf('Role "%s" already exists in the database.', $class));
                $this->entity_manager->clear();
            }
        }

        return Command::SUCCESS;
    }

    private function getClassesInNamespace(): array
    {
        $classes = [];
        $path = __DIR__ . '/../Domain/Roles/';
        $files = scandir($path);

        foreach ($files as $file) {
            if ($file !== '.' && $file !== '..' && pathinfo($file, PATHINFO_EXTENSION) === 'php') {
                $classes[] = pathinfo($file, PATHINFO_FILENAME);
            }
        }

        return $classes;
    }
}

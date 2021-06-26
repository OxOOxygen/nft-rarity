<?php

namespace App\Command;

use App\Entity\Project;
use App\Repository\ProjectRepository;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class CreateProjectCommand extends Command
{
    protected static $defaultName = 'app:create-project';
    protected static $defaultDescription = 'Creates a new project';

    private ProjectRepository $projectRepository;

    public function __construct(ProjectRepository $projectRepository)
    {
        parent::__construct();

        $this->projectRepository = $projectRepository;
    }


    protected function configure(): void
    {
        $this
            ->addArgument('name', InputArgument::REQUIRED, 'project name')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $name = $input->getArgument('name');

        $project = new Project();
        $project->setName($name);

        $this->projectRepository->save($project);

        $io->success('You have a new command! Now make it your own! Pass --help to see your options.');

        return Command::SUCCESS;
    }
}

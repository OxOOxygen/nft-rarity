<?php

namespace App\Command;

use App\Entity\Asset;
use App\Document\Asset as AssetDocument;
use App\Repository\AssetRepository;
use Elastica\Client;
use Elastica\Document;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class EsDocumentsCreateCommand extends Command
{
    protected static $defaultName = 'app:es:documents:create';
    protected static $defaultDescription = 'Add a short description for your command';

    private Client $client;
    private AssetRepository $assetRepository;

    public function __construct(Client $client, AssetRepository $assetRepository)
    {
        parent::__construct();

        $this->client = $client;
        $this->assetRepository = $assetRepository;
    }

    protected function configure(): void
    {
        $this
            ->addArgument('project-id', InputArgument::REQUIRED, 'Project id')
            ->addArgument('index-name', InputArgument::REQUIRED, 'Index name')
            ->addOption('bulk', null, InputOption::VALUE_REQUIRED, 'Option description', 500)
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $index = $this->client->getIndex($input->getArgument('index-name'));

        $queryBuilder = $this->assetRepository->getQueryForProject($input->getArgument('project-id'));

        $documents = [];

        /** @var Asset $asset */
        foreach ($queryBuilder->getQuery()->toIterable() as $asset) {
            $documents[] = new Document($asset->getId(), AssetDocument::fromEntity($asset)->toArray());
        }

        $index->addDocuments($documents);

        $index->refresh();

        return Command::SUCCESS;
    }
}

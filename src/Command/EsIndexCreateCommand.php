<?php

namespace App\Command;

use Elastica\Client;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class EsIndexCreateCommand extends Command
{
    protected static $defaultName = 'app:es:index:create';
    protected static $defaultDescription = 'Creates an elasticsearch index';

    private Client $client;

    public function __construct(Client $client)
    {
        parent::__construct();

        $this->client = $client;
    }


    protected function configure(): void
    {
        $this
            ->addArgument('index-name', InputArgument::REQUIRED, 'Index name')
            ->addOption('force', null, InputOption::VALUE_NONE, 'Overwrites existing index')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $index = $this->client->getIndex($input->getArgument('index-name'));

        $index->create(
            [
                'mappings' => [
                    'properties' => [
                        'attributes' => [
                            'properties' => [
                                'trait_type' => [
                                    'type' => 'text',
                                    'fields' => [
                                        'raw' => ['type' => 'keyword'],
                                    ],
                                ],
                                'value' => [
                                    'type' => 'text',
                                    'fields' => [
                                        'raw' => ['type' => 'keyword'],
                                    ],
                                ],
                            ]
                        ]
                    ]
                ],
                'settings' => [
                    'index' => [
                        'number_of_shards' => 4,
                        'number_of_replicas' => 1,
                    ]
                ],
            ],
            [
                'recreate' => $input->getOption('force')
            ]
        );

        return Command::SUCCESS;
    }
}

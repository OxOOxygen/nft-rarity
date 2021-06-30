<?php

namespace App\Command;

use App\Entity\Asset;
use App\Entity\Attribute;
use App\Entity\Metadata;
use App\Repository\AssetRepository;
use App\Repository\AttributeRepository;
use App\Repository\MetadataRepository;
use App\Repository\ProjectRepository;
use JsonException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class CreateAssetsFromIpfsCommand extends Command
{
    protected static $defaultName = 'app:create-assets-from-ipfs';
    protected static $defaultDescription = 'Creating assets by ipfs links';

    private ProjectRepository $projectRepository;
    private AssetRepository $assetRepository;
    private MetadataRepository $metadataRepository;
    private AttributeRepository $attributeRepository;

    public function __construct(ProjectRepository $projectRepository, AssetRepository $assetRepository, MetadataRepository $metadataRepository, AttributeRepository $attributeRepository)
    {
        parent::__construct();

        $this->projectRepository = $projectRepository;
        $this->assetRepository = $assetRepository;
        $this->metadataRepository = $metadataRepository;
        $this->attributeRepository = $attributeRepository;
    }

    protected function configure(): void
    {
        $this
            ->addArgument('link', InputArgument::REQUIRED, 'https://ipfs.io/ipfs/QmbumZq4f81hc2KsVWMMH2AmRpw7nSwX3KBsjABewabNnj/{id}.json')
            ->addArgument('project-id', InputArgument::REQUIRED)
            ->addArgument('id-end', null, InputArgument::REQUIRED, 'id end')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $idCount = (int) $input->getArgument('id-end');

        $project = $this->projectRepository->find($input->getArgument('project-id'));

        while ($project !== null && $idCount !== 0) {
            $ipfsLink = str_replace('{id}', $idCount, $input->getArgument('link'));

            if ($this->metadataRepository->findOneBy(['url' => $ipfsLink]) !== null) {
                $idCount--;
                continue;
            }

            $output->writeln('Retrieving data from ' . $ipfsLink);
            $ipfsJson = @file_get_contents($ipfsLink);

            while (!$ipfsJson) {
                $ipfsJson = @file_get_contents($ipfsLink);
            }

            if (!$ipfsJson) {
                $output->writeln('No data found');
                $idCount--;
                continue;
            }

            $metadata = new Metadata();
            $metadata->setProject($project);
            $metadata->setUrl($ipfsLink);

            try {
                $ipfsData = json_decode($ipfsJson, true, 512, JSON_THROW_ON_ERROR);
                if (isset($ipfsData['error'])) {
                    $output->writeln($ipfsData['error']);
                    $idCount--;
                    continue;
                }
                $output->writeln('Data found: ' . var_export($ipfsData, true));
                $metadata->setData($ipfsData);
            } catch (JsonException $jsonException) {
                $idCount--;
                continue;
            }

            $this->metadataRepository->save($metadata);

            $asset = new Asset();
            $asset->setName($project->getName() . ' #'.$idCount);
            $asset->setMetadata($metadata);
            $asset->setImage($ipfsData['image']);
            $asset->setProject($project);

            foreach ($ipfsData['attributes'] as $attributeData) {
                $attribute = new Attribute();
                $attribute->setType($attributeData['trait_type']);
                $attribute->setValue($attributeData['value']);
                $attribute->setProject($project);

                $attribute = $this->attributeRepository->createOrGet($attribute);

                $asset->addAttribute($attribute);
            }

            $this->assetRepository->save($asset);

            $idCount--;
        }


        $io->success('You have a new command! Now make it your own! Pass --help to see your options.');

        return Command::SUCCESS;
    }
}

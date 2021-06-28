<?php

namespace App\Controller;

use App\Repository\AssetRepository;
use App\Repository\AttributeRepository;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;

class AssetListController extends AbstractController
{
    private AssetRepository $assetRepository;
    private AttributeRepository $attributeRepository;

    public function __construct(AssetRepository $assetRepository, AttributeRepository $attributeRepository)
    {
        $this->assetRepository = $assetRepository;
        $this->attributeRepository = $attributeRepository;
    }


    /**
     * @Route("/", name="asset_list")
     */
    public function index(PaginatorInterface $paginator, Request $request): Response
    {
        $projectId = $request->get('project-id');

        $projectId = 1;

        if ($projectId === null) {
            throw new NotFoundHttpException('Missing project-id');
        }

        $assetsQuery = $this->assetRepository->getQueryForProject($projectId);

        $pagination = $paginator->paginate(
            $assetsQuery, /* query NOT result */
            $request->query->getInt('page', 1), /*page number*/
            $request->query->getInt('limit', 10) /*limit per page*/
        );

        $attributeValues = [];
        $attributes = array_merge(...$this->attributeRepository->getDistinctTypes($projectId));

        foreach ($attributes as $attribute) {
            $attributeValues[$attribute] = array_merge(...$this->attributeRepository->getDistinctValuesForType($projectId, $attribute));
        }

        return $this->render('asset_list/index.html.twig', [
            'assets' => [],
            'attributes' => $attributes,
            'attributeValues' => $attributeValues,
            'rarities' => $this->attributeRepository->getRarities($projectId),
            'pagination' => $pagination,
            'controller_name' => 'AssetListController',
        ]);
    }
}

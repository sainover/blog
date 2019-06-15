<?php

declare(strict_types=1);

namespace App\Controller;

use App\Service\TagService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class TagController extends AbstractController
{
    /**
     * @Route("/tags", name="tags")
     */
    public function tags(TagService $tagService): JsonResponse
    {
        return new JsonResponse($tagService->getAllAsArray(), Response::HTTP_OK);
    }
}

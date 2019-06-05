<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use App\Service\TagService;
use Symfony\Component\HttpFoundation\Response;

class TagController extends AbstractController
{
    /**
     * @Route("/tags", name="tags", methods={"GET"})
     */
    public function tags(TagService $tagService): JsonResponse
    {
        return new JsonResponse($tagService->getTagsAsArray(), Response::HTTP_OK);
    }
}
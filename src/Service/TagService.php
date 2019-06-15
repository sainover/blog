<?php

declare(strict_types=1);

namespace App\Service;

use App\Repository\TagRepository;

class TagService
{
    private $tagRepository;

    public function __construct(TagRepository $tagRepository)
    {
        $this->tagRepository = $tagRepository;
    }

    public function getAllAsArray(): array
    {
        $data['results'] = [];

        $tags = $this->tagRepository->findAll();
        foreach ($tags as $tag) {
            $data['results'][] = [
                'id' => $tag->getId(),
                'text' => $tag->getName(),
            ];
        }

        return $data;
    }
}

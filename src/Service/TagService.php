<?php

namespace App\Service;

use App\Repository\TagRepository;

class TagService
{
    private $tagRepository;

    public function __construct(TagRepository $tagRepository)
    {
        $this->tagRepository = $tagRepository;
    }

    public function getTagsAsArray()
    {
        $data['results'] = [];
        $tags = $this->tagRepository->findAll();
        foreach($tags as $tag) {
            $item['id'] = $tag->getId();
            $item['text'] = $tag->getName();
            $data['results'][] = $item;
        }
        return $data;
    }
}
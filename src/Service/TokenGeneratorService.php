<?php

declare(strict_types=1);

namespace App\Service;

class TokenGeneratorService
{
    public function generate(): ?string
    {
        $token = '';
        $token = time().'_'.uniqid('', true);

        return $token;
    }
}

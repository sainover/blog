<?php

namespace App\Service;

class TokenGeneratorService
{
    public function generate(): ?string
    {
        $token = '';
        $token = time() . '_' . uniqid('', TRUE);
        return $token;
    }
}
<?php

declare(strict_types=1);

namespace Yivoff\JwtRefreshBundle\Contracts;

interface TokenIdGeneratorInterface
{

    public function generateIdentifier(int $length): string;

    public function generateVerifier(int $length): string;
}

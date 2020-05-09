<?php declare(strict_types=1);

namespace Yivoff\JwtRefresh\Contracts;


interface IdGeneratorInterface
{

    public function generateIdentifier(): string;

    public function generateVerifier(): string;

}

<?php

declare(strict_types=1);

namespace Yivoff\JwtRefreshBundle\Exception;

use Exception;

class TokenInvalidException extends JwtRefreshException
{
    public function __construct(?string $tokenId, ?string $userIdentifier, ?Exception $previous = null)
    {
        parent::__construct($tokenId, $userIdentifier, FailType::INVALID, $previous);
    }
}

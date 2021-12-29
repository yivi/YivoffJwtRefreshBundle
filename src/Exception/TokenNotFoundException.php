<?php

declare(strict_types=1);

namespace Yivoff\JwtRefreshBundle\Exception;

use Exception;

class TokenNotFoundException extends JwtRefreshException
{
    public function __construct(?string $tokenId, ?string $userIdentifier, ?Exception $previous = null)
    {
        parent::__construct($tokenId, $userIdentifier, FailType::NOT_FOUND, $previous);
    }
}

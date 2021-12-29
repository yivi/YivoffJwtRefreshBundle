<?php

declare(strict_types=1);

namespace Yivoff\JwtRefreshBundle\Exception;

use Exception;
use Symfony\Component\Security\Core\Exception\AuthenticationException;

abstract class JwtRefreshException extends AuthenticationException
{
    public function __construct(public readonly ?string $tokenId, public readonly ?string $userIdentifier, public readonly FailType $failType, ?Exception $previous = null)
    {
        $message = $this->failType->value.(null !== $tokenId ? '. Token Id: '.$tokenId : '').(null !== $userIdentifier ? '. User Id: '.$userIdentifier : '');

        parent::__construct($message, 0, $previous);
    }
}

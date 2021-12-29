<?php

declare(strict_types=1);

namespace Yivoff\JwtRefreshBundle\Exception;

enum FailType: string
{
    case PAYLOAD = 'Invalid Payload';

    case INVALID = 'Invalid Token';

    case EXPIRED = 'Expired Token';

    case NOT_FOUND = 'Token not found';
}

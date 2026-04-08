<?php

declare(strict_types=1);

namespace App\Exceptions;

use Symfony\Component\HttpFoundation\Response;

class UnauthorizedException extends AppException
{
    public function __construct(string $message = 'Unauthorized.')
    {
        parent::__construct($message, Response::HTTP_UNAUTHORIZED);
    }
}

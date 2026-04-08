<?php

declare(strict_types=1);

namespace App\Exceptions;

use Symfony\Component\HttpFoundation\Response;

class NotFoundException extends AppException
{
    public function __construct(string $resource = 'Resource')
    {
        parent::__construct("{$resource} not found.", Response::HTTP_NOT_FOUND);
    }
}

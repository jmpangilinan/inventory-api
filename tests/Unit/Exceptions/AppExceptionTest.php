<?php

declare(strict_types=1);

namespace Tests\Unit\Exceptions;

use App\Exceptions\AppException;
use App\Exceptions\BusinessException;
use App\Exceptions\NotFoundException;
use App\Exceptions\UnauthorizedException;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Component\HttpFoundation\Response;
use Tests\TestCase;

class AppExceptionTest extends TestCase
{
    #[Test]
    public function not_found_exception_has_correct_message_and_code(): void
    {
        $exception = new NotFoundException('Product');

        $this->assertSame('Product not found.', $exception->getMessage());
        $this->assertSame(Response::HTTP_NOT_FOUND, $exception->getCode());
        $this->assertInstanceOf(AppException::class, $exception);
    }

    #[Test]
    public function not_found_exception_uses_default_resource_name(): void
    {
        $exception = new NotFoundException;

        $this->assertSame('Resource not found.', $exception->getMessage());
    }

    #[Test]
    public function business_exception_has_correct_message_and_code(): void
    {
        $exception = new BusinessException('Insufficient stock.');

        $this->assertSame('Insufficient stock.', $exception->getMessage());
        $this->assertSame(Response::HTTP_UNPROCESSABLE_ENTITY, $exception->getCode());
        $this->assertInstanceOf(AppException::class, $exception);
    }

    #[Test]
    public function unauthorized_exception_has_correct_message_and_code(): void
    {
        $exception = new UnauthorizedException;

        $this->assertSame('Unauthorized.', $exception->getMessage());
        $this->assertSame(Response::HTTP_UNAUTHORIZED, $exception->getCode());
        $this->assertInstanceOf(AppException::class, $exception);
    }

    #[Test]
    public function unauthorized_exception_accepts_custom_message(): void
    {
        $exception = new UnauthorizedException('Invalid device signature.');

        $this->assertSame('Invalid device signature.', $exception->getMessage());
    }
}

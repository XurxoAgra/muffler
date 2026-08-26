<?php

declare(strict_types=1);

namespace App\Maintenance\Infrastructure\Http\Request;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ValueResolverInterface;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;
use Symfony\Component\Validator\Validator\ValidatorInterface;

final class MaintenanceRecordRequestValueResolver implements ValueResolverInterface
{
    public function __construct(private readonly ValidatorInterface $validator)
    {
    }

    public function resolve(Request $request, ArgumentMetadata $argument): iterable
    {
        if (MaintenanceRecordRequest::class !== $argument->getType()) {
            return [];
        }

        return [new MaintenanceRecordRequest($request, $this->validator)];
    }
}

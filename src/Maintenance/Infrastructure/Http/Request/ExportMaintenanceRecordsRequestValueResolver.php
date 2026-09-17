<?php

declare(strict_types=1);

namespace App\Maintenance\Infrastructure\Http\Request;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ValueResolverInterface;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;
use Symfony\Component\Validator\Validator\ValidatorInterface;

final readonly class ExportMaintenanceRecordsRequestValueResolver implements ValueResolverInterface
{
    public function __construct(private ValidatorInterface $validator)
    {
    }

    public function resolve(Request $request, ArgumentMetadata $argument): iterable
    {
        if (ExportMaintenanceRecordsRequest::class !== $argument->getType()) {
            return [];
        }

        return [new ExportMaintenanceRecordsRequest($request, $this->validator)];
    }
}

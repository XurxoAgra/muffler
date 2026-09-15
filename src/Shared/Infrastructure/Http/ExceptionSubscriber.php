<?php

declare(strict_types=1);

namespace App\Shared\Infrastructure\Http;

use App\Auth\Domain\Exception\DomainException;
use App\Auth\Domain\User\Exception\InvalidEmailException;
use App\Auth\Domain\User\Exception\UserAlreadyExistsException;
use App\Auth\Domain\User\Exception\UserNotFoundException;
use App\Maintenance\Domain\Exception\InvoiceNotBelongingToVehicleException;
use App\Maintenance\Domain\Exception\InvoiceNotFoundException;
use App\Maintenance\Domain\Exception\MaintenanceRecordNotFoundException;
use App\Maintenance\Domain\Exception\MaintenanceRecordTypeInactiveException;
use App\Maintenance\Domain\Exception\MaintenanceRecordTypeNotFoundException;
use App\Shared\Application\Exception\ValidationException;
use App\Vehicle\Domain\Exception\CannotRevokeOwnerException;
use App\Vehicle\Domain\Exception\InvalidMileageException;
use App\Vehicle\Domain\Exception\InvitedUserNotFoundException;
use App\Vehicle\Domain\Exception\MileageRegressionException;
use App\Vehicle\Domain\Exception\VehicleAccessDeniedException;
use App\Vehicle\Domain\Exception\VehicleMakeNotFoundException;
use App\Vehicle\Domain\Exception\VehicleNotFoundException;
use App\Vehicle\Domain\Exception\VehicleUserAlreadyExistsException;
use App\Vehicle\Domain\Exception\VehicleUserNotFoundException;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

#[AsEventListener(event: KernelEvents::EXCEPTION)]
final class ExceptionSubscriber
{
    private const MAP = [
        UserAlreadyExistsException::class => [409, 'EMAIL_TAKEN'],
        UserNotFoundException::class => [404, 'USER_NOT_FOUND'],
        InvalidEmailException::class => [400, 'INVALID_EMAIL'],
        VehicleNotFoundException::class => [404, 'VEHICLE_NOT_FOUND'],
        VehicleMakeNotFoundException::class => [404, 'VEHICLE_MAKE_NOT_FOUND'],
        VehicleAccessDeniedException::class => [403, 'VEHICLE_ACCESS_DENIED'],
        InvalidMileageException::class => [400, 'INVALID_MILEAGE'],
        MileageRegressionException::class => [409, 'MILEAGE_REGRESSION'],
        CannotRevokeOwnerException::class => [400, 'CANNOT_REVOKE_OWNER'],
        InvitedUserNotFoundException::class => [404, 'INVITED_USER_NOT_FOUND'],
        VehicleUserAlreadyExistsException::class => [409, 'VEHICLE_USER_ALREADY_EXISTS'],
        VehicleUserNotFoundException::class => [404, 'VEHICLE_USER_NOT_FOUND'],
        MaintenanceRecordNotFoundException::class => [404, 'MAINTENANCE_RECORD_NOT_FOUND'],
        InvoiceNotFoundException::class => [404, 'INVOICE_NOT_FOUND'],
        InvoiceNotBelongingToVehicleException::class => [400, 'INVOICE_NOT_BELONGING_TO_VEHICLE'],
        MaintenanceRecordTypeNotFoundException::class => [404, 'MAINTENANCE_RECORD_TYPE_NOT_FOUND'],
        MaintenanceRecordTypeInactiveException::class => [400, 'MAINTENANCE_RECORD_TYPE_INACTIVE'],
    ];

    public function __invoke(ExceptionEvent $event): void
    {
        $exception = $event->getThrowable();

        if ($exception instanceof AccessDeniedException || $exception instanceof AccessDeniedHttpException) {
            $event->setResponse(new JsonResponse([
                'error' => [
                    'code' => 'ACCESS_DENIED',
                    'message' => 'Access denied',
                ],
            ], 403));

            return;
        }

        // Let Symfony handle HTTP exceptions normally (404, 405, etc.)
        if ($exception instanceof HttpExceptionInterface) {
            return;
        }

        if ($exception instanceof ValidationException) {
            $event->setResponse(new JsonResponse([
                'error' => [
                    'code' => 'VALIDATION_ERROR',
                    'message' => $exception->getMessage(),
                    'details' => $exception->details(),
                ],
            ], 422));

            return;
        }

        [$status, $code] = $this->resolveStatus($exception);

        $event->setResponse(new JsonResponse([
            'error' => [
                'code' => $code,
                'message' => $exception->getMessage(),
            ],
        ], $status));
    }

    private function resolveStatus(\Throwable $e): array
    {
        foreach (self::MAP as $class => [$status, $code]) {
            if ($e instanceof $class) {
                return [$status, $code];
            }
        }

        return $e instanceof DomainException
            ? [422, 'DOMAIN_ERROR']
            : [500, 'INTERNAL_ERROR'];
    }
}

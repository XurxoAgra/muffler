<?php

declare(strict_types=1);

namespace App\Auth\Infrastructure\EventListener;

use App\Auth\Application\SendEmailVerification\SendEmailVerificationCommand;
use App\Auth\Application\SendEmailVerification\SendEmailVerificationHandler;
use App\Auth\Domain\Event\UserWasRegistered;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;

// Sent synchronously. A mail transport failure must not undo the
// registration: it is logged and the user can request a new link.
#[AsEventListener]
final readonly class SendVerificationEmailOnRegistration
{
    public function __construct(
        private SendEmailVerificationHandler $handler,
        private LoggerInterface $logger,
    ) {
    }

    public function __invoke(UserWasRegistered $event): void
    {
        try {
            $this->handler->handle(new SendEmailVerificationCommand($event->email->value()));
        } catch (TransportExceptionInterface $e) {
            $this->logger->error('Could not send verification email', [
                'user_id' => $event->userId->value(),
                'exception' => $e,
            ]);
        }
    }
}

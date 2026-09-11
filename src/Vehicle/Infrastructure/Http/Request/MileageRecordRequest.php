<?php

declare(strict_types=1);

namespace App\Vehicle\Infrastructure\Http\Request;

use App\Shared\Application\Exception\ValidationException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Validator\ValidatorInterface;

final readonly class MileageRecordRequest
{
    public int $mileage;
    public \DateTimeImmutable $recordedAt;

    public function __construct(Request $request, ValidatorInterface $validator)
    {
        $data = json_decode($request->getContent(), true) ?? [];

        $this->validate($data, $validator);

        $this->mileage = (int) $data['mileage'];
        $this->recordedAt = !empty($data['recordedAt'])
            ? new \DateTimeImmutable($data['recordedAt'])
            : new \DateTimeImmutable();
    }

    private function validate(array $data, ValidatorInterface $validator): void
    {
        $violations = $validator->validate($data, new Assert\Collection([
            'mileage' => [
                new Assert\NotNull(),
                new Assert\Type('integer'),
                new Assert\PositiveOrZero(),
            ],
            'recordedAt' => new Assert\Optional([
                new Assert\DateTime(
                    format: \DateTimeInterface::ATOM,
                    message: 'Must be an ISO-8601 date-time, e.g. 2026-05-01T10:30:00+00:00',
                ),
            ]),
        ]));

        $details = [];
        foreach ($violations as $violation) {
            $details[$violation->getPropertyPath()][] = $violation->getMessage();
        }

        if (count($details) > 0) {
            throw new ValidationException('Invalid input', $details);
        }
    }
}

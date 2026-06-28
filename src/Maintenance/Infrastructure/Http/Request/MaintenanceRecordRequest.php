<?php

declare(strict_types=1);

namespace App\Maintenance\Infrastructure\Http\Request;

use App\Shared\Application\Exception\ValidationException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Validator\ValidatorInterface;

final readonly class MaintenanceRecordRequest
{
    public \DateTimeImmutable $serviceDate;
    public string $type;
    public ?int $mileage;
    public ?string $notes;
    public ?string $cost;
    public ?string $shopName;
    public ?\DateTimeImmutable $nextServiceDate;
    public ?string $invoiceId;

    public function __construct(Request $request, ValidatorInterface $validator)
    {
        $data = json_decode($request->getContent(), true) ?? [];

        $this->validate($data, $validator);

        $this->serviceDate = new \DateTimeImmutable($data['serviceDate']);
        $this->type = trim($data['type']);
        $this->mileage = isset($data['mileage']) ? (int) $data['mileage'] : null;
        $this->notes = !empty($data['notes']) ? trim($data['notes']) : null;
        $this->cost = !empty($data['cost']) ? (string) $data['cost'] : null;
        $this->shopName = !empty($data['shopName']) ? trim($data['shopName']) : null;
        $this->nextServiceDate = !empty($data['nextServiceDate']) ? new \DateTimeImmutable($data['nextServiceDate']) : null;
        $this->invoiceId = !empty($data['invoiceId']) ? trim($data['invoiceId']) : null;
    }

    private function validate(array $data, ValidatorInterface $validator): void
    {
        $decimalConstraint = new Assert\Regex(
            '/^\d+(\.\d{1,2})?$/',
            message: 'Must be a non-negative number with up to 2 decimal places',
        );

        $violations = $validator->validate($data, new Assert\Collection([
            'serviceDate' => [new Assert\NotBlank(), new Assert\Date()],
            'type' => [new Assert\NotBlank(), new Assert\Length(max: 100)],
            'mileage' => new Assert\Optional([new Assert\Type('integer'), new Assert\PositiveOrZero()]),
            'notes' => new Assert\Optional([new Assert\Type('string')]),
            'cost' => new Assert\Optional([$decimalConstraint]),
            'shopName' => new Assert\Optional([new Assert\Length(max: 255)]),
            'nextServiceDate' => new Assert\Optional([new Assert\Date()]),
            'invoiceId' => new Assert\Optional([new Assert\Uuid()]),
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

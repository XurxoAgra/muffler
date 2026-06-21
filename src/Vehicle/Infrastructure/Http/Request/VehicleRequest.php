<?php

declare(strict_types=1);

namespace App\Vehicle\Infrastructure\Http\Request;

use App\Shared\Application\Exception\ValidationException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Validator\ValidatorInterface;

final readonly class VehicleRequest
{
    public string $plate;
    public int $year;
    public string $type;
    public ?string $vin;
    public ?string $makeId;
    public ?string $modelId;
    public ?string $customMake;
    public ?string $customModel;

    public function __construct(Request $request, ValidatorInterface $validator)
    {
        $data = json_decode($request->getContent(), true) ?? [];

        $this->validate($data, $validator);

        $this->plate = trim($data['plate'] ?? '');
        $this->year = (int) ($data['year'] ?? 0);
        $this->type = trim($data['type'] ?? '');
        $this->vin = !empty($data['vin']) ? strtoupper(trim($data['vin'])) : null;
        $this->makeId = !empty($data['makeId']) ? strtolower(trim($data['makeId'])) : null;
        $this->modelId = !empty($data['modelId']) ? strtolower(trim($data['modelId'])) : null;
        $this->customMake = !empty($data['customMake']) ? trim($data['customMake']) : null;
        $this->customModel = !empty($data['customModel']) ? trim($data['customModel']) : null;
    }

    private function validate(array $data, ValidatorInterface $validator): void
    {
        $violations = $validator->validate($data, new Assert\Collection([
            'plate' => [new Assert\NotBlank(), new Assert\Length(max: 20)],
            'year' => [new Assert\NotBlank(), new Assert\Type('integer')],
            'type' => [new Assert\NotBlank(), new Assert\Length(max: 50)],
            'vin' => new Assert\Optional([
                new Assert\Regex(
                    '/^[A-HJ-NPR-Z0-9]{17}$/i',
                    message: 'VIN must be 17 alphanumeric characters (I, O and Q are not allowed)',
                ),
            ]),
            'makeId' => new Assert\Optional([new Assert\Uuid()]),
            'modelId' => new Assert\Optional([new Assert\Uuid()]),
            'customMake' => new Assert\Optional([new Assert\Length(max: 255)]),
            'customModel' => new Assert\Optional([new Assert\Length(max: 255)]),
        ]));

        $details = [];
        foreach ($violations as $violation) {
            $details[$violation->getPropertyPath()][] = $violation->getMessage();
        }

        $hasCatalogMake = !empty($data['makeId']) || !empty($data['modelId']);
        $hasCustomMake = !empty($data['customMake']) || !empty($data['customModel']);

        if (!$hasCatalogMake && !$hasCustomMake) {
            $details['makeId'][] = 'Either makeId/modelId or customMake/customModel must be provided';
        }

        if (count($details) > 0) {
            throw new ValidationException('Invalid input', $details);
        }
    }
}

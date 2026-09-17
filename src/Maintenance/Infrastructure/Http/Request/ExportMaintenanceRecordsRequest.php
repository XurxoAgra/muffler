<?php

declare(strict_types=1);

namespace App\Maintenance\Infrastructure\Http\Request;

use App\Shared\Application\Exception\ValidationException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Validator\ValidatorInterface;

final readonly class ExportMaintenanceRecordsRequest
{
    private const LABEL_MAX_LENGTH = 100;

    /** Raw value; the controller maps it to ExportFormat and answers 400 when it is not supported. */
    public string $format;

    /** @var array<string, string> maintenance record type key => display label */
    public array $labels;

    public function __construct(Request $request, ValidatorInterface $validator)
    {
        $query = $request->query->all();

        $this->validate($query, $validator);

        $this->format = is_string($query['format'] ?? null) ? strtolower(trim($query['format'])) : '';
        $this->labels = $this->normalizeLabels($query['labels'] ?? []);
    }

    private function validate(array $query, ValidatorInterface $validator): void
    {
        $violations = $validator->validate($query, new Assert\Collection(
            fields: [
                'labels' => new Assert\Optional(new Assert\Sequentially([
                    new Assert\Type('array'),
                    new Assert\All(new Assert\Sequentially([
                        new Assert\Type('string'),
                        new Assert\Length(max: self::LABEL_MAX_LENGTH),
                    ])),
                ])),
            ],
            allowExtraFields: true,
        ));

        $details = [];
        foreach ($violations as $violation) {
            $details[$violation->getPropertyPath()][] = $violation->getMessage();
        }

        if (count($details) > 0) {
            throw new ValidationException('Invalid input', $details);
        }
    }

    /**
     * Blank labels are dropped so the export falls back to the type key instead of printing an empty cell.
     *
     * @param array<int|string, string> $labels
     *
     * @return array<string, string>
     */
    private function normalizeLabels(array $labels): array
    {
        $normalized = [];

        foreach ($labels as $key => $label) {
            $label = trim($label);

            if ('' !== $label) {
                $normalized[(string) $key] = $label;
            }
        }

        return $normalized;
    }
}

<?php

declare(strict_types=1);

namespace App\Auth\Infrastructure\Http\Request;

use App\Shared\Application\Exception\ValidationException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Validator\ValidatorInterface;

final readonly class ResendVerificationRequest
{
    public string $email;

    public function __construct(Request $request, ValidatorInterface $validator)
    {
        $data = json_decode($request->getContent(), true) ?? [];

        $this->validate($data, $validator);

        $this->email = trim($data['email'] ?? '');
    }

    private function validate(array $data, ValidatorInterface $validator): void
    {
        $violations = $validator->validate($data, new Assert\Collection([
            'email' => [new Assert\NotBlank(), new Assert\Email()],
        ]));

        if (count($violations) > 0) {
            $details = [];
            foreach ($violations as $violation) {
                $details[$violation->getPropertyPath()][] = $violation->getMessage();
            }

            throw new ValidationException('Invalid input', $details);
        }
    }
}

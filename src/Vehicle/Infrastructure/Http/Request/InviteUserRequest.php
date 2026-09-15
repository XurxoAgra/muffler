<?php

declare(strict_types=1);

namespace App\Vehicle\Infrastructure\Http\Request;

use App\Shared\Application\Exception\ValidationException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Validator\ValidatorInterface;

final readonly class InviteUserRequest
{
    public string $email;

    public function __construct(Request $request, ValidatorInterface $validator)
    {
        $data = json_decode($request->getContent(), true) ?? [];

        $this->validate($data, $validator);

        $this->email = strtolower(trim($data['email'] ?? ''));
    }

    private function validate(array $data, ValidatorInterface $validator): void
    {
        $violations = $validator->validate($data, new Assert\Collection([
            'email' => [
                new Assert\NotBlank(message: 'Email cannot be blank'),
                new Assert\Email(message: 'Email is not a valid address'),
            ],
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

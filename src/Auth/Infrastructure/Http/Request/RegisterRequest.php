<?php

declare(strict_types=1);

namespace App\Auth\Infrastructure\Http\Request;

use App\Shared\Application\Exception\ValidationException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Validator\ValidatorInterface;

final readonly class RegisterRequest
{
    public string $email;
    public string $password;
    public string $firstName;
    public string $lastName;

    public function __construct(Request $request, ValidatorInterface $validator)
    {
        $data = json_decode($request->getContent(), true) ?? [];

        $this->validate($data, $validator);

        $this->email = trim($data['email'] ?? '');
        $this->password = $data['password'] ?? '';
        $this->firstName = trim($data['first_name'] ?? '');
        $this->lastName = trim($data['last_name'] ?? '');
    }

    private function validate(array $data, ValidatorInterface $validator): void
    {
        $violations = $validator->validate($data, new Assert\Collection([
            'email' => [new Assert\NotBlank(), new Assert\Email()],
            'password' => [
                new Assert\NotBlank(),
                new Assert\Length(min: 8),
                new Assert\Regex('/[A-Z]/', message: 'Password must contain at least one uppercase letter'),
                new Assert\Regex('/[0-9]/', message: 'Password must contain at least one number'),
            ],
            'first_name' => [new Assert\NotBlank(), new Assert\Length(max: 100)],
            'last_name' => [new Assert\NotBlank(), new Assert\Length(max: 100)],
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

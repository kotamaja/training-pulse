<?php

namespace App\Dto\User;

use Symfony\Component\Validator\Constraints as Assert;

final class UserCreateDto
{
    #[Assert\NotBlank]
    #[Assert\Email]
    #[Assert\Length(max: 180)]
    public string $email;

    #[Assert\Length(max: 180)]
    public ?string $username = null;

    #[Assert\Length(min: 8, max: 4096)]
    #[Assert\PasswordStrength(minScore: Assert\PasswordStrength::STRENGTH_MEDIUM)]
    public ?string $plainPassword = null;

    /**
     * @var list<string>
     */

    #[Assert\NotNull]
    #[Assert\Type('array')]
    #[Assert\All([
        new Assert\Type('string'),
        new Assert\NotBlank(),
        new Assert\Regex(
            pattern: '/^ROLE_[A-Z0-9_]+$/',
            message: 'Role must be a valid Symfony role name.',
        ),
    ])]
    public array $roles = ['ROLE_USER'];

    #[Assert\Type('bool')]
    public bool $enabled = true;
}

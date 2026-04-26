<?php

namespace App\Dto\User;

use Symfony\Component\Validator\Constraints as Assert;

final class UserPatchDto
{
    private bool $emailProvided = false;
    private bool $usernameProvided = false;
    private bool $plainPasswordProvided = false;
    private bool $rolesProvided = false;
    private bool $enabledProvided = false;

    #[Assert\Email]
    #[Assert\Length(max: 180)]
    private ?string $email = null;

    #[Assert\Length(max: 180)]
    private ?string $username = null;

    #[Assert\Length(min: 8, max: 4096)]
    #[Assert\PasswordStrength(minScore: Assert\PasswordStrength::STRENGTH_MEDIUM)]
    private ?string $plainPassword = null;

    /**
     * @var list<string>|null
     */
    #[Assert\Type('array')]
    #[Assert\All([
        new Assert\Type('string'),
        new Assert\NotBlank(),
        new Assert\Regex(
            pattern: '/^ROLE_[A-Z0-9_]+$/',
            message: 'Role must be a valid Symfony role name.',
        ),
    ])]
    private ?array $roles = null;

    #[Assert\Type('bool')]
    private ?bool $enabled = null;

    public function setEmail(?string $email): void
    {
        $this->emailProvided = true;
        $this->email = $email;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function isEmailProvided(): bool
    {
        return $this->emailProvided;
    }

    public function setUsername(?string $username): void
    {
        $this->usernameProvided = true;
        $this->username = $username;
    }

    public function getUsername(): ?string
    {
        return $this->username;
    }

    public function isUsernameProvided(): bool
    {
        return $this->usernameProvided;
    }

    public function setPlainPassword(?string $plainPassword): void
    {
        $this->plainPasswordProvided = true;
        $this->plainPassword = $plainPassword;
    }

    public function getPlainPassword(): ?string
    {
        return $this->plainPassword;
    }

    public function isPlainPasswordProvided(): bool
    {
        return $this->plainPasswordProvided;
    }

    /**
     * @param list<string>|null $roles
     */
    public function setRoles(?array $roles): void
    {
        $this->rolesProvided = true;
        $this->roles = $roles;
    }

    /**
     * @return list<string>|null
     */
    public function getRoles(): ?array
    {
        return $this->roles;
    }

    public function isRolesProvided(): bool
    {
        return $this->rolesProvided;
    }

    public function setEnabled(?bool $enabled): void
    {
        $this->enabledProvided = true;
        $this->enabled = $enabled;
    }

    public function isEnabled(): ?bool
    {
        return $this->enabled;
    }

    public function isEnabledProvided(): bool
    {
        return $this->enabledProvided;
    }
}

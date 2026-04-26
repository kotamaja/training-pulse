<?php

namespace App\Dto\User;

final class UserDetailDto
{
    public string $id;

    public string $email;

    public ?string $username = null;

    /**
     * @var list<string>
     */
    public array $roles = [];

    public bool $enabled;

    public string $createdAt;

    public ?string $updatedAt = null;
}

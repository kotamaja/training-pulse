<?php

namespace App\Write\User;

use App\Dto\User\UserCreateDto;
use App\Dto\User\UserPatchDto;
use App\Entity\User;
use App\Repository\UserRepository;
use App\Write\Exception\BusinessRuleViolationException;
use App\Write\Exception\ResourceConflictException;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

final readonly class UserWriteService implements UserWriteServiceInterface
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private UserRepository $userRepository,
        private UserPasswordHasherInterface $passwordHasher,
    ) {
    }

    public function create(UserCreateDto $input, ?User $actor = null): User
    {
        $email = $this->normalizeEmail($input->email);

        if ($email === '') {
            throw new BusinessRuleViolationException(
                message: 'Email cannot be empty.',
                field: 'email',
            );
        }

        if ($this->userRepository->findOneBy(['email' => $email]) !== null) {
            throw new ResourceConflictException(
                message: sprintf('A user with email "%s" already exists.', $email),
                field: 'email',
            );
        }

        $user = new User($email);

        $user->setUsername($this->normalizeNullableString($input->username));
        $user->setRoles($this->normalizeRoles($input->roles));
        $user->setEnabled($input->enabled);

        if ($input->plainPassword !== null && trim($input->plainPassword) !== '') {
            $user->setPassword(
                $this->passwordHasher->hashPassword($user, $input->plainPassword),
            );
        }

        $this->entityManager->persist($user);

        return $user;
    }

    public function patch(UserPatchDto $input, User $user, ?User $actor = null): UserPatchResult
    {
        $changed = false;

        if ($input->isEmailProvided()) {
            $email = $input->getEmail();

            if ($email === null || trim($email) === '') {
                throw new BusinessRuleViolationException(
                    message: 'Email cannot be empty.',
                    field: 'email',
                );
            }

            $email = $this->normalizeEmail($email);

            if ($user->getEmail() !== $email) {
                $existingUser = $this->userRepository->findOneBy(['email' => $email]);

                if ($existingUser !== null && $existingUser !== $user) {
                    throw new ResourceConflictException(
                        message: sprintf('A user with email "%s" already exists.', $email),
                        field: 'email',
                    );
                }

                $user->setEmail($email);
                $changed = true;
            }
        }

        if ($input->isUsernameProvided()) {
            $username = $this->normalizeNullableString($input->getUsername());

            if ($user->getUsername() !== $username) {
                $user->setUsername($username);
                $changed = true;
            }
        }

        if ($input->isPlainPasswordProvided()) {
            $plainPassword = $input->getPlainPassword();

            if ($plainPassword === null || trim($plainPassword) === '') {
                throw new BusinessRuleViolationException(
                    message: 'Password cannot be empty.',
                    field: 'plainPassword',
                );
            }

            $user->setPassword(
                $this->passwordHasher->hashPassword($user, $plainPassword),
            );

            $changed = true;
        }

        if ($input->isRolesProvided()) {
            $roles = $this->normalizeRoles($input->getRoles() ?? []);

            if ($this->rolesAreDifferent($user->getRoles(), $roles)) {
                $user->setRoles($roles);
                $changed = true;
            }
        }

        if ($input->isEnabledProvided()) {
            $enabled = $input->isEnabled();

            if ($enabled === null) {
                throw new BusinessRuleViolationException(
                    message: 'Enabled cannot be null.',
                    field: 'enabled',
                );
            }

            if ($user->isEnabled() !== $enabled) {
                $user->setEnabled($enabled);
                $changed = true;
            }
        }

        return new UserPatchResult(
            user: $user,
            changed: $changed,
        );
    }

    public function delete(User $user, ?User $actor = null): void
    {
        if ($actor !== null && $actor === $user) {
            throw new BusinessRuleViolationException(
                message: 'You cannot delete your own user account.',
            );
        }

        $this->entityManager->remove($user);
    }

    private function normalizeEmail(string $email): string
    {
        return mb_strtolower(trim($email));
    }

    private function normalizeNullableString(?string $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $value = trim($value);

        return $value !== '' ? $value : null;
    }

    /**
     * @param list<string> $roles
     *
     * @return list<string>
     */
    private function normalizeRoles(array $roles): array
    {
        $roles[] = 'ROLE_USER';

        $roles = array_map(
            static fn (string $role): string => strtoupper(trim($role)),
            $roles,
        );

        $roles = array_filter(
            $roles,
            static fn (string $role): bool => $role !== '',
        );

        $roles = array_values(array_unique($roles));
        sort($roles);

        return $roles;
    }

    /**
     * @param list<string> $currentRoles
     * @param list<string> $newRoles
     */
    private function rolesAreDifferent(array $currentRoles, array $newRoles): bool
    {
        return $this->normalizeRoles($currentRoles) !== $this->normalizeRoles($newRoles);
    }
}

<?php

declare(strict_types=1);

namespace NAdminAuth\Entity;

/**
 * Interface for admin user entities.
 *
 * Your project's User entity must implement this interface.
 * Provides the contract needed by AdminAuthenticator.
 */
interface AdminUserInterface
{
    public function getId(): int;

    public function getEmail(): string;

    public function getPasswordHash(): string;

    public function setPasswordHash(string $hash): void;

    public function getFullName(): string;

    public function isActive(): bool;

    public function getLocale(): string;

    public function getRole(): ?AdminRoleInterface;
}

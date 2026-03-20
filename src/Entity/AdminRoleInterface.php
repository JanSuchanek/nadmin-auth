<?php

declare(strict_types=1);

namespace NAdminAuth\Entity;

use Doctrine\Common\Collections\Collection;

/**
 * Interface for role entities.
 *
 * Your project's Role entity must implement this interface.
 */
interface AdminRoleInterface
{
    public function getId(): ?int;

    public function getName(): string;

    public function getSlug(): string;

    public function isSuperAdmin(): bool;

    /** @return Collection<int, AdminPermissionInterface> */
    public function getPermissions(): Collection;

    public function hasPermission(string $resource, string $privilege): bool;
}

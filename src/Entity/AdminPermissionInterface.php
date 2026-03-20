<?php

declare(strict_types=1);

namespace NAdminAuth\Entity;

/**
 * Interface for role permission entries.
 */
interface AdminPermissionInterface
{
    public function getResource(): string;

    public function getPrivilege(): string;
}

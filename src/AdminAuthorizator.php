<?php

declare(strict_types=1);

namespace NAdminAuth;

use Doctrine\ORM\EntityManagerInterface;
use NAdminAuth\Entity\AdminRoleInterface;
use Nette\Security\Authorizator;

/**
 * Permission-based authorizator.
 *
 * Checks role permissions from Doctrine entities implementing AdminRoleInterface.
 * Superadmin role always returns true.
 */
final class AdminAuthorizator implements Authorizator
{
    /** @var class-string<AdminRoleInterface> */
    private string $roleEntityClass;

    private string $superadminRole;


    /**
     * @param class-string<AdminRoleInterface> $roleEntityClass
     */
    public function __construct(
        private readonly EntityManagerInterface $em,
        string $roleEntityClass,
        string $superadminRole = 'superadmin',
    ) {
        $this->roleEntityClass = $roleEntityClass;
        $this->superadminRole = $superadminRole;
    }


    /**
     * @param string|null $role
     * @param string|null $resource
     * @param string|null $privilege
     */
    public function isAllowed($role, $resource, $privilege): bool
    {
        if ($role === null || $resource === null) {
            return false;
        }

        if ($role === $this->superadminRole) {
            return true;
        }

        /** @var AdminRoleInterface|null $roleEntity */
        $roleEntity = $this->em->getRepository($this->roleEntityClass)
            ->findOneBy(['slug' => $role]);

        if ($roleEntity === null) {
            return false;
        }

        return $roleEntity->hasPermission($resource, $privilege ?? 'view');
    }
}

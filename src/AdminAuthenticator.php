<?php

declare(strict_types=1);

namespace NAdminAuth;

use Doctrine\ORM\EntityManagerInterface;
use NAdminAuth\Entity\AdminUserInterface;
use Nette\Security\AuthenticationException;
use Nette\Security\Authenticator;
use Nette\Security\IIdentity;
use Nette\Security\Passwords;
use Nette\Security\SimpleIdentity;

/**
 * Universal admin authenticator.
 *
 * Authenticates users against Doctrine entities implementing AdminUserInterface.
 * Builds identity with role, permissions list, and locale.
 */
final class AdminAuthenticator implements Authenticator
{
    /** @var class-string<AdminUserInterface> */
    private string $userEntityClass;


    /**
     * @param class-string<AdminUserInterface> $userEntityClass
     */
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly Passwords $passwords,
        string $userEntityClass,
    ) {
        $this->userEntityClass = $userEntityClass;
    }


    public function authenticate(string $user, string $password): IIdentity
    {
        /** @var AdminUserInterface|null $userEntity */
        $userEntity = $this->em->getRepository($this->userEntityClass)
            ->findOneBy(['email' => $user]);

        if ($userEntity === null) {
            throw new AuthenticationException('User not found.', self::IdentityNotFound);
        }

        if (!$userEntity->isActive()) {
            throw new AuthenticationException('Account is disabled.', self::Failure);
        }

        if (!$this->passwords->verify($password, $userEntity->getPasswordHash())) {
            throw new AuthenticationException('Invalid password.', self::InvalidCredential);
        }

        // Rehash if needed
        if ($this->passwords->needsRehash($userEntity->getPasswordHash())) {
            $userEntity->setPasswordHash($this->passwords->hash($password));
            $this->em->flush();
        }

        $role = $userEntity->getRole();
        $roleSlug = $role?->getSlug() ?? 'admin';

        // Collect permissions as ['resource.privilege', ...]
        $permissions = [];
        if ($role !== null) {
            foreach ($role->getPermissions() as $perm) {
                $permissions[] = $perm->getResource() . '.' . $perm->getPrivilege();
            }
        }

        return new SimpleIdentity(
            $userEntity->getId(),
            [$roleSlug],
            [
                'email' => $userEntity->getEmail(),
                'fullName' => $userEntity->getFullName(),
                'locale' => $userEntity->getLocale(),
                'roleSlug' => $roleSlug,
                'roleName' => $role?->getName() ?? 'Admin',
                'permissions' => $permissions,
            ],
        );
    }
}

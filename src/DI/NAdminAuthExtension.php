<?php

declare(strict_types=1);

namespace NAdminAuth\DI;

use NAdminAuth\AdminAuthenticator;
use NAdminAuth\AdminAuthorizator;
use Nette\DI\CompilerExtension;
use Nette\Schema\Expect;
use Nette\Schema\Schema;

/**
 * DI extension for nadmin-auth.
 *
 * Configuration:
 *   nadminAuth:
 *       userEntity: App\Entity\User
 *       roleEntity: App\Entity\Role
 *       superadminRole: superadmin   # optional, default 'superadmin'
 */
final class NAdminAuthExtension extends CompilerExtension
{
    public function getConfigSchema(): Schema
    {
        return Expect::structure([
            'userEntity' => Expect::string()->required(),
            'roleEntity' => Expect::string()->required(),
            'superadminRole' => Expect::string('superadmin'),
        ]);
    }


    public function loadConfiguration(): void
    {
        $builder = $this->getContainerBuilder();
        /** @var \stdClass $config */
        $config = $this->getConfig();

        $builder->addDefinition($this->prefix('authenticator'))
            ->setFactory(AdminAuthenticator::class, [
                'userEntityClass' => $config->userEntity,
            ])
            ->setAutowired(false);

        $builder->addDefinition($this->prefix('authorizator'))
            ->setFactory(AdminAuthorizator::class, [
                'roleEntityClass' => $config->roleEntity,
                'superadminRole' => $config->superadminRole,
            ])
            ->setAutowired(false);
    }
}

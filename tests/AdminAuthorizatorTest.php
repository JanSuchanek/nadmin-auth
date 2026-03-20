<?php

declare(strict_types=1);

namespace NAdminAuth\Tests;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use NAdminAuth\AdminAuthorizator;
use NAdminAuth\Entity\AdminRoleInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

final class AdminAuthorizatorTest extends TestCase
{
	private EntityManagerInterface&MockObject $em;
	private AdminAuthorizator $authorizator;


	protected function setUp(): void
	{
		$this->em = $this->createMock(EntityManagerInterface::class);
		$this->authorizator = new AdminAuthorizator(
			$this->em,
			AdminRoleInterface::class,
			'superadmin',
		);
	}


	public function testNullRoleReturnsFalse(): void
	{
		$this->assertFalse($this->authorizator->isAllowed(null, 'product', 'view'));
	}


	public function testNullResourceReturnsFalse(): void
	{
		$this->assertFalse($this->authorizator->isAllowed('editor', null, 'view'));
	}


	public function testSuperadminAlwaysAllowed(): void
	{
		// Should not even touch DB
		$this->assertTrue($this->authorizator->isAllowed('superadmin', 'product', 'delete'));
		$this->assertTrue($this->authorizator->isAllowed('superadmin', 'anything', 'whatever'));
	}


	public function testRoleNotFoundReturnsFalse(): void
	{
		$repo = $this->createMock(EntityRepository::class);
		$repo->method('findOneBy')->willReturn(null);
		$this->em->method('getRepository')->willReturn($repo);

		$this->assertFalse($this->authorizator->isAllowed('unknown_role', 'product', 'view'));
	}


	public function testRoleWithPermissionAllowed(): void
	{
		$role = $this->createMock(AdminRoleInterface::class);
		$role->method('hasPermission')
			->with('product', 'edit')
			->willReturn(true);

		$this->setupRepository($role);

		$this->assertTrue($this->authorizator->isAllowed('editor', 'product', 'edit'));
	}


	public function testRoleWithoutPermissionDenied(): void
	{
		$role = $this->createMock(AdminRoleInterface::class);
		$role->method('hasPermission')
			->with('user', 'delete')
			->willReturn(false);

		$this->setupRepository($role);

		$this->assertFalse($this->authorizator->isAllowed('editor', 'user', 'delete'));
	}


	public function testNullPrivilegeDefaultsToView(): void
	{
		$role = $this->createMock(AdminRoleInterface::class);
		$role->expects($this->once())
			->method('hasPermission')
			->with('product', 'view')
			->willReturn(true);

		$this->setupRepository($role);

		$this->assertTrue($this->authorizator->isAllowed('editor', 'product', null));
	}


	public function testCustomSuperadminRole(): void
	{
		$authorizator = new AdminAuthorizator(
			$this->em,
			AdminRoleInterface::class,
			'god_mode',
		);

		$this->assertTrue($authorizator->isAllowed('god_mode', 'anything', 'ever'));
	}


	private function setupRepository(AdminRoleInterface $role): void
	{
		$repo = $this->createMock(EntityRepository::class);
		$repo->method('findOneBy')->willReturn($role);
		$this->em->method('getRepository')->willReturn($repo);
	}
}

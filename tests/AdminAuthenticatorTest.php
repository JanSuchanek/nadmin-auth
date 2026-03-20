<?php

declare(strict_types=1);

namespace NAdminAuth\Tests;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use NAdminAuth\AdminAuthenticator;
use NAdminAuth\Entity\AdminPermissionInterface;
use NAdminAuth\Entity\AdminRoleInterface;
use NAdminAuth\Entity\AdminUserInterface;
use Nette\Security\AuthenticationException;
use Nette\Security\Passwords;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

final class AdminAuthenticatorTest extends TestCase
{
	private EntityManagerInterface&MockObject $em;
	private Passwords $passwords;
	private AdminAuthenticator $authenticator;


	protected function setUp(): void
	{
		$this->em = $this->createMock(EntityManagerInterface::class);
		$this->passwords = new Passwords(PASSWORD_BCRYPT, ['cost' => 4]); // fast for tests
		$this->authenticator = new AdminAuthenticator(
			$this->em,
			$this->passwords,
			AdminUserInterface::class,
		);
	}


	public function testUserNotFound(): void
	{
		$repo = $this->createMock(EntityRepository::class);
		$repo->method('findOneBy')->willReturn(null);
		$this->em->method('getRepository')->willReturn($repo);

		$this->expectException(AuthenticationException::class);
		$this->expectExceptionMessage('User not found.');

		$this->authenticator->authenticate('unknown@example.com', 'password');
	}


	public function testInactiveUser(): void
	{
		$user = $this->createMockUser(active: false);
		$this->setupRepository($user);

		$this->expectException(AuthenticationException::class);
		$this->expectExceptionMessage('Account is disabled.');

		$this->authenticator->authenticate('test@example.com', 'password');
	}


	public function testInvalidPassword(): void
	{
		$user = $this->createMockUser(passwordHash: $this->passwords->hash('correctPassword'));
		$this->setupRepository($user);

		$this->expectException(AuthenticationException::class);
		$this->expectExceptionMessage('Invalid password.');

		$this->authenticator->authenticate('test@example.com', 'wrongPassword');
	}


	public function testSuccessfulAuthentication(): void
	{
		$password = 'secret123';
		$hash = $this->passwords->hash($password);
		$user = $this->createMockUser(passwordHash: $hash);
		$this->setupRepository($user);

		$identity = $this->authenticator->authenticate('test@example.com', $password);

		$this->assertSame(1, $identity->getId());
		$this->assertSame(['editor'], $identity->getRoles());
		$this->assertSame('test@example.com', $identity->email);
		$this->assertSame('Jan Test', $identity->fullName);
		$this->assertSame('cs', $identity->locale);
		$this->assertContains('product.edit', $identity->permissions);
		$this->assertContains('product.view', $identity->permissions);
	}


	public function testSuccessfulAuthenticationNoRole(): void
	{
		$password = 'secret123';
		$hash = $this->passwords->hash($password);
		$user = $this->createMockUser(passwordHash: $hash, hasRole: false);
		$this->setupRepository($user);

		$identity = $this->authenticator->authenticate('test@example.com', $password);

		$this->assertSame(['admin'], $identity->getRoles());
		$this->assertEmpty($identity->permissions);
	}


	/**
	 * Create a mock AdminUserInterface.
	 */
	private function createMockUser(
		bool $active = true,
		string $passwordHash = '',
		bool $hasRole = true,
	): AdminUserInterface&MockObject {
		$user = $this->createMock(AdminUserInterface::class);
		$user->method('getId')->willReturn(1);
		$user->method('getEmail')->willReturn('test@example.com');
		$user->method('getFullName')->willReturn('Jan Test');
		$user->method('isActive')->willReturn($active);
		$user->method('getPasswordHash')->willReturn($passwordHash ?: $this->passwords->hash('password'));
		$user->method('getLocale')->willReturn('cs');

		if ($hasRole) {
			$perm1 = $this->createMock(AdminPermissionInterface::class);
			$perm1->method('getResource')->willReturn('product');
			$perm1->method('getPrivilege')->willReturn('edit');

			$perm2 = $this->createMock(AdminPermissionInterface::class);
			$perm2->method('getResource')->willReturn('product');
			$perm2->method('getPrivilege')->willReturn('view');

			$role = $this->createMock(AdminRoleInterface::class);
			$role->method('getSlug')->willReturn('editor');
			$role->method('getName')->willReturn('Editor');
			$role->method('getPermissions')->willReturn(new ArrayCollection([$perm1, $perm2]));

			$user->method('getRole')->willReturn($role);
		} else {
			$user->method('getRole')->willReturn(null);
		}

		return $user;
	}


	private function setupRepository(AdminUserInterface $user): void
	{
		$repo = $this->createMock(EntityRepository::class);
		$repo->method('findOneBy')->willReturn($user);
		$this->em->method('getRepository')->willReturn($repo);
	}
}

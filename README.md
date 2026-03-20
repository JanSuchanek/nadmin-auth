# NAdmin Auth

Admin authentication, roles & permissions for Nette Framework.

## Features

- 🔐 **AdminAuthenticator** — Doctrine-based login with password rehash
- 🛡️ **AdminAuthorizator** — Role-based ACL with superadmin bypass
- 📋 **Interfaces** — `AdminUserInterface`, `AdminRoleInterface`, `AdminPermissionInterface`
- ⚙️ **DI Extension** — Zero-config Nette integration

## Installation

```bash
composer require jansuchanek/nadmin-auth
```

## Configuration

```neon
extensions:
    nadminAuth: NAdminAuth\DI\NAdminAuthExtension

nadminAuth:
    userEntity: App\Entity\User
    roleEntity: App\Entity\Role
```

## Usage

Your entities must implement the provided interfaces:

```php
use NAdminAuth\Entity\AdminUserInterface;

class User implements AdminUserInterface
{
    public function getId(): ?int { /* ... */ }
    public function getEmail(): string { /* ... */ }
    public function getPasswordHash(): string { /* ... */ }
    public function setPasswordHash(string $hash): void { /* ... */ }
    public function getFullName(): string { /* ... */ }
    public function isActive(): bool { /* ... */ }
    public function getLocale(): string { /* ... */ }
    public function getRole(): ?AdminRoleInterface { /* ... */ }
}
```

## Testing

```bash
vendor/bin/phpunit libs/nadmin-auth/tests/
```

13 tests, 31 assertions.

## Requirements

- PHP >= 8.2
- Nette Security ^3.2
- Doctrine ORM ^3.0

## License

MIT

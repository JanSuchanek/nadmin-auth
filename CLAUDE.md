# nadmin-auth

Admin authentication + role-based ACL for Nette.

## Key Classes
- `AdminAuthenticator` — Doctrine-based login with bcrypt + auto-rehash
- `AdminAuthorizator` — Role-based permission check with superadmin bypass
- Interfaces: `AdminUserInterface`, `AdminRoleInterface`, `AdminPermissionInterface`
- DI Extension: `NAdminAuthExtension`

## Tests
13 PHPUnit tests in `tests/` (AdminAuthenticatorTest + AdminAuthorizatorTest)

## Conventions
- PHP 8.2+ with `declare(strict_types=1)`
- PSR-4 autoloading in `src/`
- Nette DI Extension for service registration
- PHPStan level 9
- Part of `jansuchanek/*` on Packagist
- GitHub: https://github.com/JanSuchanek/nadmin-auth

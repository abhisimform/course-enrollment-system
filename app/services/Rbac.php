<?php

class Rbac
{
  public static function user()
  {
    return $_SESSION['user'] ?? null;
  }

  public static function permissions()
  {
    return $_SESSION['permissions'] ?? [];
  }

  public static function isAdmin()
  {
    $user = self::user();
    return isset($user['role']) && $user['role'] === 'admin';
  }

  public static function has($permission)
  {
    if (!self::user()) return false;

    if (self::isAdmin()) return true;

    foreach (self::permissions() as $perm) {
      if (isset($perm['name']) && $perm['name'] === $permission) {
        return true;
      }
    }

    return false;
  }

  public static function require($permission)
  {
    if (!self::user()) {
      self::deny();
    }

    if (self::isAdmin()) return true;

    foreach (self::permissions() as $perm) {
      if (isset($perm['name']) && $perm['name'] === $permission) {
        return true;
      }
    }

    self::deny();
  }

  private static function deny()
  {
    http_response_code(403);
    die('403 Forbidden - You do not have permission to access this resource.');
  }
}

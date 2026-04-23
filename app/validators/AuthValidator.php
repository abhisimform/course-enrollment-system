<?php

class AuthValidator
{
  public static function register(array $data = []): array
  {
    return [
      'name' => ['required', 'min:3', 'max:50'],
      'email' => ['required', 'email'],
      'password' => ['required', 'min:6']
    ];
  }

  public static function login(array $data = []): array
  {
    return [
      'email' => ['required', 'email'],
      'password' => ['required']
    ];
  }
}

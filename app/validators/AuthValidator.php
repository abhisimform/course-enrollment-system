<?php

class AuthValidator
{
  public static function login(array $data = []): array
  {
    return [
      'email' => ['required', 'email'],
      'password' => ['required']
    ];
  }
}

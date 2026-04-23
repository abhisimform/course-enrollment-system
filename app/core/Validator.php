<?php

class Validator
{
  private array $errors = [];

  public function validate(array $data, array $rules): bool
  {
    foreach ($rules as $field => $ruleList) {
      $value = $data[$field] ?? null;

      foreach ($ruleList as $rule) {

        if ($rule === 'required' && empty($value)) {
          $this->errors[$field][] = "$field is required";
        }

        if ($rule === 'email' && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
          $this->errors[$field][] = "Invalid email format";
        }

        if (str_starts_with($rule, 'min:')) {
          $min = explode(':', $rule)[1];
          if (strlen($value) < $min) {
            $this->errors[$field][] = "$field must be at least $min characters";
          }
        }

        if (str_starts_with($rule, 'max:')) {
          $max = explode(':', $rule)[1];
          if (strlen($value) > $max) {
            $this->errors[$field][] = "$field must be less than $max characters";
          }
        }

        if ($rule === 'numeric' && !is_numeric($value)) {
          $this->errors[$field][] = "$field must be numeric";
        }
      }
    }

    return empty($this->errors);
  }

  public function errors(): array
  {
    return $this->errors;
  }
}

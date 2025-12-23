<?php

declare(strict_types=1);

namespace App\Validation;

class Validator
{
    private array $errors = [];

    public function validate(array $data, array $rules): bool
    {
        foreach ($rules as $field => $ruleSet) {
            foreach ($ruleSet as $rule) {
                $this->applyRule($field, $data[$field] ?? null, $rule, $data);
            }
        }

        return empty($this->errors);
    }

    private function applyRule(string $field, $value, string $rule, array $data): void
    {
        switch ($rule) {
            case 'required':
                if (empty($value)) {
                    $this->errors[$field][] = "The {$field} field is required.";
                }
                break;

            case 'string':
                if (!is_string($value)) {
                    $this->errors[$field][] = "The {$field} must be a string.";
                }
                break;

            case 'min:6':
                if (strlen($value) < 6) {
                    $this->errors[$field][] = "The {$field} must be at least 6 characters.";
                }
                break;

            case 'email':
                if (!filter_var($value, FILTER_VALIDATE_EMAIL)) {
                    $this->errors[$field][] = "The {$field} must be a valid email address.";
                }
                break;

            case 'confirmed':
                if ($value !== ($data[$field . '_confirmation'] ?? null)) {
                    $this->errors[$field][] = "The {$field} confirmation does not match.";
                }
                break;

            case 'max:255':
                if (strlen($value) > 255) {
                    $this->errors[$field][] = "The {$field} may not be greater than 255 characters.";
                }
                break;
        }
    }

    public function getErrors(): array
    {
        return $this->errors;
    }

    public function getFirstError(): ?string
    {
        foreach ($this->errors as $fieldErrors) {
            if (!empty($fieldErrors)) {
                return $fieldErrors[0];
            }
        }
        return null;
    }
}

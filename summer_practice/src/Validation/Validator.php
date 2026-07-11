<?php

namespace App\Validation;

class Validator
{
    private array $errors = [];

    public function validateNotEmpty(string $field, $value, ?string $message = null): self
    {
        if (empty(trim($value))) {
            $this->errors[$field][] = $message ?? "Поле '{$field}' не может быть пустым";
        }
        return $this;
    }

    public function validateMinLength(string $field, string $value, int $min, ?string $message = null): self
    {
        if (mb_strlen($value) < $min) {
            $this->errors[$field][] = $message ?? "Поле '{$field}' должно содержать не менее {$min} символов";
        }
        return $this;
    }

    public function validateMaxLength(string $field, string $value, int $max, ?string $message = null): self
    {
        if (mb_strlen($value) > $max) {
            $this->errors[$field][] = $message ?? "Поле '{$field}' должно содержать не более {$max} символов";
        }
        return $this;
    }

    public function validateUnique(string $field, string $value, callable $existsFn, ?string $message = null): self
    {
        if ($existsFn($value)) {
            $this->errors[$field][] = $message ?? "Значение '{$value}' уже существует";
        }
        return $this;
    }

    public function isValid(): bool
    {
        return empty($this->errors);
    }

    public function getErrors(): array
    {
        return $this->errors;
    }

    public function getFirstError(): ?string
    {
        $first = reset($this->errors);
        return $first ? $first[0] : null;
    }
}

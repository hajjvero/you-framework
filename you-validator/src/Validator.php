<?php

namespace YouValidator;
/**
 * Validator class that holds validation rules and performs validation
 */
class Validator
{
    private string $fieldName;
    private mixed $value;
    private array $rules = [];
    private array $errors = [];

    public function __construct(string $fieldName, $value)
    {
        $this->fieldName = $fieldName;
        $this->value = $value;
    }

    public function addRule(callable $rule, string $errorMessage): void
    {
        $this->rules[] = ['rule' => $rule, 'message' => $errorMessage];
    }

    public function validate(): bool
    {
        $this->errors = [];

        foreach ($this->rules as $rule) {
            if (!$rule['rule']($this->value)) {
                $this->errors[] = str_replace(':field', $this->fieldName, $rule['message']);
            }
        }

        return empty($this->errors);
    }

    public function getErrors(): array
    {
        return $this->errors;
    }
}
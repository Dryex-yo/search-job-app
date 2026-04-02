<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Facades\Log;

/**
 * Rule to prevent SQL injection in form inputs
 * 
 * Usage: 'field_name' => [new NoSqlInjection()]
 */
class NoSqlInjection implements ValidationRule
{
    /**
     * SQL injection patterns to detect
     */
    private array $patterns = [
        "/(union|select|insert|update|delete|drop|create|alter|exec|execute|script)/i",
        "/(\-\-|\/\*|\*\/);/",
        "/(or|and)\s*(=|!=|<|>|like)\s*['\"]/i",
        "/0x[0-9a-f]+/i",
        "/(\'|\")\s*or\s*(\'|\")/i",
    ];

    /**
     * Run the validation rule.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @param  \Closure  $fail
     * @return void
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (!is_string($value)) {
            return;
        }

        foreach ($this->patterns as $pattern) {
            if (preg_match($pattern, $value)) {
                Log::warning("SQL Injection attempt detected in field: {$attribute}", [
                    'value' => substr($value, 0, 50),
                ]);

                $fail("The {$attribute} field contains invalid characters or patterns.");
                return;
            }
        }
    }
}

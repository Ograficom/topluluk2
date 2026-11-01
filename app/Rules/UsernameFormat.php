<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class UsernameFormat implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! is_string($value) || preg_match('/\A[\p{L}\p{M}\p{N}._-]+\z/u', $value) !== 1) {
            $fail('Kullanıcı adında harf, rakam, nokta, alt çizgi ve tire kullanabilirsiniz.');
        }
    }
}

<?php

declare(strict_types=1);

namespace Correios\Http\Requests;

class ConsultaCepRequest extends AbstractCorreiosRequest
{
    public function rules(): array
    {
        return [
            "cep" => ["required", "string", 'regex:/^\d{8}$/'],
        ];
    }

    protected function customMessages(): array
    {
        return [
            "cep.regex" => "CEP deve conter exatamente 8 dígitos.",
        ];
    }
}

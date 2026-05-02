<?php

declare(strict_types=1);

namespace Correios\Http\Requests;

class RastreioRequest extends AbstractCorreiosRequest
{
    public function rules(): array
    {
        return [
            "codigos" => ["required", "array", "min:1", "max:50"],
            "codigos.*" => [
                "required",
                "string",
                'regex:/^[A-Z]{2}\d{9}[A-Z]{2}$/',
            ],
            "resultado" => ["nullable", "string", "in:U,T"], // U=último evento, T=todos
        ];
    }

    protected function customMessages(): array
    {
        return [
            "codigos.*.regex" =>
                "Código de rastreamento inválido. Formato esperado: AA123456789BR.",
            "codigos.max" => "Máximo de 50 códigos por consulta.",
        ];
    }
}

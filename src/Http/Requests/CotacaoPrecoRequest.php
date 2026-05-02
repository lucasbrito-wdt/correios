<?php

declare(strict_types=1);

namespace Correios\Http\Requests;

class CotacaoPrecoRequest extends AbstractCorreiosRequest
{
    public function rules(): array
    {
        return [
            "coProduto" => [
                "required",
                "string",
                "in:03220,03298,04162,04669,04227,04510",
            ],
            "cepOrigem" => ["required", "string", 'regex:/^\d{8}$/'],
            "cepDestino" => ["required", "string", 'regex:/^\d{8}$/'],
            "psObjeto" => ["required", "integer", "min:1", "max:30000"],
            "tpObjeto" => ["nullable", "integer", "in:1,2,3"],
            "comprimento" => ["nullable", "integer", "min:11", "max:105"],
            "largura" => ["nullable", "integer", "min:6", "max:105"],
            "altura" => ["nullable", "integer", "min:1", "max:105"],
            "diametro" => ["nullable", "integer", "min:5", "max:91"],
            "vlDeclarado" => ["nullable", "numeric", "min:0"],
        ];
    }

    protected function customMessages(): array
    {
        return [
            "coProduto.in" =>
                "Código de serviço inválido. Use SEDEX (03220), PAC (03298) ou outro código contratual.",
            "cepOrigem.regex" =>
                "CEP de origem deve conter exatamente 8 dígitos.",
            "cepDestino.regex" =>
                "CEP de destino deve conter exatamente 8 dígitos.",
            "psObjeto.max" => "Peso máximo permitido é 30kg (30000g).",
        ];
    }
}

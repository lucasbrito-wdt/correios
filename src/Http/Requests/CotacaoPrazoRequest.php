<?php

declare(strict_types=1);

namespace Correios\Http\Requests;

class CotacaoPrazoRequest extends AbstractCorreiosRequest
{
    public function rules(): array
    {
        return [
            "coProduto" => ["required", "string"],
            "cepOrigem" => ["required", "string", 'regex:/^\d{8}$/'],
            "cepDestino" => ["required", "string", 'regex:/^\d{8}$/'],
            "dataPostagem" => ["nullable", "date_format:Y-m-d"],
        ];
    }

    protected function customMessages(): array
    {
        return [
            "cepOrigem.regex" =>
                "CEP de origem deve conter exatamente 8 dígitos.",
            "cepDestino.regex" =>
                "CEP de destino deve conter exatamente 8 dígitos.",
            "dataPostagem.date_format" =>
                "Data de postagem deve estar no formato AAAA-MM-DD.",
        ];
    }
}

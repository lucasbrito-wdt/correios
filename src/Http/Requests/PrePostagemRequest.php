<?php

declare(strict_types=1);

namespace Correios\Http\Requests;

class PrePostagemRequest extends AbstractCorreiosRequest
{
    public function rules(): array
    {
        return [
            "codigoServico" => ["required", "string"],
            "pesoGramas" => ["required", "integer", "min:1", "max:30000"],

            "dimensoes" => ["required", "array"],
            "dimensoes.altura" => ["required", "integer", "min:1"],
            "dimensoes.largura" => ["required", "integer", "min:6"],
            "dimensoes.comprimento" => ["required", "integer", "min:11"],

            "remetente" => ["required", "array"],
            "remetente.nome" => ["required", "string", "max:50"],
            "remetente.cpfCnpj" => ["required", "string"],
            "remetente.endereco.cep" => [
                "required",
                "string",
                'regex:/^\d{8}$/',
            ],
            "remetente.endereco.logradouro" => ["required", "string"],
            "remetente.endereco.numero" => ["required", "string"],
            "remetente.endereco.cidade" => ["required", "string"],
            "remetente.endereco.uf" => ["required", "string", "size:2"],

            "destinatario" => ["required", "array"],
            "destinatario.nome" => ["required", "string", "max:50"],
            "destinatario.endereco.cep" => [
                "required",
                "string",
                'regex:/^\d{8}$/',
            ],
            "destinatario.endereco.logradouro" => ["required", "string"],
            "destinatario.endereco.numero" => ["required", "string"],
            "destinatario.endereco.cidade" => ["required", "string"],
            "destinatario.endereco.uf" => ["required", "string", "size:2"],

            "itens" => ["nullable", "array", "min:1"],
            "itens.*.descricao" => ["required_with:itens", "string", "min:5"],
            "itens.*.quantidade" => ["required_with:itens", "integer", "min:1"],
            "itens.*.valor" => ["required_with:itens", "numeric", "min:0"],

            "numeroNotaFiscal" => ["nullable", "string"],
            "serieNotaFiscal" => ["nullable", "string"],
            "chaveNotaFiscal" => ["nullable", "string", "size:44"],
            "valorDeclarado" => ["nullable", "numeric", "min:0"],
            "avisoRecebimento" => ["nullable", "boolean"],
            "maoPropria" => ["nullable", "boolean"],
        ];
    }

    protected function customMessages(): array
    {
        return [
            "remetente.endereco.cep.regex" =>
                "CEP do remetente deve conter 8 dígitos.",
            "destinatario.endereco.cep.regex" =>
                "CEP do destinatário deve conter 8 dígitos.",
            "chaveNotaFiscal.size" =>
                "Chave NF-e deve conter exatamente 44 dígitos.",
            "itens.*.descricao.min" =>
                "Descrição do item deve ter no mínimo 5 caracteres (exigência da Declaração de Conteúdo).",
        ];
    }
}

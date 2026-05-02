<?php

declare(strict_types=1);

namespace Correios\Services;

use Correios\DTOs\Cep\EnderecoDTO;

class CepService extends AbstractCorreiosService
{
    protected function basePath(): string
    {
        return "/cep/v2";
    }

    /**
     * Consulta endereço por CEP.
     */
    public function consultar(string $cep): EnderecoDTO
    {
        $cep = preg_replace("/\D/", "", $cep);

        $resposta = $this->get("/enderecos/{$cep}");

        return EnderecoDTO::fromArray($resposta);
    }

    /**
     * Consulta vários CEPs de uma vez (até 20).
     *
     * @param  array<int, string>  $ceps
     * @return array<int, EnderecoDTO>
     */
    public function consultarLote(array $ceps): array
    {
        $ceps = array_map(
            fn($c) => preg_replace("/\D/", "", (string) $c),
            $ceps,
        );

        $resposta = $this->get("/enderecos", ["cep" => implode(",", $ceps)]);

        $itens = $resposta["itens"] ?? $resposta;

        return array_map(
            fn(array $end) => EnderecoDTO::fromArray($end),
            $itens,
        );
    }
}

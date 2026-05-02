<?php

declare(strict_types=1);

namespace Correios\Services;

use Correios\DTOs\Preco\CotacaoPrecoRequestDTO;
use Correios\DTOs\Preco\CotacaoPrecoResponseDTO;
use Correios\Exceptions\ValidacaoException;

class PrecoService extends AbstractCorreiosService
{
    protected function basePath(): string
    {
        return "/preco/v1";
    }

    /**
     * Cotação simples — um único produto.
     */
    public function cotar(
        CotacaoPrecoRequestDTO $request,
    ): CotacaoPrecoResponseDTO {
        $resposta = $this->post("/nacional", [
            "idLote" => "1",
            "parametrosProduto" => [
                array_merge(
                    ["coProduto" => $request->coProduto],
                    $request->toArray(),
                ),
            ],
        ]);

        $primeiro =
            $resposta[0] ?? ($resposta["parametrosProduto"][0] ?? $resposta);

        return CotacaoPrecoResponseDTO::fromArray($primeiro);
    }

    /**
     * Cotação em lote — até 100 produtos por chamada.
     *
     * @param  array<int, CotacaoPrecoRequestDTO>  $requests
     * @return array<int, CotacaoPrecoResponseDTO>
     */
    public function cotarLote(array $requests): array
    {
        if (count($requests) > 100) {
            throw ValidacaoException::valorInvalido(
                "requests",
                "Máximo de 100 cotações por lote.",
            );
        }

        $payload = array_map(
            fn(CotacaoPrecoRequestDTO $r) => array_merge(
                ["coProduto" => $r->coProduto],
                $r->toArray(),
            ),
            $requests,
        );

        $resposta = $this->post("/nacional", [
            "idLote" => "1",
            "parametrosProduto" => $payload,
        ]);

        $itens = $resposta["parametrosProduto"] ?? $resposta;

        return array_map(
            fn(array $item) => CotacaoPrecoResponseDTO::fromArray($item),
            $itens,
        );
    }
}

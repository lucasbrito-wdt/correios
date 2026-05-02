<?php

declare(strict_types=1);

namespace Correios\Services;

use Correios\DTOs\Prazo\PrazoResponseDTO;

class PrazoService extends AbstractCorreiosService
{
    protected function basePath(): string
    {
        return "/prazo/v1";
    }

    /**
     * Calcula prazo de entrega entre dois CEPs.
     */
    public function calcular(
        string $coProduto,
        string $cepOrigem,
        string $cepDestino,
        ?string $dataPostagem = null,
    ): PrazoResponseDTO {
        $payload = [
            "idLote" => "1",
            "parametrosPrazo" => [
                [
                    "coProduto" => $coProduto,
                    "cepOrigem" => preg_replace("/\D/", "", $cepOrigem),
                    "cepDestino" => preg_replace("/\D/", "", $cepDestino),
                    "dtPostagem" => $dataPostagem,
                ],
            ],
        ];

        $resposta = $this->post("/nacional", $payload);

        $primeiro =
            $resposta[0] ?? ($resposta["parametrosPrazo"][0] ?? $resposta);

        return PrazoResponseDTO::fromArray($primeiro);
    }

    /**
     * Calcula prazo em lote.
     *
     * @param  array<int, array{coProduto:string, cepOrigem:string, cepDestino:string, dataPostagem?:?string}>  $items
     * @return array<int, PrazoResponseDTO>
     */
    public function calcularLote(array $items): array
    {
        $payload = array_map(
            fn($item) => [
                "coProduto" => $item["coProduto"],
                "cepOrigem" => preg_replace("/\D/", "", $item["cepOrigem"]),
                "cepDestino" => preg_replace("/\D/", "", $item["cepDestino"]),
                "dtPostagem" => $item["dataPostagem"] ?? null,
            ],
            $items,
        );

        $resposta = $this->post("/nacional", [
            "idLote" => "1",
            "parametrosPrazo" => $payload,
        ]);

        $itens = $resposta["parametrosPrazo"] ?? $resposta;

        return array_map(
            fn(array $i) => PrazoResponseDTO::fromArray($i),
            $itens,
        );
    }
}

<?php

declare(strict_types=1);

namespace Correios\DTOs\Prazo;

use Correios\Contracts\DataTransferObject;

class PrazoResponseDTO implements DataTransferObject
{
    public function __construct(
        public readonly string $coProduto,
        public readonly int $prazoEntrega, // Dias úteis
        public readonly ?string $dataMaxEntrega = null,
        public readonly ?string $entregaSabado = null,
        public readonly ?string $tipoErro = null,
        public readonly ?string $mensagemErro = null,
    ) {}

    public static function fromArray(array $data): static
    {
        return new self(
            coProduto: (string) ($data["coProduto"] ?? ""),
            prazoEntrega: (int) ($data["prazoEntrega"] ?? 0),
            dataMaxEntrega: $data["dataMaxima"] ?? null,
            entregaSabado: $data["entregaSabado"] ?? null,
            tipoErro: $data["tpErro"] ?? null,
            mensagemErro: $data["msgErro"] ?? null,
        );
    }

    public function toArray(): array
    {
        return [
            "coProduto" => $this->coProduto,
            "prazoEntrega" => $this->prazoEntrega,
            "dataMaxEntrega" => $this->dataMaxEntrega,
            "entregaSabado" => $this->entregaSabado,
            "tipoErro" => $this->tipoErro,
            "mensagemErro" => $this->mensagemErro,
        ];
    }
}

<?php

declare(strict_types=1);

namespace Correios\DTOs\Preco;

use Correios\Contracts\DataTransferObject;

class CotacaoPrecoResponseDTO implements DataTransferObject
{
    public function __construct(
        public readonly string $coProduto,
        public readonly float $pcBase, // Preço base
        public readonly float $pcFinal, // Preço final (com adicionais)
        public readonly ?float $pcVlDeclarado = null,
        public readonly ?string $tipoErro = null,
        public readonly ?string $mensagemErro = null,
    ) {}

    public static function fromArray(array $data): static
    {
        return new self(
            coProduto: (string) ($data["coProduto"] ?? ""),
            pcBase: (float) ($data["pcBase"] ?? 0),
            pcFinal: (float) ($data["pcFinal"] ?? 0),
            pcVlDeclarado: isset($data["pcVlDeclarado"])
                ? (float) $data["pcVlDeclarado"]
                : null,
            tipoErro: $data["tpErro"] ?? null,
            mensagemErro: $data["msgErro"] ?? null,
        );
    }

    public function toArray(): array
    {
        return [
            "coProduto" => $this->coProduto,
            "pcBase" => $this->pcBase,
            "pcFinal" => $this->pcFinal,
            "pcVlDeclarado" => $this->pcVlDeclarado,
            "tipoErro" => $this->tipoErro,
            "mensagemErro" => $this->mensagemErro,
        ];
    }

    public function temErro(): bool
    {
        return $this->tipoErro !== null && $this->tipoErro !== "0";
    }
}

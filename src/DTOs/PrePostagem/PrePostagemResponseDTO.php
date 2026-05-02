<?php

declare(strict_types=1);

namespace Correios\DTOs\PrePostagem;

use Correios\Contracts\DataTransferObject;

class PrePostagemResponseDTO implements DataTransferObject
{
    public function __construct(
        public readonly string $id, // ID interno da pré-postagem
        public readonly ?string $codigoObjeto, // Código de rastreio (ex: "BR123456789BR")
        public readonly ?string $valorPostagem = null,
        public readonly ?string $status = null,
        public readonly ?string $dataCriacao = null,
        public readonly array $raw = [],
    ) {
        // Resposta crua da API
    }

    public static function fromArray(array $data): static
    {
        return new self(
            id: (string) ($data["id"] ?? ""),
            codigoObjeto: $data["codigoObjeto"] ?? null,
            valorPostagem: isset($data["valorPostagem"])
                ? (string) $data["valorPostagem"]
                : null,
            status: $data["statusAtual"] ?? null,
            dataCriacao: $data["dataPostagem"] ?? null,
            raw: $data,
        );
    }

    public function toArray(): array
    {
        return [
            "id" => $this->id,
            "codigoObjeto" => $this->codigoObjeto,
            "valorPostagem" => $this->valorPostagem,
            "status" => $this->status,
            "dataCriacao" => $this->dataCriacao,
            "raw" => $this->raw,
        ];
    }
}

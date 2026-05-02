<?php

declare(strict_types=1);

namespace Correios\DTOs\Rastro;

use Correios\Contracts\DataTransferObject;

class EventoRastroDTO implements DataTransferObject
{
    public function __construct(
        public readonly string $codigo,
        public readonly string $descricao,
        public readonly string $dtHrCriado,
        public readonly ?string $tipo = null,
        public readonly ?string $unidadeOrigem = null,
        public readonly ?string $unidadeDestino = null,
        public readonly ?string $cidade = null,
        public readonly ?string $uf = null,
    ) {}

    public static function fromArray(array $data): static
    {
        return new self(
            codigo: (string) ($data["codigo"] ?? ""),
            descricao: (string) ($data["descricao"] ?? ""),
            dtHrCriado: (string) ($data["dtHrCriado"] ?? ""),
            tipo: $data["tipo"] ?? null,
            unidadeOrigem: $data["unidade"]["endereco"]["cidade"] ?? null,
            unidadeDestino: $data["unidadeDestino"]["endereco"]["cidade"] ??
                null,
            cidade: $data["unidade"]["endereco"]["cidade"] ?? null,
            uf: $data["unidade"]["endereco"]["uf"] ?? null,
        );
    }

    public function toArray(): array
    {
        return [
            "codigo" => $this->codigo,
            "descricao" => $this->descricao,
            "dtHrCriado" => $this->dtHrCriado,
            "tipo" => $this->tipo,
            "unidadeOrigem" => $this->unidadeOrigem,
            "unidadeDestino" => $this->unidadeDestino,
            "cidade" => $this->cidade,
            "uf" => $this->uf,
        ];
    }
}

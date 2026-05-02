<?php

declare(strict_types=1);

namespace Correios\DTOs\Cep;

use Correios\Contracts\DataTransferObject;

class EnderecoDTO implements DataTransferObject
{
    public function __construct(
        public readonly string $cep,
        public readonly ?string $logradouro = null,
        public readonly ?string $bairro = null,
        public readonly ?string $localidade = null,
        public readonly ?string $uf = null,
        public readonly ?string $complemento = null,
        public readonly ?string $numero = null,
    ) {}

    public static function fromArray(array $data): static
    {
        return new self(
            cep: (string) ($data["cep"] ?? ""),
            logradouro: $data["logradouro"] ?? null,
            bairro: $data["bairro"] ?? null,
            localidade: $data["localidade"] ?? ($data["cidade"] ?? null),
            uf: $data["uf"] ?? null,
            complemento: $data["complemento"] ?? null,
            numero: $data["numero"] ?? null,
        );
    }

    public function toArray(): array
    {
        return array_filter(
            [
                "cep" => $this->cep,
                "logradouro" => $this->logradouro,
                "bairro" => $this->bairro,
                "localidade" => $this->localidade,
                "uf" => $this->uf,
                "complemento" => $this->complemento,
                "numero" => $this->numero,
            ],
            fn($v) => $v !== null,
        );
    }
}

<?php

declare(strict_types=1);

namespace Correios\DTOs\Rastro;

use Correios\Contracts\DataTransferObject;

class ObjetoRastreadoDTO implements DataTransferObject
{
    /**
     * @param  array<int, EventoRastroDTO>  $eventos
     */
    public function __construct(
        public readonly string $codigoObjeto,
        public readonly string $tipoPostal,
        public readonly array $eventos,
    ) {}

    public static function fromArray(array $data): static
    {
        $eventos = array_map(
            fn(array $e) => EventoRastroDTO::fromArray($e),
            $data["eventos"] ?? [],
        );

        return new self(
            codigoObjeto: (string) ($data["codObjeto"] ?? ""),
            tipoPostal: (string) ($data["tipoPostal"]["descricao"] ?? ""),
            eventos: $eventos,
        );
    }

    public function toArray(): array
    {
        return [
            "codigoObjeto" => $this->codigoObjeto,
            "tipoPostal" => $this->tipoPostal,
            "eventos" => array_map(
                fn(EventoRastroDTO $e) => $e->toArray(),
                $this->eventos,
            ),
        ];
    }

    public function ultimoEvento(): ?EventoRastroDTO
    {
        return $this->eventos[0] ?? null;
    }

    public function foiEntregue(): bool
    {
        $ultimo = $this->ultimoEvento();
        if ($ultimo === null) {
            return false;
        }

        // Códigos BDE/BDI = entregue
        return in_array($ultimo->codigo, ["BDE", "BDI", "BDR"], true);
    }
}

<?php

declare(strict_types=1);

namespace Correios\Contracts;

interface DataTransferObject
{
    /**
     * Constrói o DTO a partir de um array de dados (geralmente vindo da API).
     */
    public static function fromArray(array $data): static;

    /**
     * Serializa o DTO de volta para array (para envio à API ou logging).
     */
    public function toArray(): array;
}

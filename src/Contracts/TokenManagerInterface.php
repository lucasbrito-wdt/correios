<?php

declare(strict_types=1);

namespace Correios\Contracts;

interface TokenManagerInterface
{
    /**
     * Retorna um token JWT válido (do cache ou gerando um novo).
     */
    public function getToken(): string;

    /**
     * Invalida o token cacheado, forçando a próxima chamada a gerar um novo.
     */
    public function invalidate(): void;
}

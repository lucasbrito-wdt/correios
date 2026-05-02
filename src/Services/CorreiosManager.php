<?php

declare(strict_types=1);

namespace Correios\Services;

/**
 * Fachada agregadora — ponto único de acesso a todos os services.
 *
 * Uso:
 *   app(CorreiosManager::class)->preco()->cotar(...);
 *   app(CorreiosManager::class)->rastro()->rastrear(...);
 */
class CorreiosManager
{
    public function __construct(
        protected readonly CepService $cep,
        protected readonly PrecoService $preco,
        protected readonly PrazoService $prazo,
        protected readonly RastroService $rastro,
        protected readonly PrePostagemService $prePostagem,
    ) {}

    public function cep(): CepService
    {
        return $this->cep;
    }

    public function preco(): PrecoService
    {
        return $this->preco;
    }

    public function prazo(): PrazoService
    {
        return $this->prazo;
    }

    public function rastro(): RastroService
    {
        return $this->rastro;
    }

    public function prePostagem(): PrePostagemService
    {
        return $this->prePostagem;
    }
}

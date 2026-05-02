<?php

declare(strict_types=1);

namespace Correios\Facades;

use Correios\Services\CepService;
use Correios\Services\CorreiosManager;
use Correios\Services\PrazoService;
use Correios\Services\PrecoService;
use Correios\Services\PrePostagemService;
use Correios\Services\RastroService;
use Illuminate\Support\Facades\Facade;

/**
 * @method static CepService          cep()
 * @method static PrecoService        preco()
 * @method static PrazoService        prazo()
 * @method static RastroService       rastro()
 * @method static PrePostagemService  prePostagem()
 *
 * @see CorreiosManager
 */
class Correios extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return CorreiosManager::class;
    }
}

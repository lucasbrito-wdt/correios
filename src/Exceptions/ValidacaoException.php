<?php

declare(strict_types=1);

namespace Correios\Exceptions;

class ValidacaoException extends CorreiosException
{
    public static function campoObrigatorio(string $campo): self
    {
        return new self("Campo obrigatório não informado: {$campo}");
    }

    public static function valorInvalido(string $campo, string $detalhe): self
    {
        return new self("Valor inválido em '{$campo}': {$detalhe}");
    }
}

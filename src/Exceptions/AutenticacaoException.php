<?php

declare(strict_types=1);

namespace Correios\Exceptions;

class AutenticacaoException extends CorreiosException
{
    public static function falhaTokenJWT(string $detalhe): self
    {
        return new self("Falha ao gerar token JWT dos Correios: {$detalhe}");
    }

    public static function credenciaisAusentes(string $campo): self
    {
        return new self(
            "Credencial obrigatória ausente: {$campo}. Configure no .env.",
        );
    }

    public static function tokenExpirado(): self
    {
        return new self(
            "Token dos Correios expirado e a renovação automática falhou.",
        );
    }
}

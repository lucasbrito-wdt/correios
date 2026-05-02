<?php

declare(strict_types=1);

namespace Correios\Support;

use Illuminate\Support\Facades\Log;

class LogHelper
{
    public static function info(string $mensagem, array $contexto = []): void
    {
        if (!config("correios.logging.enabled", true)) {
            return;
        }

        Log::channel(config("correios.logging.channel", "stack"))->info(
            "[Correios] {$mensagem}",
            self::mascarar($contexto),
        );
    }

    public static function warning(string $mensagem, array $contexto = []): void
    {
        if (!config("correios.logging.enabled", true)) {
            return;
        }

        Log::channel(config("correios.logging.channel", "stack"))->warning(
            "[Correios] {$mensagem}",
            self::mascarar($contexto),
        );
    }

    public static function error(string $mensagem, array $contexto = []): void
    {
        if (!config("correios.logging.enabled", true)) {
            return;
        }

        Log::channel(config("correios.logging.channel", "stack"))->error(
            "[Correios] {$mensagem}",
            self::mascarar($contexto),
        );
    }

    /**
     * Mascara campos sensíveis nos logs (token, código de acesso etc).
     */
    protected static function mascarar(array $contexto): array
    {
        $sensiveis = ["token", "codigo_acesso", "authorization", "password"];

        array_walk_recursive($contexto, function (&$valor, $chave) use (
            $sensiveis,
        ) {
            if (
                is_string($chave) &&
                in_array(strtolower($chave), $sensiveis, true)
            ) {
                $valor = "***REDACTED***";
            }
        });

        return $contexto;
    }
}

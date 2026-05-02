<?php

declare(strict_types=1);

namespace Correios\Exceptions;

use Illuminate\Http\Client\Response;

class RequestException extends CorreiosException
{
    public static function deResponse(
        Response $response,
        string $endpoint,
    ): self {
        $status = $response->status();
        $body = $response->body();

        $exception = new self(
            "Falha na chamada Correios [{$endpoint}] HTTP {$status}: {$body}",
            $status,
        );

        return $exception->comContexto([
            "endpoint" => $endpoint,
            "status" => $status,
            "body" => $response->json() ?? $body,
        ]);
    }
}

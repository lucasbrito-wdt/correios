<?php

declare(strict_types=1);

namespace Correios\Services;

use Correios\Exceptions\RequestException;
use Correios\Http\Client\CorreiosHttpClient;
use Correios\Support\LogHelper;
use Illuminate\Http\Client\Response;

abstract class AbstractCorreiosService
{
    public function __construct(
        protected readonly CorreiosHttpClient $client,
    ) {}

    /**
     * Cada service especializado define seu próprio prefixo de rota.
     * Ex: "/preco/v1", "/prazo/v1", "/cep/v2", "/rastro/v1", "/prepostagem/v1"
     */
    abstract protected function basePath(): string;

    /**
     * GET helper.
     */
    protected function get(string $endpoint, array $query = []): array
    {
        return $this->processar(
            $this->client->request()->get($this->buildUrl($endpoint), $query),
            $endpoint,
        );
    }

    /**
     * POST helper.
     */
    protected function post(string $endpoint, array $payload = []): array
    {
        return $this->processar(
            $this->client
                ->request()
                ->post($this->buildUrl($endpoint), $payload),
            $endpoint,
        );
    }

    /**
     * PUT helper.
     */
    protected function put(string $endpoint, array $payload = []): array
    {
        return $this->processar(
            $this->client->request()->put($this->buildUrl($endpoint), $payload),
            $endpoint,
        );
    }

    /**
     * DELETE helper.
     */
    protected function delete(string $endpoint, array $payload = []): array
    {
        return $this->processar(
            $this->client
                ->request()
                ->delete($this->buildUrl($endpoint), $payload),
            $endpoint,
        );
    }

    /**
     * Trata a resposta uniformemente: log, validação de status e retorno em array.
     */
    protected function processar(Response $response, string $endpoint): array
    {
        if ($response->failed()) {
            LogHelper::error("Falha na requisição", [
                "endpoint" => $endpoint,
                "status" => $response->status(),
                "body" => $response->body(),
            ]);

            throw RequestException::deResponse($response, $endpoint);
        }

        LogHelper::info("Requisição bem-sucedida", [
            "endpoint" => $endpoint,
            "status" => $response->status(),
        ]);

        return $response->json() ?? [];
    }

    protected function buildUrl(string $endpoint): string
    {
        return rtrim($this->basePath(), "/") . "/" . ltrim($endpoint, "/");
    }
}

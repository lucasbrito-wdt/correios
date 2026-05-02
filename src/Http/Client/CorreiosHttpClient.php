<?php

declare(strict_types=1);

namespace Correios\Http\Client;

use Correios\Contracts\TokenManagerInterface;
use Correios\Support\LogHelper;
use Illuminate\Http\Client\Factory as HttpFactory;
use Illuminate\Http\Client\PendingRequest;

class CorreiosHttpClient
{
    public function __construct(
        protected readonly HttpFactory $http,
        protected readonly TokenManagerInterface $tokenManager,
        protected readonly array $config,
    ) {}

    /**
     * Retorna um PendingRequest pré-configurado com baseUrl, token e retry inteligente.
     */
    public function request(): PendingRequest
    {
        return $this->http
            ->baseUrl($this->resolveBaseUrl())
            ->withToken($this->tokenManager->getToken())
            ->acceptJson()
            ->asJson()
            ->timeout($this->config["http"]["timeout"] ?? 30)
            ->retry(
                $this->config["http"]["retry_times"] ?? 2,
                $this->config["http"]["retry_sleep_ms"] ?? 500,
                function ($exception, $request) {
                    // Se o servidor retornou 401, o token foi invalidado/expirou.
                    // Limpamos o cache e retornamos true pra forçar nova tentativa.
                    if ($exception->response?->status() === 401) {
                        LogHelper::warning(
                            "Token invalidado pelo servidor (401), renovando...",
                        );
                        $this->tokenManager->invalidate();
                        $request->withToken($this->tokenManager->getToken());
                        return true;
                    }

                    // 5xx: tenta de novo
                    return $exception->response?->serverError() ?? false;
                },
                throw: false,
            );
    }

    public function baseUrl(): string
    {
        return $this->resolveBaseUrl();
    }

    protected function resolveBaseUrl(): string
    {
        $ambiente = $this->config["ambiente"] ?? "homologacao";
        return rtrim($this->config["urls"][$ambiente] ?? "", "/");
    }
}

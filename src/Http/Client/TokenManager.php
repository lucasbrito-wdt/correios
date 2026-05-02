<?php

declare(strict_types=1);

namespace Correios\Http\Client;

use Correios\Contracts\TokenManagerInterface;
use Correios\Exceptions\AutenticacaoException;
use Correios\Support\LogHelper;
use Illuminate\Contracts\Cache\Repository as CacheRepository;
use Illuminate\Http\Client\Factory as HttpFactory;

class TokenManager implements TokenManagerInterface
{
    public function __construct(
        protected readonly HttpFactory $http,
        protected readonly CacheRepository $cache,
        protected readonly array $config,
    ) {}

    public function getToken(): string
    {
        $chave = $this->config["cache"]["key"] ?? "correios:token";
        $ttl = ($this->config["cache"]["ttl_horas"] ?? 23) * 3600;

        return $this->cache->remember(
            $chave,
            $ttl,
            fn() => $this->gerarNovoToken(),
        );
    }

    public function invalidate(): void
    {
        $this->cache->forget($this->config["cache"]["key"] ?? "correios:token");
    }

    /**
     * Faz a chamada de autenticação ao CWS e retorna o JWT.
     */
    protected function gerarNovoToken(): string
    {
        $this->validarCredenciais();

        $tipo = $this->config["tipo_autenticacao"] ?? "cartao_postagem";
        [$endpoint, $body] = $this->montarPayloadAutenticacao($tipo);

        $baseUrl = $this->resolveBaseUrl();

        LogHelper::info("Solicitando novo token", [
            "tipo" => $tipo,
            "endpoint" => $endpoint,
        ]);

        $response = $this->http
            ->withBasicAuth(
                $this->config["credenciais"]["usuario"],
                $this->config["credenciais"]["codigo_acesso"],
            )
            ->acceptJson()
            ->asJson()
            ->timeout($this->config["http"]["timeout"] ?? 15)
            ->retry(
                $this->config["http"]["retry_times"] ?? 2,
                $this->config["http"]["retry_sleep_ms"] ?? 500,
                throw: false,
            )
            ->post("{$baseUrl}{$endpoint}", $body);

        if ($response->failed()) {
            LogHelper::error("Falha na autenticação", [
                "status" => $response->status(),
                "body" => $response->body(),
            ]);

            throw AutenticacaoException::falhaTokenJWT(
                "HTTP {$response->status()} - {$response->body()}",
            );
        }

        $token = $response->json("token");

        if (!is_string($token) || $token === "") {
            throw AutenticacaoException::falhaTokenJWT(
                "Token não retornado pela API.",
            );
        }

        LogHelper::info("Token obtido com sucesso", [
            "expira_em" => $response->json("expiraEm"),
        ]);

        return $token;
    }

    protected function validarCredenciais(): void
    {
        $obrigatorios = ["usuario", "codigo_acesso"];

        foreach ($obrigatorios as $campo) {
            if (empty($this->config["credenciais"][$campo] ?? null)) {
                throw AutenticacaoException::credenciaisAusentes(
                    "credenciais.{$campo}",
                );
            }
        }
    }

    /**
     * Define endpoint e body conforme o tipo de autenticação.
     *
     * @return array{0: string, 1: array}
     */
    protected function montarPayloadAutenticacao(string $tipo): array
    {
        return match ($tipo) {
            "usuario" => ["/token/v1/autentica", []],

            "contrato" => [
                "/token/v1/autentica/contrato",
                ["numero" => $this->exigirCredencial("contrato")],
            ],

            "cartao_postagem" => [
                "/token/v1/autentica/cartaopostagem",
                ["numero" => $this->exigirCredencial("cartao_postagem")],
            ],

            default => throw AutenticacaoException::falhaTokenJWT(
                "Tipo de autenticação inválido: {$tipo}",
            ),
        };
    }

    protected function exigirCredencial(string $campo): string
    {
        $valor = $this->config["credenciais"][$campo] ?? null;

        if (empty($valor)) {
            throw AutenticacaoException::credenciaisAusentes(
                "credenciais.{$campo}",
            );
        }

        return $valor;
    }

    protected function resolveBaseUrl(): string
    {
        $ambiente = $this->config["ambiente"] ?? "homologacao";
        return rtrim($this->config["urls"][$ambiente] ?? "", "/");
    }
}

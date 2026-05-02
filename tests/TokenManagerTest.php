<?php

declare(strict_types=1);

namespace Correios\Tests;

use Correios\Exceptions\AutenticacaoException;
use Correios\Http\Client\TokenManager;
use Illuminate\Cache\ArrayStore;
use Illuminate\Cache\Repository;
use Illuminate\Http\Client\Factory;
use Illuminate\Support\Facades\Http;
use Orchestra\Testbench\TestCase;

class TokenManagerTest extends TestCase
{
    public function test_gera_e_cacheia_token(): void
    {
        Http::fake([
            "apihom.correios.com.br/token/v1/autentica/cartaopostagem" => Http::response(
                [
                    "token" => "jwt-de-teste",
                    "expiraEm" => now()->addHours(24)->toIso8601String(),
                ],
                201,
            ),
        ]);

        $manager = new TokenManager(
            http: $this->app->make(Factory::class),
            cache: new Repository(new ArrayStore()),
            config: $this->configBase(),
        );

        $this->assertSame("jwt-de-teste", $manager->getToken());
        // Segunda chamada deve vir do cache (sem nova requisição)
        $this->assertSame("jwt-de-teste", $manager->getToken());

        Http::assertSentCount(1);
    }

    public function test_lanca_excecao_quando_credenciais_ausentes(): void
    {
        $this->expectException(AutenticacaoException::class);

        $config = $this->configBase();
        $config["credenciais"]["usuario"] = null;

        $manager = new TokenManager(
            http: $this->app->make(Factory::class),
            cache: new Repository(new ArrayStore()),
            config: $config,
        );

        $manager->getToken();
    }

    protected function configBase(): array
    {
        return [
            "ambiente" => "homologacao",
            "urls" => [
                "producao" => "https://api.correios.com.br",
                "homologacao" => "https://apihom.correios.com.br",
            ],
            "credenciais" => [
                "usuario" => "usuario_teste",
                "codigo_acesso" => "codigo_teste",
                "cartao_postagem" => "0070123456",
                "contrato" => "9912345678",
            ],
            "tipo_autenticacao" => "cartao_postagem",
            "cache" => [
                "key" => "correios:token:test",
                "ttl_horas" => 23,
            ],
            "http" => [
                "timeout" => 15,
                "retry_times" => 1,
                "retry_sleep_ms" => 100,
            ],
            "logging" => [
                "enabled" => false,
            ],
        ];
    }
}

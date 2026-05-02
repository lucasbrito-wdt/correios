<?php

declare(strict_types=1);

namespace Correios\Providers;

use Correios\Contracts\TokenManagerInterface;
use Correios\Http\Client\CorreiosHttpClient;
use Correios\Http\Client\TokenManager;
use Correios\Services\CepService;
use Correios\Services\CorreiosManager;
use Correios\Services\PrazoService;
use Correios\Services\PrecoService;
use Correios\Services\PrePostagemService;
use Correios\Services\RastroService;
use Illuminate\Contracts\Cache\Factory as CacheFactory;
use Illuminate\Http\Client\Factory as HttpFactory;
use Illuminate\Support\ServiceProvider;

class CorreiosServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__ . "/../../config/correios.php",
            "correios",
        );

        $this->registrarTokenManager();
        $this->registrarHttpClient();
        $this->registrarServices();
        $this->registrarManager();
    }

    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->publishes(
                [
                    __DIR__ . "/../../config/correios.php" => config_path(
                        "correios.php",
                    ),
                ],
                "correios-config",
            );
        }
    }

    protected function registrarTokenManager(): void
    {
        $this->app->singleton(TokenManagerInterface::class, function ($app) {
            $config = $app["config"]->get("correios");
            $store = $config["cache"]["store"] ?? null;

            return new TokenManager(
                http: $app->make(HttpFactory::class),
                cache: $app->make(CacheFactory::class)->store($store),
                config: $config,
            );
        });
    }

    protected function registrarHttpClient(): void
    {
        $this->app->singleton(CorreiosHttpClient::class, function ($app) {
            return new CorreiosHttpClient(
                http: $app->make(HttpFactory::class),
                tokenManager: $app->make(TokenManagerInterface::class),
                config: $app["config"]->get("correios"),
            );
        });
    }

    protected function registrarServices(): void
    {
        $services = [
            CepService::class,
            PrecoService::class,
            PrazoService::class,
            RastroService::class,
            PrePostagemService::class,
        ];

        foreach ($services as $service) {
            $this->app->singleton(
                $service,
                fn($app) => new $service($app->make(CorreiosHttpClient::class)),
            );
        }
    }

    protected function registrarManager(): void
    {
        $this->app->singleton(
            CorreiosManager::class,
            fn($app) => new CorreiosManager(
                cep: $app->make(CepService::class),
                preco: $app->make(PrecoService::class),
                prazo: $app->make(PrazoService::class),
                rastro: $app->make(RastroService::class),
                prePostagem: $app->make(PrePostagemService::class),
            ),
        );
    }

    public function provides(): array
    {
        return [
            TokenManagerInterface::class,
            CorreiosHttpClient::class,
            CepService::class,
            PrecoService::class,
            PrazoService::class,
            RastroService::class,
            PrePostagemService::class,
            CorreiosManager::class,
        ];
    }
}

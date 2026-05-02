# Laravel Correios — Pacote de integração com o CWS

Pacote Laravel para integrar com as APIs do CWS (Correios Web Services): Token, CEP, Preço, Prazo, Rastro e Pré-Postagem.

Arquitetura limpa com **classe abstrata base**, **services especializados**, **DTOs**, **Form Requests para validação**, **token JWT cacheado no Redis** e **retry automático em 401**.

## Requisitos

- PHP 8.2+
- Laravel 11 ou 12
- Contrato ativo com os Correios + cartão de postagem
- Credenciais geradas no portal `cws.correios.com.br`

## Instalação

```bash
composer require codifytech/laravel-correios
```

Publique a config:

```bash
php artisan vendor:publish --tag=correios-config
```

## Configuração

Adicione no `.env`:

```env
CORREIOS_AMBIENTE=homologacao        # ou 'producao'
CORREIOS_USUARIO=seu_id_correios
CORREIOS_CODIGO_ACESSO=seu_codigo_gerado_no_cws
CORREIOS_CARTAO_POSTAGEM=0070123456
CORREIOS_CONTRATO=9912345678
CORREIOS_TIPO_AUTH=cartao_postagem   # ou 'contrato' / 'usuario'
CORREIOS_CACHE_STORE=redis
```

## Estrutura do pacote

```
src/
├── Contracts/                          # Interfaces (DTO, TokenManager)
├── DTOs/                               # Data Transfer Objects por domínio
│   ├── Cep/EnderecoDTO.php
│   ├── Preco/CotacaoPrecoRequestDTO.php
│   ├── Preco/CotacaoPrecoResponseDTO.php
│   ├── Prazo/PrazoResponseDTO.php
│   ├── Rastro/EventoRastroDTO.php
│   ├── Rastro/ObjetoRastreadoDTO.php
│   └── PrePostagem/...
├── Exceptions/                         # CorreiosException, Autenticacao, Request, Validacao
├── Facades/Correios.php                # Facade Laravel
├── Http/
│   ├── Client/
│   │   ├── TokenManager.php            # Gerencia JWT (cache + renovação)
│   │   └── CorreiosHttpClient.php      # Wrapper Http::baseUrl + auth + retry
│   └── Requests/                       # Form Requests para validação
│       ├── AbstractCorreiosRequest.php
│       ├── CotacaoPrecoRequest.php
│       ├── CotacaoPrazoRequest.php
│       ├── ConsultaCepRequest.php
│       ├── PrePostagemRequest.php
│       └── RastreioRequest.php
├── Services/
│   ├── AbstractCorreiosService.php     # 🟢 Classe abstrata base
│   ├── CepService.php
│   ├── PrecoService.php
│   ├── PrazoService.php
│   ├── RastroService.php
│   ├── PrePostagemService.php
│   └── CorreiosManager.php             # Fachada agregadora
├── Support/LogHelper.php               # Logging com mascaramento de credenciais
└── Providers/CorreiosServiceProvider.php
```

## Fluxo de autenticação (resumo)

1. `TokenManager` checa o Redis pela chave `correios:token`
2. Se não existir, faz `POST /token/v1/autentica/cartaopostagem` com Basic Auth
3. Cacheia o JWT por 23h (1h de margem antes do vencimento real de 24h)
4. Toda chamada subsequente injeta `Authorization: Bearer {jwt}` automaticamente
5. Se algum endpoint devolver `401`, o cache é invalidado e o token regenerado em retry

## Como usar

### Via Facade

```php
use Correios\Facades\Correios;
use Correios\DTOs\Preco\CotacaoPrecoRequestDTO;

// CEP
$endereco = Correios::cep()->consultar('58015-430');
echo $endereco->logradouro;

// Cotação de frete
$cotacao = Correios::preco()->cotar(
    CotacaoPrecoRequestDTO::fromArray([
        'coProduto'  => '03220',     // SEDEX
        'cepOrigem'  => '58015430',
        'cepDestino' => '01001000',
        'psObjeto'   => 1500,        // gramas
        'comprimento'=> 30,
        'largura'    => 20,
        'altura'     => 10,
    ])
);
echo "Frete: R$ {$cotacao->pcFinal}";

// Prazo
$prazo = Correios::prazo()->calcular('03220', '58015430', '01001000');
echo "Prazo: {$prazo->prazoEntrega} dias úteis";

// Rastreio
$objeto = Correios::rastro()->rastrear('BR123456789BR');
echo $objeto->ultimoEvento()->descricao;
echo $objeto->foiEntregue() ? 'Entregue!' : 'Em trânsito';
```

### Via injeção de dependência (recomendado)

```php
use Correios\Services\PrecoService;

class CalcularFreteAction
{
    public function __construct(
        private readonly PrecoService $precoService,
    ) {}

    public function __invoke(CotacaoPrecoRequest $request): CotacaoPrecoResponseDTO
    {
        $dto = CotacaoPrecoRequestDTO::fromArray($request->validated());
        return $this->precoService->cotar($dto);
    }
}
```

### Cotação em lote (até 100)

```php
$resultados = Correios::preco()->cotarLote([
    CotacaoPrecoRequestDTO::fromArray([...]),
    CotacaoPrecoRequestDTO::fromArray([...]),
    // ...
]);
```

### Pré-Postagem (gerar etiqueta)

```php
use Correios\DTOs\PrePostagem\PrePostagemRequestDTO;

$pre = Correios::prePostagem()->criar(
    PrePostagemRequestDTO::fromArray([
        'codigoServico' => '03220',
        'pesoGramas'    => 1500,
        'dimensoes'     => ['altura' => 10, 'largura' => 20, 'comprimento' => 30],
        'remetente'     => [...],
        'destinatario'  => [...],
        'itens'         => [
            ['descricao' => 'Camiseta personalizada', 'quantidade' => 1, 'valor' => 89.90],
        ],
        'numeroNotaFiscal' => '12345',
        'chaveNotaFiscal'  => '35200114200166000187550010000000071123456789',
    ])
);

echo "Código: {$pre->codigoObjeto}";

// Gerar PDF da etiqueta
$rotulo = Correios::prePostagem()->gerarRotulo($pre->id);
```

## Pontos de extensão

### Criando um service novo

Estenda `AbstractCorreiosService`:

```php
namespace App\Correios;

use Correios\Services\AbstractCorreiosService;

class MeuServiceCustom extends AbstractCorreiosService
{
    protected function basePath(): string
    {
        return '/meu-endpoint/v1';
    }

    public function minhaOperacao(array $payload): array
    {
        return $this->post('/operacao', $payload);
    }
}
```

Registre no seu `AppServiceProvider`:

```php
$this->app->singleton(MeuServiceCustom::class, fn ($app) => new MeuServiceCustom(
    $app->make(\Correios\Http\Client\CorreiosHttpClient::class)
));
```

Pronto — autenticação, retry, logging e tratamento de erros já vêm de graça.

## Testes

```bash
composer install
vendor/bin/phpunit
```

Use `Http::fake()` pra simular as respostas do CWS.

## Boas práticas

- **Sempre comece em homologação** (`apihom.correios.com.br`) antes de mudar pra produção
- **Cacheie cotações de frete por CEP+peso** — os Correios cobram por consulta em alguns contratos
- **Use jobs em fila pro polling de rastreio** — não consulte sob demanda do usuário
- **Faça webhook ou polling agendado** — armazene histórico no seu banco pra timeline rica no painel
- **Monitore o status code 401** — se aparecer com frequência, sua chave de acesso pode ter sido revogada (validade de 180 dias para subdelegações)

## Códigos de serviço comuns

| Código | Serviço             |
|--------|---------------------|
| 03220  | SEDEX (contrato)    |
| 03298  | PAC (contrato)      |
| 04162  | SEDEX (varejo)      |
| 04510  | PAC (varejo)        |
| 04669  | SEDEX 12            |
| 04227  | SEDEX 10            |

## Códigos de eventos de rastreio mais relevantes

| Código | Significado            |
|--------|------------------------|
| BDE/BDI/BDR | Objeto entregue   |
| OEC    | Saiu para entrega       |
| LDI    | Aguardando retirada     |
| RO     | Encaminhado             |
| PO     | Postado                 |

## Licença

MIT

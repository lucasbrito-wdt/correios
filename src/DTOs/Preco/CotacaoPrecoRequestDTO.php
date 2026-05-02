<?php

declare(strict_types=1);

namespace Correios\DTOs\Preco;

use Correios\Contracts\DataTransferObject;
use Correios\Exceptions\ValidacaoException;

class CotacaoPrecoRequestDTO implements DataTransferObject
{
    public function __construct(
        public readonly string $coProduto, // Código do serviço: 03220 (SEDEX), 03298 (PAC), etc
        public readonly string $cepOrigem,
        public readonly string $cepDestino,
        public readonly int $psObjeto, // Peso em gramas
        public readonly int $tpObjeto = 2, // 1=Envelope, 2=Pacote, 3=Rolo
        public readonly ?int $comprimento = null,
        public readonly ?int $largura = null,
        public readonly ?int $altura = null,
        public readonly ?int $diametro = null,
        public readonly ?float $vlDeclarado = null,
        public readonly bool $servicoAdicional = false,
    ) {}

    public static function fromArray(array $data): static
    {
        foreach (
            ["coProduto", "cepOrigem", "cepDestino", "psObjeto"]
            as $obrigatorio
        ) {
            if (!isset($data[$obrigatorio])) {
                throw ValidacaoException::campoObrigatorio($obrigatorio);
            }
        }

        return new self(
            coProduto: (string) $data["coProduto"],
            cepOrigem: self::limparCep($data["cepOrigem"]),
            cepDestino: self::limparCep($data["cepDestino"]),
            psObjeto: (int) $data["psObjeto"],
            tpObjeto: (int) ($data["tpObjeto"] ?? 2),
            comprimento: isset($data["comprimento"])
                ? (int) $data["comprimento"]
                : null,
            largura: isset($data["largura"]) ? (int) $data["largura"] : null,
            altura: isset($data["altura"]) ? (int) $data["altura"] : null,
            diametro: isset($data["diametro"]) ? (int) $data["diametro"] : null,
            vlDeclarado: isset($data["vlDeclarado"])
                ? (float) $data["vlDeclarado"]
                : null,
            servicoAdicional: (bool) ($data["servicoAdicional"] ?? false),
        );
    }

    public function toArray(): array
    {
        return array_filter(
            [
                "coProduto" => $this->coProduto,
                "cepOrigem" => $this->cepOrigem,
                "cepDestino" => $this->cepDestino,
                "psObjeto" => $this->psObjeto,
                "tpObjeto" => $this->tpObjeto,
                "comprimento" => $this->comprimento,
                "largura" => $this->largura,
                "altura" => $this->altura,
                "diametro" => $this->diametro,
                "vlDeclarado" => $this->vlDeclarado,
            ],
            fn($v) => $v !== null,
        );
    }

    protected static function limparCep(string $cep): string
    {
        return preg_replace("/\D/", "", $cep) ?? $cep;
    }
}

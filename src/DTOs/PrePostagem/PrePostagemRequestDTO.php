<?php

declare(strict_types=1);

namespace Correios\DTOs\PrePostagem;

use Correios\Contracts\DataTransferObject;
use Correios\Exceptions\ValidacaoException;

class PrePostagemRequestDTO implements DataTransferObject
{
    /**
     * @param  array  $remetente   Dados do remetente (nome, endereço, CEP etc)
     * @param  array  $destinatario  Dados do destinatário
     * @param  string $codigoServico Ex: "03220" (SEDEX), "03298" (PAC)
     * @param  array  $itens        Itens declarados
     */
    public function __construct(
        public readonly array $remetente,
        public readonly array $destinatario,
        public readonly string $codigoServico,
        public readonly int $pesoGramas,
        public readonly array $dimensoes, // ['comprimento'=>x, 'largura'=>y, 'altura'=>z]
        public readonly array $itens = [],
        public readonly ?string $idCorreios = null,
        public readonly ?string $cartaoPostagem = null,
        public readonly ?string $numeroNotaFiscal = null,
        public readonly ?string $serieNotaFiscal = null,
        public readonly ?string $chaveNotaFiscal = null,
        public readonly ?float $valorDeclarado = null,
        public readonly bool $aviso_recebimento = false,
        public readonly bool $maoPropria = false,
    ) {}

    public static function fromArray(array $data): static
    {
        foreach (
            [
                "remetente",
                "destinatario",
                "codigoServico",
                "pesoGramas",
                "dimensoes",
            ]
            as $campo
        ) {
            if (!isset($data[$campo])) {
                throw ValidacaoException::campoObrigatorio($campo);
            }
        }

        return new self(
            remetente: $data["remetente"],
            destinatario: $data["destinatario"],
            codigoServico: (string) $data["codigoServico"],
            pesoGramas: (int) $data["pesoGramas"],
            dimensoes: $data["dimensoes"],
            itens: $data["itens"] ?? [],
            idCorreios: $data["idCorreios"] ?? null,
            cartaoPostagem: $data["cartaoPostagem"] ?? null,
            numeroNotaFiscal: $data["numeroNotaFiscal"] ?? null,
            serieNotaFiscal: $data["serieNotaFiscal"] ?? null,
            chaveNotaFiscal: $data["chaveNotaFiscal"] ?? null,
            valorDeclarado: isset($data["valorDeclarado"])
                ? (float) $data["valorDeclarado"]
                : null,
            aviso_recebimento: (bool) ($data["avisoRecebimento"] ?? false),
            maoPropria: (bool) ($data["maoPropria"] ?? false),
        );
    }

    /**
     * Constrói o payload no formato exato esperado pela API de Pré-Postagem.
     */
    public function toArray(): array
    {
        $servicosAdicionais = [];
        if ($this->valorDeclarado !== null) {
            $servicosAdicionais[] = [
                "codigoServicoAdicional" => "019",
                "valorDeclarado" => $this->valorDeclarado,
            ];
        }
        if ($this->aviso_recebimento) {
            $servicosAdicionais[] = ["codigoServicoAdicional" => "001"];
        }
        if ($this->maoPropria) {
            $servicosAdicionais[] = ["codigoServicoAdicional" => "002"];
        }

        return array_filter(
            [
                "idCorreios" => $this->idCorreios,
                "codigoServico" => $this->codigoServico,
                "numeroCartaoPostagem" => $this->cartaoPostagem,
                "pesoInformado" => $this->pesoGramas,
                "codigoFormatoObjeto" => "002", // Pacote/caixa
                "alturaInformada" => $this->dimensoes["altura"] ?? null,
                "larguraInformada" => $this->dimensoes["largura"] ?? null,
                "comprimentoInformado" =>
                    $this->dimensoes["comprimento"] ?? null,
                "remetente" => $this->remetente,
                "destinatario" => $this->destinatario,
                "servicosAdicionais" => $servicosAdicionais ?: null,
                "itensDeclaracaoConteudo" => $this->itens ?: null,
                "numeroNotaFiscal" => $this->numeroNotaFiscal,
                "serieNotaFiscal" => $this->serieNotaFiscal,
                "chaveNFe" => $this->chaveNotaFiscal,
            ],
            fn($v) => $v !== null,
        );
    }
}

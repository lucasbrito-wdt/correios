<?php

declare(strict_types=1);

namespace Correios\Services;

use Correios\DTOs\PrePostagem\PrePostagemRequestDTO;
use Correios\DTOs\PrePostagem\PrePostagemResponseDTO;

class PrePostagemService extends AbstractCorreiosService
{
    protected function basePath(): string
    {
        return "/prepostagem/v1";
    }

    /**
     * Cria uma pré-postagem (gera código de rastreio).
     */
    public function criar(
        PrePostagemRequestDTO $request,
    ): PrePostagemResponseDTO {
        $resposta = $this->post("/prepostagens", $request->toArray());

        return PrePostagemResponseDTO::fromArray($resposta);
    }

    /**
     * Consulta uma pré-postagem por ID.
     */
    public function buscar(string $id): PrePostagemResponseDTO
    {
        $resposta = $this->get("/prepostagens/{$id}");

        return PrePostagemResponseDTO::fromArray($resposta);
    }

    /**
     * Cancela uma pré-postagem ainda não despachada.
     */
    public function cancelar(string $id): array
    {
        return $this->delete("/prepostagens/{$id}");
    }

    /**
     * Solicita o rótulo (etiqueta) em PDF para impressão.
     */
    public function gerarRotulo(
        string $idPrePostagem,
        string $tipoRotulo = "P",
    ): array {
        return $this->post("/rotulo/assincrono/pdf", [
            "idsPrePostagem" => [$idPrePostagem],
            "tipoRotulo" => $tipoRotulo, // P = Padrão (10x15 cm)
            "formatoRotulo" => "ET", // ET = Etiqueta
            "imprimeRemetente" => "N",
        ]);
    }
}

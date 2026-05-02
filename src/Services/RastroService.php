<?php

declare(strict_types=1);

namespace Correios\Services;

use Correios\DTOs\Rastro\ObjetoRastreadoDTO;
use Correios\Exceptions\ValidacaoException;

class RastroService extends AbstractCorreiosService
{
    protected function basePath(): string
    {
        return "/srorastro/v1";
    }

    /**
     * Rastreia um único objeto.
     */
    public function rastrear(
        string $codigoObjeto,
        string $resultado = "T",
    ): ObjetoRastreadoDTO {
        $resultados = $this->rastrearVarios([$codigoObjeto], $resultado);

        return $resultados[0] ??
            throw ValidacaoException::valorInvalido(
                "codigoObjeto",
                "Nenhum dado retornado para o objeto {$codigoObjeto}.",
            );
    }

    /**
     * Rastreia múltiplos objetos (até 50).
     *
     * @param  array<int, string>  $codigos
     * @param  string  $resultado  'T' = todos os eventos, 'U' = apenas o último
     * @return array<int, ObjetoRastreadoDTO>
     */
    public function rastrearVarios(
        array $codigos,
        string $resultado = "T",
    ): array {
        if (count($codigos) > 50) {
            throw ValidacaoException::valorInvalido(
                "codigos",
                "Máximo de 50 códigos por consulta.",
            );
        }

        $resposta = $this->get("/objetos", [
            "codigosObjetos" => implode(",", $codigos),
            "resultado" => $resultado,
        ]);

        $objetos = $resposta["objetos"] ?? $resposta;

        return array_map(
            fn(array $obj) => ObjetoRastreadoDTO::fromArray($obj),
            $objetos,
        );
    }
}

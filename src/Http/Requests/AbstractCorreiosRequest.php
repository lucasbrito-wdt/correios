<?php

declare(strict_types=1);

namespace Correios\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

abstract class AbstractCorreiosRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Mensagens base reutilizáveis em todos os requests.
     */
    public function messages(): array
    {
        return array_merge(
            [
                "required" => "O campo :attribute é obrigatório.",
                "string" => "O campo :attribute deve ser texto.",
                "integer" => "O campo :attribute deve ser um número inteiro.",
                "numeric" => "O campo :attribute deve ser numérico.",
                "min" => "O campo :attribute deve ter no mínimo :min.",
                "max" => "O campo :attribute deve ter no máximo :max.",
                "in" => "O campo :attribute contém um valor inválido.",
                "regex" => "O campo :attribute está em formato inválido.",
            ],
            $this->customMessages(),
        );
    }

    /**
     * Cada subclasse pode acrescentar mensagens próprias.
     */
    protected function customMessages(): array
    {
        return [];
    }

    /**
     * Limpa CEPs antes da validação.
     */
    protected function prepareForValidation(): void
    {
        $merge = [];

        foreach (["cepOrigem", "cepDestino", "cep"] as $campo) {
            if ($this->has($campo)) {
                $merge[$campo] = preg_replace(
                    "/\D/",
                    "",
                    (string) $this->input($campo),
                );
            }
        }

        if ($merge) {
            $this->merge($merge);
        }
    }
}

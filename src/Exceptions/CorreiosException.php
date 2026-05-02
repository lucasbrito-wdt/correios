<?php

declare(strict_types=1);

namespace Correios\Exceptions;

use Exception;

class CorreiosException extends Exception
{
    protected ?array $contexto = null;

    public function comContexto(array $contexto): static
    {
        $this->contexto = $contexto;
        return $this;
    }

    public function getContexto(): ?array
    {
        return $this->contexto;
    }
}

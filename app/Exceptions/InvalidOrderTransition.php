<?php

namespace App\Exceptions;

use DomainException;

class InvalidOrderTransition extends DomainException
{
    public function __construct(public readonly string $from, public readonly string $to)
    {
        parent::__construct("Perubahan status pesanan tidak diizinkan: {$from} -> {$to}");
    }
}

<?php

namespace App\Core;

interface Middleware
{
    /**
     * Processa a requisição antes de chegar ao controller.
     * Se falhar na validação (ex: deslogado), deve redirecionar ou abortar.
     */
    public function handle(Request $request): void;
}
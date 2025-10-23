<?php

namespace App\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\PreventRequestsDuringMaintenance as Middleware;

class PreventRequestsDuringMaintenance extends Middleware
{
    // Puedes personalizar excepciones si deseas
    protected $except = [
        // '/api/health-check',
    ];
}

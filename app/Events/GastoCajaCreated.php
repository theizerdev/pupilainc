<?php

namespace App\Events;

use App\Models\GastoCaja;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class GastoCajaCreated
{
    use Dispatchable, SerializesModels;

    public $gastoCaja;

    public function __construct(GastoCaja $gastoCaja)
    {
        $this->gastoCaja = $gastoCaja;
    }
}

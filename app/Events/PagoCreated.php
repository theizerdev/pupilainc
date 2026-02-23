<?php

namespace App\Events;

use App\Models\Pago;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PagoCreated
{
    use Dispatchable, SerializesModels;

    public $pago;

    public function __construct(Pago $pago)
    {
        $this->pago = $pago;
    }
}

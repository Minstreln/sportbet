<?php

namespace App\Listeners;

use App\Events\AlertaEmailAposta;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class SendAlertaEmailAposta
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param  AlertaEmailAposta  $event
     * @return void
     */
    public function handle(AlertaEmailAposta $event)
    {
        //
    }
}

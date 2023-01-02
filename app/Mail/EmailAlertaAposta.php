<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;
use App\Models\Aposta;

class EmailAlertaAposta extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    private $aposta;

    public function __construct(Aposta $aposta)
    {
        $this->aposta = $aposta;
    }

    /**
     * Build the message
     * @return $this
     */
    public function build()
    {
         
        return $this->markdown('emails.alertAposta')
                        ->subject('Alerta Aposta!!!')
                        ->with([
                            'bilhete' => $this->aposta,
                            ]);

                     
    }
}

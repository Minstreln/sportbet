<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use App\Models\Aposta;
use App\Models\Configuracao;
use Illuminate\Support\Facades\Mail;
use App\Mail\EmailAlertaAposta;

class sendAlertaBet implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    protected $aposta;
    protected $email;

    public function __construct(Aposta $aposta, $email)
    {
        $this->aposta   = $aposta;
        $this->email    = $email;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    
    {   
       
       
        Mail::to($this->email)
            ->send(new EmailAlertaAposta($this->aposta));
    }
}

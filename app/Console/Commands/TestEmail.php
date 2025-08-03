<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class TestEmail extends Command
{
    protected $signature = 'email:test {email}';
    protected $description = 'Envía un correo electrónico de prueba';

    public function handle()
    {
        $email = $this->argument('email');
        
        $this->info("Enviando correo de prueba a {$email}...");
        
        try {
            Mail::raw('Prueba de correo desde Laravel', function($message) use ($email) {
                $message->to($email)->subject('Prueba de correo');
            });
            
            $this->info('¡Correo enviado con éxito!');
        } catch (\Exception $e) {
            $this->error('Error al enviar el correo: ' . $e->getMessage());
        }
        
        return 0;
    }
}

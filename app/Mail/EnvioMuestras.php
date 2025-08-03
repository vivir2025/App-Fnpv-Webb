<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class EnvioMuestras extends Mailable
{
    use Queueable, SerializesModels;

    public $envio;
    protected $pdfContent;
    protected $filename;

    /**
     * Create a new message instance.
     */
    public function __construct($envio, $pdfContent, $filename)
    {
        $this->envio = $envio;
        $this->pdfContent = $pdfContent;
        $this->filename = $filename;
    }

    /**
     * Build the message.
     */
    public function build()
    {
        $nombreSede = isset($this->envio['sede']['nombre']) 
            ? $this->envio['sede']['nombre'] 
            : (isset($this->envio['sede']['nombresede']) 
                ? $this->envio['sede']['nombresede'] 
                : (isset($this->envio['nombresede']) 
                    ? $this->envio['nombresede'] 
                    : 'N/A'));

        return $this->view('emails.envio_muestras')
                   ->subject('Envío de Muestras - ' . $nombreSede . ' - ' . $this->envio['codigo'])
                   ->attachData($this->pdfContent, $this->filename, [
                       'mime' => 'application/pdf',
                   ]);
    }
}

<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Http\Controllers\VirtualAssistantController;

class CallRoute extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'VirtualAssistantController:testLlm';


    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Ejecuta las pruebas de llm para el modelo seleccionado y guarda los resultados';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $controller = new VirtualAssistantController();
        $result = $controller->testLlm("complexLlmToSql"); 
        $this->info('Result: ' . $result);
    }
}

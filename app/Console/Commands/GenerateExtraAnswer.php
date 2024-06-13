<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Http\Controllers\VirtualAssistantController;

class GenerateExtraAnswer extends Command
{
    protected $signature = 'extra:generate';

    protected $description = 'Generate extra answer';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        // Llama a la función generateExtraAnswer() del controlador VirtualAssistantController
        $controller = new VirtualAssistantController();
        $controller->generateExtraAnswer();
        
        $this->info('Extra answer generated successfully.');
    }
}

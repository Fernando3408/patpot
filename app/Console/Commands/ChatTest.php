<?php

namespace App\Console\Commands;

use App\Services\ChatbotService;
use Illuminate\Console\Command;

class ChatTest extends Command
{
    protected $signature = 'chat:test {message?}';
    protected $description = 'Probar el chatbot del ERP sin Telegram';

    public function handle(): int
    {
        $chatbot = app(ChatbotService::class);

        $message = $this->argument('message');

        if (!$message) {
            $this->info('Chatbot PatPot - Escribe "salir" para terminar');
            $this->newLine();

            while (true) {
                $input = $this->ask('Tú');
                if (in_array(mb_strtolower($input), ['salir', 'exit', 'quit'])) {
                    $this->info('¡Hasta luego!');
                    break;
                }
                $response = $chatbot->handle($input);
                $this->newLine();
                $this->info('🤖 PatPot Bot: ' . $response);
                $this->newLine();
            }
        } else {
            $response = $chatbot->handle($message);
            $this->info('🤖 PatPot Bot: ' . $response);
        }

        return Command::SUCCESS;
    }
}

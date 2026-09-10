<?php

namespace App\Http\Controllers;

use App\Services\ChatbotService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class TelegramController extends Controller
{
    public function __construct(private ChatbotService $chatbot) {}

    public function webhook(Request $request)
    {
        $data = $request->all();

        if (!isset($data['message'])) {
            return response()->json(['ok' => true]);
        }

        $chatId = $data['message']['chat']['id'];
        $text = $data['message']['text'] ?? '';
        $userId = $data['message']['from']['id'] ?? null;

        if (empty($text)) {
            return response()->json(['ok' => true]);
        }

        $response = $this->chatbot->handle($text, $userId);

        $this->sendMessage($chatId, $response);

        return response()->json(['ok' => true]);
    }

    public function setWebhook()
    {
        $token = config('services.telegram.token', env('TELEGRAM_BOT_TOKEN'));
        $url = config('app.url') . '/api/telegram/webhook';

        $response = Http::get("https://api.telegram.org/bot{$token}/setWebhook", [
            'url' => $url,
        ]);

        return response()->json($response->json());
    }

    private function sendMessage(int $chatId, string $text): void
    {
        $token = config('services.telegram.token', env('TELEGRAM_BOT_TOKEN'));

        Http::post("https://api.telegram.org/bot{$token}/sendMessage", [
            'chat_id' => $chatId,
            'text' => $text,
            'parse_mode' => 'Markdown',
        ]);
    }
}

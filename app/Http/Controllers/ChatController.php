<?php

namespace App\Http\Controllers;

use App\Services\ChatbotService;
use Illuminate\Http\Request;

class ChatController extends Controller
{
    public function index()
    {
        return view('chat.index');
    }

    public function send(Request $request)
    {
        $request->validate(['message' => 'required|string|max:1000']);

        $chatbot = app(ChatbotService::class);
        $response = $chatbot->handle($request->input('message'), auth()->id());

        return response()->json(['reply' => $response]);
    }
}

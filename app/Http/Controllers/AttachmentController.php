<?php

namespace App\Http\Controllers;

use App\Models\Attachment;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AttachmentController extends Controller
{
    public function list(Request $request): JsonResponse
    {
        $modelClass = $request->input('model_class');
        $modelId = $request->input('model_id');

        if (! $modelClass || ! class_exists($modelClass)) {
            return response()->json(['error' => 'Modelo inválido'], 422);
        }

        $model = $modelClass::findOrFail($modelId);
        $attachments = $model->attachments->map(fn ($a) => [
            'id' => $a->id,
            'original_name' => $a->original_name,
            'formatted_size' => $a->formatted_size,
        ]);

        return response()->json(['attachments' => $attachments]);
    }

    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'files' => ['required', 'array', 'max:10'],
            'files.*' => ['file', 'max:10240'],
            'model_class' => ['required', 'string'],
            'model_id' => ['required', 'integer'],
        ]);

        $modelClass = $request->model_class;
        $allowed = [
            \App\Models\Product::class,
            \App\Models\Input::class,
            \App\Models\Order::class,
            \App\Models\Purchase::class,
            \App\Models\Production::class,
            \App\Models\Supplier::class,
            \App\Models\Customer::class,
            \App\Models\Store::class,
            \App\Models\Retail::class,
            \App\Models\Price::class,
            \App\Models\Task::class,
        ];

        if (! in_array($modelClass, $allowed)) {
            return response()->json(['ok' => false, 'error' => 'Tipo de entidad no permitido.'], 422);
        }

        $model = $modelClass::findOrFail($request->model_id);
        $saved = [];

        foreach ($request->file('files') as $file) {
            $originalName = $file->getClientOriginalName();
            $storedName = uniqid('att_', true).'.'.$file->getClientOriginalExtension();
            $path = $file->storeAs('attachments', $storedName, 'local');

            $attachment = $model->attachments()->create([
                'original_name' => $originalName,
                'stored_name' => $storedName,
                'path' => $path,
                'mime_type' => $file->getMimeType(),
                'size' => $file->getSize(),
                'user_id' => auth()->id(),
            ]);

            $saved[] = $attachment;
        }

        return response()->json(['ok' => true]);
    }

    public function download(Attachment $attachment): StreamedResponse
    {
        return Storage::disk('local')->download($attachment->path, $attachment->original_name);
    }

    public function destroy(Attachment $attachment): JsonResponse|\Illuminate\Http\RedirectResponse
    {
        Storage::disk('local')->delete($attachment->path);
        $attachment->delete();

        if (request()->expectsJson()) {
            return response()->json(['ok' => true]);
        }

        return back()->with('success', 'Archivo eliminado.');
    }
}

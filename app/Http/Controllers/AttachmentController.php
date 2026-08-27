<?php

namespace App\Http\Controllers;

use App\Models\Attachment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AttachmentController extends Controller
{
    public function list(Request $request)
    {
        $modelClass = $request->input('model_class');
        $modelId = $request->input('model_id');
        $model = $modelClass::findOrFail($modelId);
        $attachments = $model->attachments->map(fn($a) => [
            'id' => $a->id,
            'original_name' => $a->original_name,
            'formatted_size' => $a->formatted_size,
        ]);
        return response()->json(['attachments' => $attachments]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'files' => ['required', 'array', 'max:10'],
            'files.*' => ['file', 'max:10240'],
            'model_class' => ['required', 'string'],
            'model_id' => ['required', 'integer'],
        ]);

        $modelClass = $request->model_class;
        if (!class_exists($modelClass)) {
            return response()->json(['ok' => false, 'error' => 'Tipo de entidad inválido.'], 422);
        }

        $model = $modelClass::findOrFail($request->model_id);
        $saved = [];

        foreach ($request->file('files') as $file) {
            $originalName = $file->getClientOriginalName();
            $storedName = uniqid('att_', true) . '.' . $file->getClientOriginalExtension();
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

    public function destroy(Attachment $attachment)
    {
        Storage::disk('local')->delete($attachment->path);
        $attachment->delete();

        if (request()->expectsJson()) {
            return response()->json(['ok' => true]);
        }

        return back()->with('success', 'Archivo eliminado.');
    }
}

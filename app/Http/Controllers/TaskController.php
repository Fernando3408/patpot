<?php

namespace App\Http\Controllers;

use App\Models\Task;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TaskController extends Controller
{
    public function index(Request $request): View
    {
        $query = Task::query()
            ->orderByRaw("FIELD(status, 'pending', 'in_progress', 'completed')")
            ->orderBy('due_on');
        
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where('title', 'like', "%{$search}%")
                ->orWhere('owner', 'like', "%{$search}%");
        }
        
        $tasks = $query->get();

        return view('tasks.index', compact('tasks'));
    }

    public function create(): View
    {
        return view('tasks.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'owner' => 'nullable|string|max:255',
            'due_on' => 'required|date',
            'priority' => 'required|in:low,medium,high,urgent',
            'module' => 'nullable|string|max:255',
            'status' => 'required|in:pending,in_progress,completed',
            'notes' => 'nullable|string',
        ]);

        Task::create($data);

        return redirect('/tareas')->with('success', 'Tarea creada correctamente.');
    }

    public function edit(Task $task): View
    {
        return view('tasks.edit', compact('task'));
    }

    public function update(Request $request, Task $task)
    {
        try {
            if ($request->ajax()) {
                $rules = [
                    'title' => 'sometimes|required|string|max:255',
                    'owner' => 'sometimes|nullable|string|max:255',
                    'due_on' => 'sometimes|required|date',
                    'priority' => 'sometimes|required|in:low,medium,high,urgent',
                    'module' => 'sometimes|nullable|string|max:255',
                    'status' => 'sometimes|required|in:pending,in_progress,completed',
                    'notes' => 'sometimes|nullable|string',
                ];
            } else {
                $rules = [
                    'title' => 'required|string|max:255',
                    'owner' => 'nullable|string|max:255',
                    'due_on' => 'required|date',
                    'priority' => 'required|in:low,medium,high,urgent',
                    'module' => 'nullable|string|max:255',
                    'status' => 'required|in:pending,in_progress,completed',
                    'notes' => 'nullable|string',
                ];
            }
            $data = $request->validate($rules);

            if (($data['status'] ?? null) === 'completed' && ! $task->completed_on) {
                $data['completed_on'] = now()->toDateString();
            }

            $task->update($data);

            if ($request->ajax()) {
                return response()->json(['success' => true]);
            }
            return redirect('/tareas')->with('success', 'Tarea actualizada correctamente.');
        } catch (\Illuminate\Validation\ValidationException $e) {
            if ($request->ajax()) {
                return response()->json(['errors' => $e->errors()], 422);
            }
            throw $e;
        }
    }

    public function destroy(Task $task): RedirectResponse
    {
        $task->delete();

        return redirect('/tareas')->with('success', 'Tarea eliminada correctamente.');
    }

    public function complete(Task $task): RedirectResponse
    {
        $task->update(['status' => 'completed', 'completed_on' => now()->toDateString()]);

        return redirect('/tareas')->with('success', 'Tarea marcada como completada.');
    }
}

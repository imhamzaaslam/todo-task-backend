<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Todo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class TodoController extends Controller
{
    public function index()
    {
        return Todo::all();
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'status' => 'in:pending,in_progress,completed',
            'priority' => 'in:low,medium,high',
            'file' => 'nullable|file|mimes:pdf|max:2048',
        ]);

        $data = $request->only(['title', 'description', 'status', 'priority']);
        if ($request->hasFile('file')) {
            $filePath = $request->file('file')->store('pdfs', 'public');
            $data['file_path'] = $filePath;
        }

        return Todo::create($data);
    }

    public function show(Todo $todo)
    {
        return $todo;
    }

    public function update(Request $request, Todo $todo)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'status' => 'in:pending,in_progress,completed',
            'priority' => 'in:low,medium,high',
            'file' => 'nullable|file|mimes:pdf|max:2048',
        ]);

        $data = $request->only(['title', 'description', 'status', 'priority']);
        if ($request->hasFile('file')) {
            if ($todo->file_path) {
                Storage::disk('public')->delete($todo->file_path);
            }
            $filePath = $request->file('file')->store('pdfs', 'public');
            $data['file_path'] = $filePath;
        }

        $todo->update($data);
        return $todo;
    }

    public function destroy(Todo $todo)
    {
        if ($todo->file_path) {
            Storage::disk('public')->delete($todo->file_path);
        }
        $todo->delete();
        return response()->noContent();
    }
}
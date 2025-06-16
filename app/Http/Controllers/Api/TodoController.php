<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Todo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\UploadedFile;

class TodoController extends Controller
{
    public function index()
    {
        return Todo::orderBy('created_at', 'desc')->get();
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
            $data['file_path'] = $this->uploadPdfFile($request->file('file'));
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
            $data['file_path'] = $this->uploadPdfFile($request->file('file'));
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
    
    protected function uploadPdfFile(UploadedFile $file, string $folder = 'pdfs', string $disk = 'public')
    {
        $extension = $file->getClientOriginalExtension();
        $fileName = now()->format('Y-m-d_His') . '_' . uniqid() . '.' . $extension;

        return $file->storeAs($folder, $fileName, $disk);
    }
}
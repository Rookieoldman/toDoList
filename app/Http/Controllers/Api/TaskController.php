<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Task;

class TaskController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return Task::orderBy('created_at', 'desc')->get();
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'title' => 'required|string|max:255',
    
        ]);

        $task = Task::create([
            'title' => $validatedData['title'],
            
        ]);

        return response()->json($task, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Task $task)
    {
        
        $validatedData = $request->validate([
            'title' => 'sometimes|required|string|max:255', // Requerido SOLO SI se envía
            'is_complete' => 'sometimes|required|boolean', // Requerido SOLO SI se envía
        ]);

        if (empty($validatedData)) {
            // Puedes devolver la tarea sin cambios o un mensaje de error
             return response()->json($task); // Devolver sin cambios
            // O: return response()->json(['message' => 'No data provided for update.'], 400);
        }
        $task->update($validatedData);

        return response()->json($task, 200);
    }
    

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Task $task)
    {
        $task->delete();

        return response()->json(null, 204);
    }
    
}

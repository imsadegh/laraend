<?php

namespace App\Http\Controllers;

use App\Models\CourseModule;
use Illuminate\Http\Request;

class CourseModuleController extends Controller
{
    // List modules for a given course
    public function index($courseId)
    {
        $modules = CourseModule::where('course_id', $courseId)->get();
        return response()->json($modules);
    }

    // Create a new module for a course
    public function store(Request $request, $courseId)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'type' => 'required|in:video,article',
            'content_url' => 'nullable|string',
            'description' => 'nullable|string',
            'article_content' => 'nullable|string',
            'module_data' => 'nullable|json',
            'position' => 'nullable|integer',
            'visible' => 'boolean',
            'release_date' => 'nullable|date',
            'is_mandatory' => 'boolean',
            'estimated_duration_minutes' => 'nullable|integer',
            'view_count' => 'nullable|integer',
            'prerequisite_modules' => 'nullable|json',
            'rating' => 'nullable|numeric|min:0|max:5',
            // 'slug' => 'nullable|string|unique:course_modules,slug',
            'created_by' => 'required|exists:users,id',
        ]);

        $validated['course_id'] = $courseId;

        $module = CourseModule::create($validated);
        return response()->json([
            'message' => 'Module created successfully',
            'module' => $module,
        ], 201);
    }

    // Show a specific module
    public function show($id)
    {
        $module = CourseModule::find($id);
        if (!$module) {
            return response()->json(['message' => 'Module not found'], 404);
        }
        return response()->json($module);
    }

    // Update an existing module
    public function update(Request $request, $id)
    {
        $module = CourseModule::find($id);
        if (!$module) {
            return response()->json(['message' => 'Module not found'], 404);
        }

        $validated = $request->validate([
            'title' => 'sometimes|required|string|max:255',
            'type' => 'sometimes|required|in:video,article',
            'content_url' => 'nullable|string',
            'description' => 'nullable|string',
            'article_content' => 'nullable|string',
            'module_data' => 'nullable|json',
            'position' => 'nullable|integer',
            'visible' => 'sometimes|boolean',
            'release_date' => 'nullable|date',
            'is_mandatory' => 'sometimes|boolean',
            'estimated_duration_minutes' => 'nullable|integer',
            'view_count' => 'nullable|integer',
            'prerequisite_modules' => 'nullable|json',
            'rating' => 'nullable|numeric|min:0|max:5',
            // 'slug' => 'nullable|string|unique:course_modules,slug,' . $module->id,
        ]);

        $module->update($validated);
        return response()->json([
            'message' => 'Module updated successfully',
            'module' => $module,
        ]);
    }

    // Delete a module
    public function destroy($id)
    {
        $module = CourseModule::find($id);
        if (!$module) {
            return response()->json(['message' => 'Module not found'], 404);
        }
        $module->delete();
        return response()->json(['message' => 'Module deleted successfully']);
    }
}

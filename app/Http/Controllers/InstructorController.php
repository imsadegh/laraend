<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Http\JsonResponse;

class InstructorController extends Controller
{
    public function index(): JsonResponse
    {
        $instructors = User::where('role_id', 2) // Assuming role_id 2 is for instructors
            ->select('id', 'first_name', 'last_name')
            ->get();

        return response()->json($instructors, 200);
    }
}

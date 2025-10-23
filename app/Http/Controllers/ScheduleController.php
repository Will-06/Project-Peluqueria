<?php

namespace App\Http\Controllers;

use App\Models\Schedule;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ScheduleController extends Controller
{
    public function index(): JsonResponse
    {
        $schedule = Schedule::with('admin')
            ->where('is_active', true)
            ->first();

        return response()->json([
            'data' => $schedule ? [
                'id' => $schedule->id,
                'image_url' => $schedule->image_url,
                'is_active' => $schedule->is_active,
                'admin' => [
                    'id' => $schedule->admin->id,
                    'name' => $schedule->admin->name,
                ],
                'created_at' => $schedule->created_at
            ] : null
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $this->authorize('manage-schedules');

        $request->validate([
            'image_url' => 'required|url|max:500',
        ]);

        // Desactivar horarios anteriores
        Schedule::where('is_active', true)->update(['is_active' => false]);

        $schedule = Schedule::create([
            'admin_id' => $request->user()->id,
            'image_url' => $request->image_url,
            'is_active' => true,
        ]);

        return response()->json([
            'message' => 'Horario actualizado exitosamente',
            'data' => $schedule
        ], 201);
    }

    public function update(Request $request, Schedule $schedule): JsonResponse
    {
        $this->authorize('manage-schedules');

        $request->validate([
            'image_url' => 'sometimes|url|max:500',
            'is_active' => 'boolean',
        ]);

        if ($request->has('is_active') && $request->is_active) {
            // Desactivar otros horarios si se activa este
            Schedule::where('id', '!=', $schedule->id)
                ->where('is_active', true)
                ->update(['is_active' => false]);
        }

        $schedule->update($request->all());

        return response()->json([
            'message' => 'Horario actualizado',
            'data' => $schedule
        ]);
    }

    public function destroy(Schedule $schedule): JsonResponse
    {
        $this->authorize('manage-schedules');

        $schedule->delete();

        return response()->json([
            'message' => 'Horario eliminado'
        ]);
    }
}
<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreAppointmentRequest;
use App\Http\Resources\AppointmentResource;
use App\Models\Appointment;
use App\Models\AppointmentMessage;
use App\Models\Notification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\DB;

class AppointmentController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $query = Appointment::with(['user', 'haircut', 'messages.author']);

        if ($request->user()->isClient()) {
            $query->where('user_id', $request->user()->id);
        }

        // Filtros para admin
        if ($request->user()->isAdmin()) {
            if ($request->has('status')) {
                $query->where('status', $request->status);
            }
            if ($request->has('user_id')) {
                $query->where('user_id', $request->user_id);
            }
        }

        $appointments = $query->latest()->paginate(10);

        return AppointmentResource::collection($appointments);
    }

    public function store(StoreAppointmentRequest $request): JsonResponse
    {
        $appointment = DB::transaction(function () use ($request) {
            $appointment = Appointment::create([
                'user_id' => $request->user()->id,
                'haircut_id' => $request->haircut_id,
                'status' => 'pending',
            ]);

            // Crear primer mensaje
            AppointmentMessage::create([
                'appointment_id' => $appointment->id,
                'author_id' => $request->user()->id,
                'message' => $request->message,
            ]);

            // Crear notificación para administradores
            $admins = \App\Models\User::where('role', 'admin')->get();
            foreach ($admins as $admin) {
                Notification::create([
                    'user_id' => $admin->id,
                    'type' => 'new_appointment',
                    'data' => [
                        'appointment_id' => $appointment->id,
                        'user_name' => $request->user()->name,
                        'message' => 'Nueva solicitud de cita recibida',
                    ],
                ]);
            }

            return $appointment->load(['user', 'haircut', 'messages.author']);
        });

        return response()->json([
            'message' => 'Cita solicitada exitosamente',
            'data' => new AppointmentResource($appointment)
        ], 201);
    }

    public function show(Appointment $appointment): AppointmentResource
    {
        $this->authorize('view', $appointment);

        $appointment->load(['user', 'haircut', 'messages.author']);

        return new AppointmentResource($appointment);
    }

    public function updateStatus(Request $request, Appointment $appointment): JsonResponse
    {
        $this->authorize('update', $appointment);

        $request->validate([
            'status' => 'required|in:approved,rejected,cancelled',
        ]);

        $appointment->update(['status' => $request->status]);

        // Crear notificación para el usuario
        Notification::create([
            'user_id' => $appointment->user_id,
            'type' => 'appointment_status_updated',
            'data' => [
                'appointment_id' => $appointment->id,
                'status' => $request->status,
                'message' => "Tu cita ha sido {$request->status}",
            ],
        ]);

        return response()->json([
            'message' => 'Estado de la cita actualizado',
            'data' => new AppointmentResource($appointment->fresh())
        ]);
    }

    public function addMessage(Request $request, Appointment $appointment): JsonResponse
    {
        $this->authorize('addMessage', $appointment);

        $request->validate([
            'message' => 'required|string|max:1000',
        ]);

        $appointmentMessage = AppointmentMessage::create([
            'appointment_id' => $appointment->id,
            'author_id' => $request->user()->id,
            'message' => $request->message,
        ]);

        // Notificar al otro participante
        $recipientId = $request->user()->id === $appointment->user_id 
            ? $appointment->user_id 
            : ($appointment->user_id === $request->user()->id ? null : $appointment->user_id);

        if ($recipientId) {
            Notification::create([
                'user_id' => $recipientId,
                'type' => 'new_appointment_message',
                'data' => [
                    'appointment_id' => $appointment->id,
                    'author_name' => $request->user()->name,
                ],
            ]);
        }

        return response()->json([
            'message' => 'Mensaje enviado',
            'data' => $appointmentMessage->load('author')
        ], 201);
    }

    public function destroy(Appointment $appointment): JsonResponse
    {
        $this->authorize('delete', $appointment);

        $appointment->delete();

        return response()->json([
            'message' => 'Cita eliminada exitosamente'
        ]);
    }

    public function userAppointments(Request $request): AnonymousResourceCollection
    {
        $appointments = Appointment::with(['haircut', 'messages'])
            ->where('user_id', $request->user()->id)
            ->latest()
            ->paginate(10);

        return AppointmentResource::collection($appointments);
    }
}
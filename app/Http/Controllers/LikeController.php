<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreLikeRequest;
use App\Models\Haircut;
use App\Models\Like;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LikeController extends Controller
{
    public function toggle(StoreLikeRequest $request): JsonResponse
    {
        $user = $request->user();
        $haircut = Haircut::findOrFail($request->haircut_id);

        $existingLike = Like::where('user_id', $user->id)
            ->where('haircut_id', $haircut->id)
            ->first();

        if ($existingLike) {
            // Si ya existe, eliminar el like
            $existingLike->delete();
            $haircut->decrement('like_count');

            return response()->json([
                'message' => 'Like removido',
                'liked' => false,
                'like_count' => $haircut->fresh()->like_count
            ]);
        }

        // Crear nuevo like
        Like::create([
            'user_id' => $user->id,
            'haircut_id' => $haircut->id,
            'type' => $request->type,
        ]);

        $haircut->increment('like_count');

        return response()->json([
            'message' => 'Like agregado',
            'liked' => true,
            'like_type' => $request->type,
            'like_count' => $haircut->fresh()->like_count
        ]);
    }

    public function userLikes(Request $request): JsonResponse
    {
        $likes = Like::with('haircut.images')
            ->where('user_id', $request->user()->id)
            ->latest()
            ->get();

        return response()->json([
            'data' => $likes->map(function ($like) {
                return [
                    'id' => $like->id,
                    'type' => $like->type,
                    'haircut' => [
                        'id' => $like->haircut->id,
                        'name' => $like->haircut->name,
                        'featured_image_url' => $like->haircut->featured_image_url,
                    ],
                    'created_at' => $like->created_at
                ];
            })
        ]);
    }

    public function checkLike(Request $request, Haircut $haircut): JsonResponse
    {
        $like = Like::where('user_id', $request->user()->id)
            ->where('haircut_id', $haircut->id)
            ->first();

        return response()->json([
            'liked' => !is_null($like),
            'like_type' => $like?->type,
            'like_count' => $haircut->like_count
        ]);
    }
}
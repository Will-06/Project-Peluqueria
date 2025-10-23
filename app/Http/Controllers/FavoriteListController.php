<?php

namespace App\Http\Controllers;

use App\Models\FavoriteList;
use App\Models\FavoriteListItem;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class FavoriteListController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $lists = FavoriteList::with(['items.haircut.images'])
            ->where('user_id', $request->user()->id)
            ->latest()
            ->get();

        return response()->json([
            'data' => $lists->map(function ($list) {
                return [
                    'id' => $list->id,
                    'name' => $list->name,
                    'is_private' => $list->is_private,
                    'items_count' => $list->items->count(),
                    'items' => $list->items->map(function ($item) {
                        return [
                            'id' => $item->id,
                            'haircut' => [
                                'id' => $item->haircut->id,
                                'name' => $item->haircut->name,
                                'featured_image_url' => $item->haircut->featured_image_url,
                                'images' => $item->haircut->images
                            ],
                            'added_at' => $item->created_at
                        ];
                    }),
                    'created_at' => $list->created_at
                ];
            })
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'is_private' => 'boolean',
        ]);

        $list = FavoriteList::create([
            'user_id' => $request->user()->id,
            'name' => $request->name,
            'is_private' => $request->boolean('is_private', true),
        ]);

        return response()->json([
            'message' => 'Lista creada exitosamente',
            'data' => $list
        ], 201);
    }

    public function addHaircut(Request $request, FavoriteList $favoriteList): JsonResponse
    {
        $this->authorize('update', $favoriteList);

        $request->validate([
            'haircut_id' => 'required|exists:haircuts,id',
        ]);

        // Verificar si ya existe en la lista
        $existingItem = FavoriteListItem::where('favorite_list_id', $favoriteList->id)
            ->where('haircut_id', $request->haircut_id)
            ->first();

        if ($existingItem) {
            return response()->json([
                'message' => 'Este corte ya está en la lista'
            ], 422);
        }

        $item = DB::transaction(function () use ($favoriteList, $request) {
            $item = FavoriteListItem::create([
                'favorite_list_id' => $favoriteList->id,
                'haircut_id' => $request->haircut_id,
            ]);

            // Incrementar contador en el haircut
            $item->haircut->increment('favorite_count');

            return $item;
        });

        return response()->json([
            'message' => 'Corte agregado a la lista',
            'data' => $item->load('haircut.images')
        ], 201);
    }

    public function removeHaircut(FavoriteList $favoriteList, $haircutId): JsonResponse
    {
        $this->authorize('update', $favoriteList);

        $item = FavoriteListItem::where('favorite_list_id', $favoriteList->id)
            ->where('haircut_id', $haircutId)
            ->firstOrFail();

        DB::transaction(function () use ($item) {
            // Decrementar contador en el haircut
            $item->haircut->decrement('favorite_count');
            $item->delete();
        });

        return response()->json([
            'message' => 'Corte removido de la lista'
        ]);
    }

    public function update(Request $request, FavoriteList $favoriteList): JsonResponse
    {
        $this->authorize('update', $favoriteList);

        $request->validate([
            'name' => 'sometimes|string|max:255',
            'is_private' => 'boolean',
        ]);

        $favoriteList->update($request->all());

        return response()->json([
            'message' => 'Lista actualizada',
            'data' => $favoriteList->fresh()
        ]);
    }

    public function destroy(FavoriteList $favoriteList): JsonResponse
    {
        $this->authorize('delete', $favoriteList);

        DB::transaction(function () use ($favoriteList) {
            // Decrementar contadores de favoritos
            foreach ($favoriteList->items as $item) {
                $item->haircut->decrement('favorite_count');
            }
            
            $favoriteList->delete();
        });

        return response()->json([
            'message' => 'Lista eliminada'
        ]);
    }

    public function show(FavoriteList $favoriteList): JsonResponse
    {
        $this->authorize('view', $favoriteList);

        $favoriteList->load(['items.haircut.images', 'user']);

        return response()->json([
            'data' => [
                'id' => $favoriteList->id,
                'name' => $favoriteList->name,
                'is_private' => $favoriteList->is_private,
                'user' => [
                    'id' => $favoriteList->user->id,
                    'name' => $favoriteList->user->name,
                ],
                'items' => $favoriteList->items->map(function ($item) {
                    return [
                        'id' => $item->id,
                        'haircut' => [
                            'id' => $item->haircut->id,
                            'name' => $item->haircut->name,
                            'featured_image_url' => $item->haircut->featured_image_url,
                            'like_count' => $item->haircut->like_count,
                            'images' => $item->haircut->images
                        ],
                        'added_at' => $item->created_at
                    ];
                }),
                'created_at' => $favoriteList->created_at
            ]
        ]);
    }
}
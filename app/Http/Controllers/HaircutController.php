<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreHaircutRequest;
use App\Http\Requests\UpdateHaircutRequest;
use App\Http\Resources\HaircutResource;
use App\Models\Haircut;
use App\Models\HaircutImage;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\DB;

class HaircutController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $query = Haircut::with(['tags', 'images', 'admin'])
            ->withCount(['likes', 'reviews'])
            ->withAvg('reviews', 'rating');

        // Solo mostrar publicados si no es admin
        if (!$request->user() || !$request->user()->isAdmin()) {
            $query->published();
        }

        // Filtro por tags
        if ($request->has('tags')) {
            $tags = explode(',', $request->tags);
            $query->whereHas('tags', function ($q) use ($tags) {
                $q->whereIn('name', $tags);
            });
        }

        // Filtro por búsqueda
        if ($request->has('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        // Ordenamiento
        $sort = $request->get('sort', 'created_at');
        $order = $request->get('order', 'desc');
        $query->orderBy($sort, $order);

        $haircuts = $query->paginate(12);

        return HaircutResource::collection($haircuts);
    }

    public function store(StoreHaircutRequest $request): JsonResponse
    {
        $haircut = DB::transaction(function () use ($request) {
            $haircut = Haircut::create([
                'admin_id' => $request->user()->id,
                'name' => $request->name,
                'description' => $request->description,
                'featured_image_url' => $request->featured_image_url,
                'is_published' => $request->boolean('is_published', false),
            ]);

            // Sincronizar tags
            if ($request->has('tags')) {
                $haircut->tags()->sync($request->tags);
            }

            // Crear imágenes adicionales
            if ($request->has('images')) {
                foreach ($request->images as $imageData) {
                    HaircutImage::create([
                        'haircut_id' => $haircut->id,
                        'image_url' => $imageData['image_url'],
                        'order' => $imageData['order'] ?? 0,
                    ]);
                }
            }

            return $haircut->load(['tags', 'images', 'admin']);
        });

        return response()->json([
            'message' => 'Corte creado exitosamente',
            'data' => new HaircutResource($haircut)
        ], 201);
    }

    public function show(Request $request, Haircut $haircut): HaircutResource
    {
        // Verificar permisos de visualización
        if (!$haircut->is_published && (!$request->user() || !$request->user()->isAdmin())) {
            abort(404);
        }

        $haircut->load([
            'tags', 
            'images', 
            'admin',
            'reviews.user'
        ])->loadCount(['likes', 'reviews'])
          ->loadAvg('reviews', 'rating');

        return new HaircutResource($haircut);
    }

    public function update(UpdateHaircutRequest $request, Haircut $haircut): JsonResponse
    {
        $this->authorize('update', $haircut);

        $haircut->update($request->validated());

        if ($request->has('tags')) {
            $haircut->tags()->sync($request->tags);
        }

        return response()->json([
            'message' => 'Corte actualizado exitosamente',
            'data' => new HaircutResource($haircut->fresh(['tags', 'images', 'admin']))
        ]);
    }

    public function destroy(Haircut $haircut): JsonResponse
    {
        $this->authorize('delete', $haircut);

        $haircut->delete();

        return response()->json([
            'message' => 'Corte eliminado exitosamente'
        ]);
    }

    public function publish(Haircut $haircut): JsonResponse
    {
        $this->authorize('update', $haircut);

        $haircut->update(['is_published' => true]);

        return response()->json([
            'message' => 'Corte publicado exitosamente'
        ]);
    }

    public function unpublish(Haircut $haircut): JsonResponse
    {
        $this->authorize('update', $haircut);

        $haircut->update(['is_published' => false]);

        return response()->json([
            'message' => 'Corte despublicado exitosamente'
        ]);
    }

    public function addImage(Request $request, Haircut $haircut): JsonResponse
    {
        $this->authorize('update', $haircut);

        $request->validate([
            'image_url' => 'required|url|max:500',
            'order' => 'integer|min:0',
        ]);

        $image = $haircut->images()->create([
            'image_url' => $request->image_url,
            'order' => $request->order ?? 0,
        ]);

        return response()->json([
            'message' => 'Imagen agregada exitosamente',
            'data' => $image
        ], 201);
    }

    public function removeImage(Haircut $haircut, HaircutImage $image): JsonResponse
    {
        $this->authorize('update', $haircut);

        if ($image->haircut_id !== $haircut->id) {
            abort(404, 'Imagen no encontrada para este corte');
        }

        $image->delete();

        return response()->json([
            'message' => 'Imagen eliminada exitosamente'
        ]);
    }

    public function popular(): AnonymousResourceCollection
    {
        $haircuts = Haircut::with(['tags', 'images'])
            ->published()
            ->withCount(['likes', 'reviews'])
            ->orderBy('like_count', 'desc')
            ->limit(10)
            ->get();

        return HaircutResource::collection($haircuts);
    }

    public function featured(): AnonymousResourceCollection
    {
        $haircuts = Haircut::with(['tags', 'images'])
            ->published()
            ->whereHas('tags', function ($query) {
                $query->where('name', 'featured');
            })
            ->withCount(['likes', 'reviews'])
            ->inRandomOrder()
            ->limit(6)
            ->get();

        return HaircutResource::collection($haircuts);
    }
}
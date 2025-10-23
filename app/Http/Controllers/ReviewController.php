<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreReviewRequest;
use App\Http\Resources\ReviewResource;
use App\Models\Review;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class ReviewController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $query = Review::with(['user', 'haircut']);

        if ($request->has('haircut_id')) {
            $query->where('haircut_id', $request->haircut_id);
        }

        if ($request->has('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        $reviews = $query->latest()->paginate(10);

        return ReviewResource::collection($reviews);
    }

    public function store(StoreReviewRequest $request): JsonResponse
    {
        // Verificar que no existe ya una reseña para este corte
        $existingReview = Review::where('user_id', $request->user()->id)
            ->where('haircut_id', $request->haircut_id)
            ->first();

        if ($existingReview) {
            return response()->json([
                'message' => 'Ya has realizado una reseña para este corte'
            ], 422);
        }

        $review = Review::create([
            'user_id' => $request->user()->id,
            'haircut_id' => $request->haircut_id,
            'rating' => $request->rating,
            'comment' => $request->comment,
        ]);

        return response()->json([
            'message' => 'Reseña creada exitosamente',
            'data' => new ReviewResource($review->load('user'))
        ], 201);
    }

    public function show(Review $review): ReviewResource
    {
        $review->load(['user', 'haircut']);

        return new ReviewResource($review);
    }

    public function update(StoreReviewRequest $request, Review $review): JsonResponse
    {
        $this->authorize('update', $review);

        $review->update($request->validated());

        return response()->json([
            'message' => 'Reseña actualizada exitosamente',
            'data' => new ReviewResource($review->fresh(['user', 'haircut']))
        ]);
    }

    public function destroy(Review $review): JsonResponse
    {
        $this->authorize('delete', $review);

        $review->delete();

        return response()->json([
            'message' => 'Reseña eliminada exitosamente'
        ]);
    }

    public function userReviews(Request $request): AnonymousResourceCollection
    {
        $reviews = Review::with('haircut')
            ->where('user_id', $request->user()->id)
            ->latest()
            ->paginate(10);

        return ReviewResource::collection($reviews);
    }
}
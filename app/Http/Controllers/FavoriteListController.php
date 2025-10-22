<?php

namespace App\Http\Controllers;

use App\Models\FavoriteList;
use App\Http\Requests\StoreFavoriteListRequest;
use App\Http\Requests\UpdateFavoriteListRequest;

class FavoriteListController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreFavoriteListRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(FavoriteList $favoriteList)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(FavoriteList $favoriteList)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateFavoriteListRequest $request, FavoriteList $favoriteList)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(FavoriteList $favoriteList)
    {
        //
    }
}

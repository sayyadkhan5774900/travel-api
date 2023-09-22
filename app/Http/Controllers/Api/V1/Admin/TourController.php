<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Helpers;
use App\Http\Controllers\Controller;
use App\Http\Requests\TourListRequest;
use App\Http\Requests\TourRequest;
use App\Http\Resources\TourResource;
use App\Models\Tour;
use App\Models\Travel;

class TourController extends Controller
{
    public function index(Travel $travel, TourListRequest $request)
    {
        $tours = $travel->tours()
            ->when($request->priceFrom, function ($query) use ($request) {
                return $query->where('price', '>=', $request->priceFrom * 100);
            })
            ->when($request->priceTo, function ($query) use ($request) {
                return $query->where('price', '<=', $request->priceTo * 100);
            })
            ->when($request->dateFrom, function ($query) use ($request) {
                return $query->where('starting_date', '>=', Helpers::removeTimeFromDate($request->dateFrom));
            })
            ->when($request->dateTo, function ($query) use ($request) {
                return $query->where('starting_date', '<=', Helpers::removeTimeFromDate($request->dateTo));
            })
            ->when($request->sortBy && $request->orderBy, function ($query) use ($request) {
                return $query->orderBy($request->sortBy, $request->orderBy);
            })
            ->orderBy('starting_date')
            ->paginate();

        return TourResource::collection($tours);
    }

    public function store(Travel $travel, TourRequest $request)
    {
        $tour = $travel->tours()->create($request->validated());

        return new TourResource($tour);
    }

    public function update(Tour $tour, TourRequest $request)
    {
        $tour->update($request->validated());

        return new TourResource($tour);
    }

    public function destroy(string $id)
    {
        //
    }
}

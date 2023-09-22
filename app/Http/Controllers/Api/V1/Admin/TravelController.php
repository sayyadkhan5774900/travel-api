<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\TravelRequest;
use App\Http\Resources\TravelResource;
use App\Models\Travel;
use Illuminate\Http\Request;

class TravelController extends Controller
{

    public function index()
    {
        $travels = Travel::where('is_public', 1)->paginate();
        return TravelResource::collection($travels);
    }

    public function store(TravelRequest $request)
    {
        $travel = Travel::create($request->validated());
        return new TravelResource($travel);
    }

    public function update(Travel $travel, TravelRequest $request)
    {
        $travel->update($request->validated());

        return new TravelResource($travel);
    }

    public function destroy(string $id)
    {
        //
    }
}

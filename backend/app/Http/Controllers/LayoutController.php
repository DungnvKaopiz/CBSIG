<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreLayoutRequest;
use App\Http\Requests\UpdateLayoutRequest;
use App\Models\Layout;
use App\Services\LayoutService;
use Illuminate\Http\Request;

class LayoutController extends Controller
{
    protected $layoutService;

    public function __construct(LayoutService $layoutService)
    {
        $this->layoutService = $layoutService;
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $layouts = $this->layoutService->getAll();
        
        return response()->json([
            'message' => 'Layouts fetched successfully',
            'data' => $layouts,
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show(Layout $layout)
    {
        $layout = $this->layoutService->find($layout->id);
        
        return response()->json([
            'message' => 'Layout fetched successfully',
            'data' => $layout,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreLayoutRequest $request)
    {
        $layout = $this->layoutService->create($request->validated());

        return response()->json([
            'message' => 'Layout created successfully',
            'data' => $layout,
        ], 201);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateLayoutRequest $request, Layout $layout)
    {
        $layout = $this->layoutService->update($layout, $request->validated());

        return response()->json([
            'message' => 'Layout updated successfully',
            'data' => $layout,
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Layout $layout)
    {
        $this->layoutService->delete($layout);

        return response()->json([
            'message' => 'Layout deleted successfully',
        ]);
    }
}

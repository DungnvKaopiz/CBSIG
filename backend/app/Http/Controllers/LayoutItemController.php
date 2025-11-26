<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreLayoutItemRequest;
use App\Http\Requests\UpdateLayoutItemRequest;
use App\Models\LayoutItem;
use App\Services\LayoutItemService;
use Illuminate\Http\Request;

class LayoutItemController extends Controller
{
    protected $layoutItemService;

    public function __construct(LayoutItemService $layoutItemService)
    {
        $this->layoutItemService = $layoutItemService;
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $layoutItems = $this->layoutItemService->getAll();
        
        return response()->json([
            'message' => 'Layout items fetched successfully',
            'data' => $layoutItems,
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show(LayoutItem $layoutItem)
    {
        $layoutItem = $this->layoutItemService->find($layoutItem->id);
        
        return response()->json([
            'message' => 'Layout item fetched successfully',
            'data' => $layoutItem,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreLayoutItemRequest $request)
    {
        $layoutItem = $this->layoutItemService->create($request->validated());

        return response()->json([
            'message' => 'Layout item created successfully',
            'data' => $layoutItem,
        ], 201);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateLayoutItemRequest $request, LayoutItem $layoutItem)
    {
        $layoutItem = $this->layoutItemService->update($layoutItem, $request->validated());

        return response()->json([
            'message' => 'Layout item updated successfully',
            'data' => $layoutItem,
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(int $id)
    {
        $layoutItem = LayoutItem::find($id);
        $this->layoutItemService->delete($layoutItem);

        return response()->json([
            'message' => 'Layout item deleted successfully',
        ]);
    }

    /**
     * Get layout items by layout ID
     */
    public function getByLayout(int $layoutId)
    {
        $layoutItems = $this->layoutItemService->getByLayout($layoutId);
        
        return response()->json([
            'message' => 'Layout items fetched successfully',
            'data' => $layoutItems,
        ]);
    }
}

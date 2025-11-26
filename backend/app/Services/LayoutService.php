<?php

namespace App\Services;

use App\Models\Layout;
use Illuminate\Support\Facades\Auth;

class LayoutService
{
    protected $layoutItemService;

    public function __construct(LayoutItemService $layoutItemService)
    {
        $this->layoutItemService = $layoutItemService;
    }

    /**
     * Get all layouts
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getAll()
    {
        return Layout::with('layoutItems')
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Get layout by ID
     *
     * @param int $id
     * @return Layout|null
     */
    public function find(int $id)
    {
        return Layout::with('layoutItems')->find($id);
    }

    /**
     * Create a new layout
     *
     * @param array $data
     * @return Layout
     */
    public function create(array $data): Layout
    {
        $frames = $data['frames'] ?? [];
        unset($data['frames']);

        $layoutData = [
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
            'canvas_width' => $data['canvas_width'] ?? 1280,
            'canvas_height' => $data['canvas_height'] ?? 720,
            'created_by_user_id' => Auth::id(),
        ];

        $layout = Layout::create($layoutData);

        if (!empty($frames)) {
            $this->layoutItemService->syncForLayout($layout, $frames);
        }

        return $layout->fresh(['layoutItems']);
    }

    /**
     * Update a layout
     *
     * @param Layout $layout
     * @param array $data
     * @return Layout
     */
    public function update(Layout $layout, array $data): Layout
    {
        $frames = $data['frames'] ?? [];
        unset($data['frames']);

        $layout->update($data);
        $this->layoutItemService->syncForLayout($layout, $frames);

        return $layout->fresh(['layoutItems']);
    }

    /**
     * Delete a layout
     *
     * @param Layout $layout
     * @return bool
     */
    public function delete(Layout $layout): bool
    {
        return $layout->delete();
    }
}


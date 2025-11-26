<?php

namespace App\Services;

use App\Models\Layout;
use App\Models\LayoutItem;
use Illuminate\Support\Collection;

class LayoutItemService
{
    /**
     * Get all layout items
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getAll()
    {
        return LayoutItem::with('layout')
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Get layout item by ID
     *
     * @param int $id
     * @return LayoutItem|null
     */
    public function find(int $id)
    {
        return LayoutItem::with('layout')->find($id);
    }

    /**
     * Get layout items by layout ID
     *
     * @param int $layoutId
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getByLayout(int $layoutId)
    {
        return LayoutItem::where('layout_id', $layoutId)
            ->orderBy('created_at', 'asc')
            ->get();
    }

    /**
     * Create a new layout item
     *
     * @param array $data
     * @return LayoutItem
     */
    public function create(array $data): LayoutItem
    {
        $itemData = [
            'layout_id' => $data['layout_id'],
            'name' => $data['name'],
            'content_id' => $data['content_id'] ?? null,
            'frame_metadata' => $data['frame_metadata'] ?? null,
        ];

        return LayoutItem::create($itemData);
    }

    /**
     * Create multiple layout items
     *
     * @param int $layoutId
     * @param array $items
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function createMany(int $layoutId, array $items)
    {
        $layoutItems = [];
        
        foreach ($items as $item) {
            $layoutItems[] = LayoutItem::create([
                'layout_id' => $layoutId,
                'name' => $item['name'],
                'content_id' => $item['content_id'] ?? null,
                'frame_metadata' => $item['frame_metadata'] ?? null,
            ]);
        }

        return collect($layoutItems);
    }

    /**
     * Update a layout item
     *
     * @param LayoutItem $layoutItem
     * @param array $data
     * @return LayoutItem
     */
    public function update(LayoutItem $layoutItem, array $data): LayoutItem
    {
        $layoutItem->update($data);
        return $layoutItem->fresh(['layout']);
    }

    /**
     * Delete a layout item
     *
     * @param LayoutItem $layoutItem
     * @return bool
     */
    public function delete(LayoutItem $layoutItem): bool
    {
        return $layoutItem->delete();
    }

    /**
     * Delete all layout items for a layout
     *
     * @param int $layoutId
     * @return int Number of deleted items
     */
    public function deleteByLayout(int $layoutId): int
    {
        return LayoutItem::where('layout_id', $layoutId)->delete();
    }

    /**
     * Sync layout items for a layout (replace existing frames)
     *
     * @param Layout $layout
     * @param array $frames
     * @return Collection
     */
    public function syncForLayout(Layout $layout, array $frames): Collection
    {
        $inputIds = collect($frames)->pluck('id')->filter()->toArray();

        $layout->layoutItems()->whereNotIn('id', $inputIds)->delete();

        foreach ($frames as $index => $frame) {
            $data = [
                'layout_id' => $layout->id,
                'name' => $frame['name'] ?? 'Frame ' . ($index + 1),
                'content_id' => $frame['content_id'] ?? $frame['contentId'] ?? null,
                'frame_metadata' => $frame['frame_metadata'] ?? [
                    'x' => $frame['x'] ?? 0,
                    'y' => $frame['y'] ?? 0,
                    'width' => $frame['width'] ?? 200,
                    'height' => $frame['height'] ?? 200,
                    'z_index' => $frame['z_index'] ?? $frame['zIndex'] ?? $index + 1,
                    'image_fit' => $frame['image_fit'] ?? $frame['imageFit'] ?? 'contain',
                    'order_index' => $frame['order_index'] ?? $frame['orderIndex'] ?? $index,
                ],
            ];

            $layout->layoutItems()->updateOrCreate(
                ['id' => $frame['id'] ?? null],
                $data                    
            );
        }

        return $layout->layoutItems()->get();
    }
}


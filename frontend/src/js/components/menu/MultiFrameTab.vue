<template>
  <div class="multiframe-tab">
    <!-- Left Sidebar: Device Info & Layout List -->
    <div class="sidebar-left">
      <div class="sidebar-content">
        <!-- Device Information -->
        <div class="section">
          <h3 class="section-title">Device Information</h3>
          <div class="info-panel">
            <div class="info-row">
              <span class="info-label">Device Name</span>
              <span class="info-value">{{ deviceName }}</span>
            </div>
            <div class="info-row">
              <span class="info-label">Device Size</span>
              <span class="info-value">{{ canvasSize.width }}x{{ canvasSize.height }}</span>
            </div>
            <div class="info-row">
              <span class="info-label">Max Frames</span>
              <span class="info-value">{{ selectedLayout?.frames.length || 0 }}</span>
            </div>
          </div>
        </div>

        <!-- Layout List -->
        <div class="section">
          <h3 class="section-title">Layout List</h3>
          <p v-if="layoutsError" class="error-message status-message">{{ layoutsError }}</p>
          <p v-else-if="isLoadingLayouts" class="info-message status-message">Loading layouts...</p>
          <p v-else-if="!layouts.length" class="empty-message status-message">
            No layouts yet. Click Add to create one.
          </p>
          <ul v-if="layouts.length" class="list">
            <li v-for="layout in layouts" :key="layout.id">
              <button
                :class="['list-item', { active: selectedLayout?.id === layout.id }]"
                @click="selectLayout(layout.id)"
              >
                {{ layout.name }}
              </button>
            </li>
          </ul>
          <div class="list-actions">
            <button class="btn-action" @click="addLayout" :disabled="isLoadingLayouts">
              <Plus :size="16" />
              Add
            </button>
            <button
              class="btn-action"
              @click="deleteLayout"
              :disabled="!selectedLayout || layouts.length <= 1 || isLoadingLayouts"
            >
              <Trash2 :size="16" />
              Delete
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Middle Sidebar: Layout Properties, Frame Operations, Frame List -->
    <div class="sidebar-middle">
      <div class="sidebar-content">
        <template v-if="selectedLayout">
          <!-- Layout Properties -->
          <div class="section">
            <h3 class="section-title">Layout Properties</h3>
            <div class="form-panel">
              <label class="form-label">Name</label>
              <input
                type="text"
                v-model="selectedLayout.name"
                class="form-input"
              />
            </div>
          </div>

          <!-- Frame Operations -->
          <div class="section">
            <h3 class="section-title">Frame Operations</h3>
            <div v-if="selectedFrame" class="form-panel">
              <div class="form-group">
                <label class="form-label">Frame ID</label>
                <input
                  type="text"
                  v-model="selectedFrame.name"
                  class="form-input"
                />
              </div>
              <div class="form-grid">
                <div class="form-group">
                  <label class="form-label">Position X</label>
                  <input
                    type="number"
                    :value="Math.round(selectedFrame.x)"
                    @input="updateFrameProp('x', $event)"
                    class="form-input"
                  />
                </div>
                <div class="form-group">
                  <label class="form-label">Position Y</label>
                  <input
                    type="number"
                    :value="Math.round(selectedFrame.y)"
                    @input="updateFrameProp('y', $event)"
                    class="form-input"
                  />
                </div>
              </div>
              <div class="form-grid">
                <div class="form-group">
                  <label class="form-label">Size W</label>
                  <input
                    type="number"
                    :value="Math.round(selectedFrame.width)"
                    @input="updateFrameProp('width', $event)"
                    class="form-input"
                  />
                </div>
                <div class="form-group">
                  <label class="form-label">Size H</label>
                  <input
                    type="number"
                    :value="Math.round(selectedFrame.height)"
                    @input="updateFrameProp('height', $event)"
                    class="form-input"
                  />
                </div>
              </div>
            </div>
            <p v-else class="empty-message">Select a frame to edit.</p>
          </div>

          <!-- Frame List -->
          <div class="section">
            <h3 class="section-title">Frame List</h3>
            <div class="list-panel">
              <ul class="list">
                <li v-for="frame in selectedLayout.frames" :key="frame.id">
                  <button
                    :class="['list-item', { active: selectedFrame?.id === frame.id }]"
                    @click="selectFrame(frame.id)"
                  >
                    {{ frame.name }}
                  </button>
                </li>
              </ul>
              <div class="list-actions">
                <button class="btn-action" @click="addFrame">
                  <Plus :size="16" />
                  Add
                </button>
                <button
                  class="btn-action"
                  @click="selectedFrame && deleteFrame(selectedFrame.id)"
                  :disabled="!selectedFrame"
                >
                  <Trash2 :size="16" />
                  Delete
                </button>
              </div>
            </div>
          </div>

          <!-- Display & Arrangement -->
          <div class="section">
            <h3 class="section-title">Display & Arrangement</h3>
            <div class="form-panel">
              <div class="form-group">
                <label class="form-label">Image Fit</label>
                <select
                  :value="selectedFrame?.imageFit || 'contain'"
                  @change="updateFrameImageFit"
                  :disabled="!selectedFrame || !selectedFrame.mediaUrl"
                  class="form-input"
                >
                  <option value="contain">Contain</option>
                  <option value="cover">Cover</option>
                  <option value="fill">Fill</option>
                  <option value="none">None</option>
                  <option value="scale-down">Scale Down</option>
                </select>
              </div>
              <div class="form-group">
                <label class="form-label">Layer Order</label>
                <div class="button-group">
                  <button
                    class="btn-action"
                    @click="bringForward"
                    :disabled="!selectedFrame"
                  >
                    <ArrowUp :size="16" />
                    Forward
                  </button>
                  <button
                    class="btn-action"
                    @click="sendBackward"
                    :disabled="!selectedFrame"
                  >
                    <ArrowDown :size="16" />
                    Backward
                  </button>
                </div>
              </div>
            </div>
          </div>
        </template>
        <div v-else class="empty-state">
          <p>Select a layout to see details.</p>
        </div>
      </div>
    </div>

    <!-- Canvas Area -->
    <div class="canvas-area">
      <div
        ref="canvasRef"
        class="canvas"
        :style="{ width: `${canvasSize.width}px`, height: `${canvasSize.height}px` }"
        @click="handleCanvasClick"
      >
        <div class="grid-overlay">
          <div class="grid-line grid-line-h grid-line-1"></div>
          <div class="grid-line grid-line-h grid-line-2"></div>
          <div class="grid-line grid-line-h grid-line-3"></div>
          <div class="grid-line grid-line-v grid-line-1"></div>
          <div class="grid-line grid-line-v grid-line-2"></div>
          <div class="grid-line grid-line-v grid-line-3"></div>
        </div>
        <FrameComponent
          v-for="frame in selectedLayout?.frames || []"
          :key="frame.id"
          :frame="frame"
          :is-selected="selectedFrame?.id === frame.id"
          :canvas-ref="canvasRef"
          @select="selectFrame"
          @update="updateFrame"
          @delete="deleteFrame"
        />
      </div>
      <!-- Bottom Actions -->
      <div class="bottom-actions">
        <div v-if="error" class="error-message">{{ error }}</div>
        <div v-if="successMessage" class="success-message">{{ successMessage }}</div>
        <button class="btn-primary" @click="save" :disabled="isLoading">
          {{ isLoading ? 'Saving...' : 'Save' }}
        </button>
        <button class="btn-secondary" @click="close" :disabled="isLoading">Close</button>
      </div>
    </div>
  </div>
</template>

<script>
import { ref, computed, onMounted } from 'vue';
import { Plus, Trash2, ArrowUp, ArrowDown } from 'lucide-vue-next';
import FrameComponent from '../multiframe/MultiFrameFrame.vue';
import { layoutService } from '@/api/layoutService';

export default {
  name: 'MultiFrameTab',
  components: {
    FrameComponent,
    Plus,
    Trash2,
    ArrowUp,
    ArrowDown,
  },
  setup() {
    const canvasRef = ref(null);
    const deviceName = ref('Dev Test');
    const canvasSize = ref({ width: 1280, height: 720 });
    const isLoading = ref(false);
    const error = ref(null);
    const successMessage = ref(null);
    const isLoadingLayouts = ref(false);
    const layoutsError = ref(null);

    // Layouts and frames state
    const layouts = ref([]);

    const selectedLayoutId = ref(null);
    const selectedFrameId = ref(null);

    const selectedLayout = computed(() => {
      return layouts.value.find(l => l.id === selectedLayoutId.value) || null;
    });

    const selectedFrame = computed(() => {
      if (!selectedLayout.value || !selectedFrameId.value) return null;
      return selectedLayout.value.frames.find(f => f.id === selectedFrameId.value) || null;
    });

    // Layout operations
    const selectLayout = (id) => {
      selectedLayoutId.value = id;
      selectedFrameId.value = null;
      const layout = layouts.value.find(l => l.id === id);
      if (layout) {
        canvasSize.value = {
          width: layout.canvas_width ?? layout.canvasWidth ?? 1280,
          height: layout.canvas_height ?? layout.canvasHeight ?? 720,
        };
      }
    };

    const addLayout = () => {
      const newLayout = {
        id: `temp-${Date.now()}`,
        name: `Layout ${layouts.value.length + 1}`,
        canvas_width: canvasSize.value.width,
        canvas_height: canvasSize.value.height,
        frames: [],
      };
      layouts.value.push(newLayout);
      selectLayout(newLayout.id);
    };

    const deleteLayout = () => {
      if (!selectedLayout.value || layouts.value.length <= 1) return;
      const index = layouts.value.findIndex(l => l.id === selectedLayout.value.id);
      layouts.value.splice(index, 1);
      if (layouts.value.length > 0) {
        selectLayout(layouts.value[0].id);
      } else {
        selectedLayoutId.value = null;
      }
    };

    // Frame operations
    const selectFrame = (id) => {
      selectedFrameId.value = id;
    };

    const addFrame = () => {
      if (!selectedLayout.value) return;
      const newFrame = {
        id: `f${Date.now()}`,
        name: `Frame ${selectedLayout.value.frames.length + 1}`,
        x: 100,
        y: 100,
        width: 200,
        height: 200,
        zIndex: selectedLayout.value.frames.length + 1,
        imageFit: 'contain',
        mediaUrl: null,
        mediaType: null,
      };
      selectedLayout.value.frames.push(newFrame);
      selectFrame(newFrame.id);
    };

    const deleteFrame = (id) => {
      if (!selectedLayout.value) return;
      const index = selectedLayout.value.frames.findIndex(f => f.id === id);
      if (index !== -1) {
        selectedLayout.value.frames.splice(index, 1);
        if (selectedFrameId.value === id) {
          selectedFrameId.value = null;
        }
      }
    };

    const updateFrame = (id, updates) => {
      if (!selectedLayout.value) return;
      const frame = selectedLayout.value.frames.find(f => f.id === id);
      if (frame) {
        Object.assign(frame, updates);
      }
    };

    const updateFrameProp = (prop, event) => {
      if (!selectedFrame.value) return;
      const value = parseInt(event.target.value) || 0;
      updateFrame(selectedFrame.value.id, { [prop]: value });
    };

    const updateFrameImageFit = (event) => {
      if (!selectedFrame.value) return;
      updateFrame(selectedFrame.value.id, { imageFit: event.target.value });
    };

    const bringForward = () => {
      if (!selectedLayout.value || !selectedFrame.value) return;
      const frames = selectedLayout.value.frames;
      const currentIndex = frames.findIndex(f => f.id === selectedFrame.value.id);
      if (currentIndex < frames.length - 1) {
        const nextFrame = frames[currentIndex + 1];
        [frames[currentIndex].zIndex, nextFrame.zIndex] = [nextFrame.zIndex, frames[currentIndex].zIndex];
        frames.sort((a, b) => a.zIndex - b.zIndex);
      }
    };

    const sendBackward = () => {
      if (!selectedLayout.value || !selectedFrame.value) return;
      const frames = selectedLayout.value.frames;
      const currentIndex = frames.findIndex(f => f.id === selectedFrame.value.id);
      if (currentIndex > 0) {
        const prevFrame = frames[currentIndex - 1];
        [frames[currentIndex].zIndex, prevFrame.zIndex] = [prevFrame.zIndex, frames[currentIndex].zIndex];
        frames.sort((a, b) => a.zIndex - b.zIndex);
      }
    };

    const handleCanvasClick = (e) => {
      if (e.target === canvasRef.value) {
        selectedFrameId.value = null;
      }
    };

    const mapFrameFromApi = (frame, index) => {
      const metadata = frame.frame_metadata || frame.frameMetadata || {};
      return {
        id: frame.id?.toString() || `frame-${Date.now()}-${index}`,
        name: frame.name || `Frame ${index + 1}`,
        x: metadata.x ?? 0,
        y: metadata.y ?? 0,
        width: metadata.width ?? 200,
        height: metadata.height ?? 200,
        zIndex: metadata.z_index ?? metadata.zIndex ?? index + 1,
        imageFit: metadata.image_fit ?? metadata.imageFit ?? 'contain',
        mediaUrl: frame.media_url || frame.mediaUrl || null,
        mediaType: frame.media_type || frame.mediaType || null,
      };
    };

    const mapLayoutFromApi = (layout) => {
      const items = layout.layout_items || layout.layoutItems || [];
      return {
        id: layout.id?.toString() || `temp-${Date.now()}`,
        name: layout.name || 'Untitled Layout',
        description: layout.description || null,
        canvas_width: layout.canvas_width ?? layout.canvasWidth ?? 1280,
        canvas_height: layout.canvas_height ?? layout.canvasHeight ?? 720,
        frames: items.map((frame, index) => mapFrameFromApi(frame, index)),
      };
    };

    const fetchLayouts = async (preferredId = null) => {
      isLoadingLayouts.value = true;
      layoutsError.value = null;
      try {
        const response = await layoutService.getAll();
        const layoutData = response.data?.data || response.data || [];
        const mappedLayouts = layoutData.map(mapLayoutFromApi);
        layouts.value = mappedLayouts;

        if (mappedLayouts.length > 0) {
          const target =
            preferredId && mappedLayouts.find(l => l.id === preferredId)
              ? preferredId
              : mappedLayouts[0].id;
          selectLayout(target);
        } else {
          selectedLayoutId.value = null;
        }
      } catch (err) {
        console.error('Error fetching layouts:', err);
        layoutsError.value = err.response?.data?.message || err.message || 'Failed to fetch layouts';
        layouts.value = [];
        selectedLayoutId.value = null;
      } finally {
        isLoadingLayouts.value = false;
      }
    };

    onMounted(() => {
      fetchLayouts();
    });

    const save = async () => {
      if (!selectedLayout.value) {
        error.value = 'Please select a layout to save';
        return;
      }

      isLoading.value = true;
      error.value = null;
      successMessage.value = null;

      try {
        const layout = selectedLayout.value;
        const framePayload = layout.frames.map((frame, index) => ({
          id: frame.id?.toString() || null,
          name: frame.name,
          content_id: frame.contentId || null,
          x: frame.x,
          y: frame.y,
          width: frame.width,
          height: frame.height,
          z_index: frame.zIndex || index + 1,
          image_fit: frame.imageFit || 'contain',
          order_index: index,
        }));

        const layoutData = {
          name: layout.name,
          description: layout.description || null,
          canvas_width: canvasSize.value.width,
          canvas_height: canvasSize.value.height,
          frames: framePayload,
        };

        const isExistingLayout =
          layout.id &&
          !layout.id.toString().startsWith('temp-') &&
          !isNaN(Number(layout.id));

        let savedLayout;

        if (isExistingLayout) {
          const response = await layoutService.update(Number(layout.id), layoutData);
          savedLayout = response.data.data;
        } else {
          const response = await layoutService.create(layoutData);
          savedLayout = response.data.data;
        }

        // Create layout items (frames)
        layout.id = savedLayout.id.toString();
        layout.canvas_width = savedLayout.canvas_width;
        layout.canvas_height = savedLayout.canvas_height;

        successMessage.value = 'Layout saved successfully!';

        await fetchLayouts(savedLayout.id.toString());

        // Clear success message after 3 seconds
        setTimeout(() => {
          successMessage.value = null;
        }, 3000);
      } catch (err) {
        console.error('Error saving layout:', err);
        error.value = err.response?.data?.message || err.message || 'Failed to save layout';
      } finally {
        isLoading.value = false;
      }
    };

    const close = () => {
      console.log('Close multi-frame editor');
      // TODO: Navigate back or close modal
    };

    return {
      canvasRef,
      deviceName,
      canvasSize,
      layouts,
      selectedLayout,
      selectedFrame,
      selectLayout,
      addLayout,
      deleteLayout,
      selectFrame,
      addFrame,
      deleteFrame,
      updateFrame,
      updateFrameProp,
      updateFrameImageFit,
      bringForward,
      sendBackward,
      handleCanvasClick,
      save,
      close,
      isLoading,
      error,
      successMessage,
      isLoadingLayouts,
      layoutsError,
      fetchLayouts,
    };
  },
};
</script>

<style scoped>
.multiframe-tab {
  display: flex;
  height: 100%;
  min-height: calc(100vh - 80px);
  overflow: hidden;
  margin: -32px;
  padding: 0;
}

.sidebar-left,
.sidebar-middle {
  background-color: var(--bg-primary);
  border-right: 1px solid var(--border-color);
  display: flex;
  flex-direction: column;
  overflow: hidden;
  flex-shrink: 0;
  max-width: 300px;
}

.sidebar-left {
  width: 256px;
  padding: 0px 10px;
}

.sidebar-middle {
  flex: 1;
}

.sidebar-content {
  flex: 1;
  overflow-y: auto;
  padding-top: 16px;
}

.section {
  border-bottom: 1px solid var(--border-subtle);
  padding-bottom: 16px;
  margin-bottom: 10px;
}

.section-title {
  font-size: 14px;
  font-weight: 600;
  color: var(--text-secondary);
  margin-bottom: 12px;
  padding: 0 16px;
}

.info-panel,
.form-panel,
.list-panel {
  background-color: var(--bg-primary);
  padding: 12px;
  border-radius: 6px;
  margin: 0 16px;
}

.info-row {
  display: flex;
  justify-content: space-between;
  align-items: center;
  font-size: 14px;
  margin-bottom: 4px;
}

.info-label {
  color: var(--text-secondary);
}

.info-value {
  font-weight: 500;
  color: var(--text-secondary);
}

.list {
  list-style: none;
  padding: 0;
  margin: 0;
  max-height: 10vh;
  overflow-y: auto;
}

.list-item {
  width: 100%;
  text-align: left;
  padding: 8px 12px;
  border-radius: 6px;
  font-size: 14px;
  transition: all 0.2s;
  background-color: transparent;
  border: none;
  color: var(--text-secondary);
  cursor: pointer;
  margin-bottom: 4px;
}

.list-item:hover {
  background-color: var(--bg-hover);
}

.list-item.active {
  background-color: var(--button-primary-bg);
  color: var(--color-white);
}

.list-actions {
  display: flex;
  gap: 8px;
  padding-top: 8px;
  border-top: 1px solid var(--border-subtle);
  margin-top: 8px;
}

.btn-action {
  flex: 1;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 6px;
  background-color: var(--bg-tertiary);
  color: var(--text-secondary);
  border: none;
  padding: 8px 12px;
  border-radius: 6px;
  font-size: 14px;
  cursor: pointer;
  transition: all 0.2s;
}

.btn-action:hover:not(:disabled) {
  background-color: var(--bg-hover);
}

.btn-action:disabled {
  background-color: var(--bg-primary);
  opacity: 0.5;
  cursor: not-allowed;
}

.form-group {
  margin-bottom: 12px;
}

.form-group:last-child {
  margin-bottom: 0;
}

.form-label {
  display: block;
  font-size: 12px;
  color: var(--text-secondary);
  margin-bottom: 4px;
}

.form-input {
  width: 100%;
  background-color: var(--bg-primary);
  color: var(--text-primary);
  border: 1px solid var(--border-color);
  padding: 6px 8px;
  border-radius: 6px;
  font-size: 14px;
}

.form-input:focus {
  outline: none;
  border-color: var(--button-primary-bg);
  box-shadow: 0 0 0 2px rgba(37, 99, 235, 0.2);
}

.form-input:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

.form-grid {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 8px;
}

.button-group {
  display: flex;
  gap: 8px;
  width: 100%;
}

.button-group .btn-action {
  flex: 1;
  min-width: 0;
}

.empty-message,
.empty-state {
  text-align: center;
  color: #9ca3af;
  font-size: 14px;
  padding: 16px;
}

.canvas-area {
  min-width: 1280px;
  flex: 1;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 16px;
  background-color: var(--bg-primary);
  overflow: auto;
  position: relative;
}

.canvas {
  position: relative;
  background-color: var(--bg-tertiary);
  box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.5);
  overflow: hidden;
}

.grid-overlay {
  position: absolute;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  pointer-events: none;
  z-index: 0;
}

.grid-line {
  position: absolute;
  background-color: var(--text-secondary);
}

.grid-line-h {
  width: 100%;
  height: 1px;
}

.grid-line-v {
  width: 1px;
  height: 100%;
}

.grid-line-h.grid-line-1 {
  top: 25%;
  transform: translateY(-50%);
}

.grid-line-h.grid-line-2 {
  top: 50%;
  transform: translateY(-50%);
  background-color: var(--text-secondary);
  height: 2px;
}

.grid-line-h.grid-line-3 {
  top: 75%;
  transform: translateY(-50%);
}

.grid-line-v.grid-line-1 {
  left: 25%;
  transform: translateX(-50%);
}

.grid-line-v.grid-line-2 {
  left: 50%;
  transform: translateX(-50%);
  background-color: var(--text-secondary);
  width: 2px;
}

.grid-line-v.grid-line-3 {
  left: 75%;
  transform: translateX(-50%);
}

.bottom-actions {
  position: absolute;
  bottom: 16px;
  right: 16px;
  display: flex;
  gap: 8px;
  z-index: 100;
}

.btn-primary,
.btn-secondary {
  padding: 8px 16px;
  border-radius: 6px;
  font-size: 14px;
  font-weight: 500;
  cursor: pointer;
  border: none;
  transition: all 0.2s;
}

.btn-primary {
  background-color: var(--button-primary-bg);
  color: var(--color-white);
}

.btn-primary:hover {
  background-color: var(--button-primary-hover);
}

.btn-secondary {
  background-color: var(--bg-primary);
  color: var(--text-primary);
  border: 1px solid var(--border-color);
}

.btn-secondary:hover {
  background-color: var(--bg-hover);
  color: var(--text-primary);
}

.btn-primary:disabled,
.btn-secondary:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

.error-message {
  color: #dc2626;
  font-size: 14px;
  margin-bottom: 8px;
  padding: 8px 12px;
  background-color: rgba(220, 38, 38, 0.1);
  border-radius: 6px;
  border: 1px solid rgba(220, 38, 38, 0.3);
}

.success-message {
  color: #16a34a;
  font-size: 14px;
  margin-bottom: 8px;
  padding: 8px 12px;
  background-color: rgba(22, 163, 74, 0.1);
  border-radius: 6px;
  border: 1px solid rgba(22, 163, 74, 0.3);
}

.info-message {
  color: var(--text-secondary);
  font-size: 14px;
  margin-bottom: 8px;
  padding: 8px 12px;
  background-color: rgba(37, 99, 235, 0.1);
  border-radius: 6px;
  border: 1px solid rgba(37, 99, 235, 0.3);
}

.status-message {
  margin: 0 16px 8px;
}
</style>


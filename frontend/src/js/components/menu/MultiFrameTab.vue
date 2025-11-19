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
          <ul class="list">
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
            <button class="btn-action" @click="addLayout">
              <Plus :size="16" />
              Add
            </button>
            <button
              class="btn-action"
              @click="deleteLayout"
              :disabled="!selectedLayout || layouts.length <= 1"
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
                @input="updateLayoutName"
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
                  @input="updateFrameName"
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
        <button class="btn-primary" @click="save">Save</button>
        <button class="btn-secondary" @click="close">Close</button>
      </div>
    </div>
  </div>
</template>

<script>
import { ref, computed } from 'vue';
import { Plus, Trash2, ArrowUp, ArrowDown } from 'lucide-vue-next';
import FrameComponent from '../multiframe/MultiFrameFrame.vue';

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

    // Layouts and frames state
    const layouts = ref([
      {
        id: '1',
        name: 'Initial Layout',
        frames: [
          {
            id: 'f1',
            name: 'Frame 1',
            x: 0,
            y: 0,
            width: 907,
            height: 240,
            zIndex: 1,
            imageFit: 'contain',
            mediaUrl: null,
            mediaType: null,
          },
          {
            id: 'f2',
            name: 'Frame 2',
            x: 907,
            y: 0,
            width: 373,
            height: 562,
            zIndex: 2,
            imageFit: 'contain',
            mediaUrl: null,
            mediaType: null,
          },
        ],
      },
    ]);

    const selectedLayoutId = ref('1');
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
    };

    const addLayout = () => {
      const newLayout = {
        id: Date.now().toString(),
        name: `Layout ${layouts.value.length + 1}`,
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
      }
    };

    const updateLayoutName = () => {
      // Name is already updated via v-model
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

    const updateFrameName = () => {
      // Name is already updated via v-model
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

    const save = () => {
      console.log('save layout:', selectedLayout.value);
      // TODO: Save to backend
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
      updateLayoutName,
      selectFrame,
      addFrame,
      deleteFrame,
      updateFrame,
      updateFrameName,
      updateFrameProp,
      updateFrameImageFit,
      bringForward,
      sendBackward,
      handleCanvasClick,
      save,
      close,
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
</style>


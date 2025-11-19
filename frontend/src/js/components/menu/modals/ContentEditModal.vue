<template>
  <teleport to="body">
    <div v-if="open" class="modal-overlay" @click.self="emitClose">
      <div class="edit-modal">
        <header class="modal-header">
          <div class="modal-title">
            <Type :size="18" />
            <div>
              <p>Edit Content</p>
              <small>{{ content?.title }}</small>
            </div>
          </div>
          <button class="icon-button" @click="emitClose">
            <X :size="16" />
          </button>
        </header>

        <div class="modal-body">
          <section class="preview-panel">
            <div class="preview-media">
              <div
                class="media-surface"
                ref="mediaSurface"
                :style="previewMediaStyle"
              >
                <video
                  v-if="content?.type === 'Video' && content?.url"
                  :src="content.url"
                  class="media-video"
                  autoplay
                  loop
                  muted
                  playsinline
                />
                <div
                  v-else
                  class="media-image"
                  :style="{ backgroundImage: content?.thumbnail ? `url(${content.thumbnail})` : undefined }"
                />
                <div
                  class="text-preview"
                  ref="textPreview"
                  :key="animationKey"
                  :class="[isPreviewPlaying ? textAnimationClass : '', { dragging: isDragging }]"
                  :style="textPreviewStyle"
                  @mousedown.prevent.stop="startDrag"
                  @touchstart.prevent.stop="startDrag"
                  @animationend="handleAnimationEnd"
                >
                  {{ settings.text }}
                </div>
              </div>
            </div>
            <div class="rotation-control">
              <label>
                <RotateCw :size="14" />
                Rotate (°)
              </label>
              <div class="slider-row">
                <input type="range" min="-180" max="180" v-model.number="settings.rotation" />
                <span>{{ settings.rotation }}°</span>
              </div>
              <div class="rotate-buttons">
                <button type="button" @click="rotateBy(-90)">-90°</button>
                <button type="button" @click="rotateBy(90)">+90°</button>
              </div>
              <button class="play-button" type="button" @click="togglePreview">
                <Play :size="14" v-if="!isPreviewPlaying" />
                <Pause :size="14" v-else />
                <span>{{ isPreviewPlaying ? 'Pause' : 'Play' }}</span>
              </button>
            </div>
          </section>

          <section class="controls-panel">
            <div class="control-group">
              <label>Ad Text</label>
              <textarea v-model="settings.text" rows="3" placeholder="Enter promotional text"></textarea>
            </div>

            <div class="control-grid">
              <div class="control-group">
                <label>Font Family</label>
                <select v-model="settings.fontFamily">
                  <option v-for="font in fontFamilies" :key="font" :value="font">{{ font }}</option>
                </select>
              </div>
              <div class="control-group">
                <label>Font Size (px)</label>
                <input type="number" min="12" max="200" v-model.number="settings.fontSize" />
              </div>
              <div class="control-group">
                <label>Font Color</label>
                <input type="color" v-model="settings.fontColor" />
              </div>
              <div class="control-group">
                <label>Weight</label>
                <select v-model="settings.fontWeight">
                  <option value="300">Light</option>
                  <option value="400">Regular</option>
                  <option value="600">Semi Bold</option>
                  <option value="700">Bold</option>
                </select>
              </div>
            </div>

            <div class="control-grid">
              <div class="control-group">
                <label>Alignment</label>
                <select v-model="settings.textAlign">
                  <option value="left">Left</option>
                  <option value="center">Center</option>
                  <option value="right">Right</option>
                  <option value="justify">Justify</option>
                </select>
              </div>
              <div class="control-group">
                <label>Orientation</label>
                <select v-model="settings.orientation">
                  <option value="horizontal">Horizontal</option>
                  <option value="vertical">Vertical</option>
                </select>
              </div>
              <div class="control-group">
                <label>Letter Spacing (px)</label>
                <input type="number" min="-20" max="60" v-model.number="settings.letterSpacing" />
              </div>
              <div class="control-group">
                <label>Line Height</label>
                <input type="number" step="0.1" min="0.5" max="3" v-model.number="settings.lineHeight" />
              </div>
            </div>

            <div class="control-grid">
              <div class="control-group">
                <label>Horizontal Position (%)</label>
                <input type="range" min="0" max="100" v-model.number="settings.horizontalPosition" />
                <small>{{ settings.horizontalPosition }}%</small>
              </div>
              <div class="control-group">
                <label>Vertical Position (%)</label>
                <input type="range" min="0" max="100" v-model.number="settings.verticalPosition" />
                <small>{{ settings.verticalPosition }}%</small>
              </div>
              <div class="control-group">
                <label>Start / End (s)</label>
                <div class="dual-input">
                  <input type="number" min="0" v-model.number="settings.startTime" placeholder="Start" />
                  <input type="number" min="0" v-model.number="settings.endTime" placeholder="End" />
                </div>
              </div>
              <div class="control-group">
                <label>Display Duration / Interval (s)</label>
                <div class="dual-input">
                  <input type="number" min="0" v-model.number="settings.displayDuration" placeholder="Duration" />
                  <input type="number" min="0" v-model.number="settings.interval" placeholder="Interval" />
                </div>
              </div>
            </div>

            <div class="control-group">
              <label>Scroll Mode</label>
              <select v-model="settings.scrollMode">
                <option value="none">Static</option>
                <option value="leftToRight">Left → Right</option>
                <option value="rightToLeft">Right → Left</option>
                <option value="bottomToTop">Bottom → Top</option>
                <option value="topToBottom">Top → Bottom</option>
              </select>
              <div class="control-grid compact">
                <div class="control-group">
                  <label>Speed (seconds per loop)</label>
                  <input type="number" min="1" max="60" v-model.number="settings.scrollSpeed" />
                </div>
                <div class="control-group">
                  <label>Loop Count (0 = infinite)</label>
                  <input type="number" min="0" max="100" v-model.number="settings.loopCount" />
                </div>
              </div>
            </div>

            <div class="control-grid">
              <div class="control-group">
                <label>
                  <input type="checkbox" v-model="settings.outlineEnabled" />
                  Text Frame
                </label>
                <div v-if="settings.outlineEnabled" class="control-grid compact">
                  <div class="control-group">
                    <label>Color</label>
                    <input type="color" v-model="settings.outlineColor" />
                  </div>
                  <div class="control-group">
                    <label>Size (px)</label>
                    <input type="number" min="1" max="10" v-model.number="settings.outlineWidth" />
                  </div>
                </div>
              </div>
              <div class="control-group">
                <label>
                  <input type="checkbox" v-model="settings.shadowEnabled" />
                  Drop Shadow
                </label>
                <div v-if="settings.shadowEnabled" class="control-grid compact">
                  <div class="control-group">
                    <label>Color</label>
                    <input type="color" v-model="settings.shadowColor" />
                  </div>
                  <div class="control-group">
                    <label>Blur (px)</label>
                    <input type="number" min="0" max="20" v-model.number="settings.shadowBlur" />
                  </div>
                </div>
              </div>
            </div>

            <div class="modal-actions">
              <button class="secondary" @click="emitClose">Cancel</button>
              <button class="primary" @click="handleApply">Apply</button>
            </div>
          </section>
        </div>
      </div>
    </div>
  </teleport>
</template>

<script>
import { reactive, ref, computed, nextTick, onBeforeUnmount, watch } from 'vue';
import { Play, Pause, RotateCw, Type, X } from 'lucide-vue-next';

export default {
  name: 'ContentEditModal',
  components: {
    Play,
    Pause,
    RotateCw,
    Type,
    X,
  },
  props: {
    open: {
      type: Boolean,
      default: false,
    },
    content: {
      type: Object,
      default: null,
    },
  },
  emits: ['close', 'apply'],
  setup(props, { emit }) {
    const fontFamilies = [
      'MS Gothic',
      'MS Mincho',
      'Meiryo',
      'Yu Gothic',
      'Arial',
      'Roboto',
      'Noto Sans JP',
    ];

    const defaultSettings = () => ({
      rotation: 0,
      text: 'ようこそ！',
      fontFamily: 'MS Gothic',
      fontSize: 72,
      fontColor: '#ffffff',
      fontWeight: '600',
      textAlign: 'center',
      orientation: 'horizontal',
      letterSpacing: 0,
      lineHeight: 1.1,
      horizontalPosition: 50,
      verticalPosition: 50,
      startTime: 0,
      endTime: 30,
      displayDuration: 5,
      interval: 1,
      scrollMode: 'none',
      scrollSpeed: 1,
      loopCount: 0,
      outlineEnabled: false,
      outlineColor: '#000000',
      outlineWidth: 2,
      shadowEnabled: false,
      shadowColor: '#000000',
      shadowBlur: 6,
    });

    const settings = reactive(defaultSettings());
    const isPreviewPlaying = ref(false);
    const animationCounter = ref(0);
    const isDragging = ref(false);
    const mediaSurface = ref(null);
    const textPreview = ref(null);
    const animationDistance = ref({ x: 0, y: 0 });

    const emitClose = () => {
      emit('close');
    };

    const handleApply = () => {
      emit('apply', { ...settings });
      emitClose();
    };

    const rotateBy = (delta) => {
      let next = settings.rotation + delta;
      while (next > 180) next -= 360;
      while (next < -180) next += 360;
      settings.rotation = next;
    };

    const previewMediaStyle = computed(() => {
      const rotation = settings.rotation;
      // Calculate scale to fit rotated content within container
      // For any rectangle rotated by angle θ, scale = 1 / (|cos(θ)| + |sin(θ)|)
      // This ensures the rotated rectangle always fits within the original bounds
      const absRotation = Math.abs(rotation);
      const normalizedRotation = absRotation % 90;
      
      if (normalizedRotation === 0) {
        // No rotation or 90/180/270 degrees - no scaling needed
        return {
          transform: `rotate(${rotation}deg)`,
          transformOrigin: 'center center',
        };
      }
      
      const radians = (normalizedRotation * Math.PI) / 180;
      const cos = Math.abs(Math.cos(radians));
      const sin = Math.abs(Math.sin(radians));
      const scale = 1 / (cos + sin);
      
      return {
        transform: `rotate(${rotation}deg) scale(${scale})`,
        transformOrigin: 'center center',
      };
    });

    const animationKey = computed(() =>
      `${isPreviewPlaying.value ? textAnimationClass.value : 'static'}-${animationCounter.value}`
    );

    const textAnimationClass = computed(() => {
      switch (settings.scrollMode) {
        case 'leftToRight':
          return 'marquee-left-right';
        case 'rightToLeft':
          return 'marquee-right-left';
        case 'bottomToTop':
          return 'marquee-bottom-top';
        case 'topToBottom':
          return 'marquee-top-bottom';
        default:
          return '';
      }
    });

    const calculateAnimationDistance = () => {
      if (!textPreview.value || !mediaSurface.value) return;
      
      const textRect = textPreview.value.getBoundingClientRect();
      const surfaceRect = mediaSurface.value.getBoundingClientRect();
      
      animationDistance.value = {
        x: surfaceRect.width + textRect.width,
        y: surfaceRect.height + textRect.height,
      };
    };

    const textPreviewStyle = computed(() => ({
      fontFamily: settings.fontFamily,
      fontSize: `${settings.fontSize}px`,
      color: settings.fontColor,
      fontWeight: settings.fontWeight,
      textAlign: settings.textAlign,
      letterSpacing: `${settings.letterSpacing}px`,
      lineHeight: settings.lineHeight,
      writingMode: settings.orientation === 'vertical' ? 'vertical-rl' : 'horizontal-tb',
      top: `${settings.verticalPosition}%`,
      left: `${settings.horizontalPosition}%`,
      textShadow: settings.shadowEnabled ? `0 0 ${settings.shadowBlur}px ${settings.shadowColor}` : 'none',
      WebkitTextStroke: settings.outlineEnabled
        ? `${settings.outlineWidth}px ${settings.outlineColor}`
        : 'initial',
      animationDuration: `${Math.max(settings.scrollSpeed, 1)}s`,
      animationIterationCount: settings.loopCount === 0 ? 'infinite' : settings.loopCount,
      animationPlayState: isPreviewPlaying.value ? 'running' : 'paused',
      '--animation-distance-x': `${animationDistance.value.x}px`,
      '--animation-distance-y': `${animationDistance.value.y}px`,
    }));

    const togglePreview = async () => {
      if (isPreviewPlaying.value) {
        // Pause
        isPreviewPlaying.value = false;
      } else {
        // Play
        if (settings.scrollMode === 'none') {
          return; // Don't play if no animation mode selected
        }
        isPreviewPlaying.value = false;
        animationCounter.value += 1;
        await nextTick();
        isPreviewPlaying.value = true;
      }
    };

    const handleAnimationEnd = () => {
      // Only stop if not infinite loop (loopCount !== 0)
      // For infinite loops, animation will continue, so we don't stop
      if (settings.loopCount !== 0 && isPreviewPlaying.value) {
        isPreviewPlaying.value = false;
      }
    };

    const updatePositionFromEvent = (event) => {
      const surface = mediaSurface.value;
      if (!surface) return;
      const rect = surface.getBoundingClientRect();
      const clientX = event.touches ? event.touches[0].clientX : event.clientX;
      const clientY = event.touches ? event.touches[0].clientY : event.clientY;
      const relativeX = ((clientX - rect.left) / rect.width) * 100;
      const relativeY = ((clientY - rect.top) / rect.height) * 100;
      settings.horizontalPosition = Math.min(100, Math.max(0, relativeX));
      settings.verticalPosition = Math.min(100, Math.max(0, relativeY));
    };

    const handleDrag = (event) => {
      if (!isDragging.value) return;
      updatePositionFromEvent(event);
    };

    const endDrag = () => {
      if (!isDragging.value) return;
      isDragging.value = false;
      window.removeEventListener('mousemove', handleDrag);
      window.removeEventListener('mouseup', endDrag);
      window.removeEventListener('touchmove', handleDrag);
      window.removeEventListener('touchend', endDrag);
    };

    const startDrag = (event) => {
      isPreviewPlaying.value = false;
      isDragging.value = true;
      updatePositionFromEvent(event);
      window.addEventListener('mousemove', handleDrag, { passive: true });
      window.addEventListener('mouseup', endDrag);
      window.addEventListener('touchmove', handleDrag, { passive: true });
      window.addEventListener('touchend', endDrag);
    };

    onBeforeUnmount(() => {
      endDrag();
    });

    watch(
      () => props.open,
      (isOpen) => {
        if (isOpen) {
          Object.assign(settings, defaultSettings());
          isPreviewPlaying.value = false;
          nextTick(() => {
            calculateAnimationDistance();
          });
        } else {
          isPreviewPlaying.value = false;
          endDrag();
        }
      }
    );

    watch(
      () => settings.scrollMode,
      () => {
        isPreviewPlaying.value = false;
        animationCounter.value += 1;
      }
    );

    watch(
      () => [
        settings.text,
        settings.fontSize,
        settings.fontFamily,
        settings.orientation,
        settings.fontWeight,
        settings.letterSpacing,
      ],
      () => {
        if (props.open) {
          nextTick(() => {
            calculateAnimationDistance();
          });
        }
      },
      { deep: true }
    );

    return {
      fontFamilies,
      settings,
      isPreviewPlaying,
      animationKey,
      textAnimationClass,
      textPreviewStyle,
      previewMediaStyle,
      isDragging,
      mediaSurface,
      textPreview,
      emitClose,
      handleApply,
      rotateBy,
      togglePreview,
      handleAnimationEnd,
      startDrag,
      Pause,
    };
  },
};
</script>

<style scoped>
.modal-overlay {
  position: fixed;
  inset: 0;
  background: rgba(0, 0, 0, 0.65);
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 24px;
  z-index: 2000;
}

.edit-modal {
  width: min(1200px, 100%);
  max-height: 90vh;
  background: var(--bg-primary, #1a1a1a);
  border-radius: 16px;
  border: 1px solid var(--border-color);
  display: flex;
  flex-direction: column;
  overflow: hidden;
}

.modal-header {
  padding: 20px 24px;
  display: flex;
  align-items: center;
  justify-content: space-between;
  border-bottom: 1px solid var(--border-color);
}

.modal-title {
  display: flex;
  align-items: center;
  gap: 12px;
  color: var(--text-primary);
}

.modal-title p {
  margin: 0;
  font-size: 18px;
  font-weight: 600;
}

.modal-title small {
  display: block;
  font-size: 12px;
  color: var(--text-secondary);
}

.icon-button {
  width: 32px;
  height: 32px;
  border-radius: 8px;
  border: 1px solid var(--border-color);
  background: transparent;
  color: var(--text-primary);
  cursor: pointer;
  justify-items: center;
}

.modal-body {
  display: grid;
  grid-template-columns: 1.1fr 1fr;
  gap: 16px;
  padding: 24px;
  overflow-y: auto;
}

.preview-panel {
  display: flex;
  flex-direction: column;
  gap: 16px;
}

.preview-media {
  border-radius: 12px;
  border: 1px solid var(--border-color);
  padding: 12px;
  background: var(--bg-secondary);
  height: 360px;
  overflow: hidden;
  position: relative;
}

.media-surface {
  position: relative;
  width: 100%;
  height: 100%;
  border-radius: 8px;
  overflow: hidden;
}

.media-video {
  width: 100%;
  height: 100%;
  object-fit: cover;
  display: block;
}

.media-image {
  width: 100%;
  height: 100%;
  background-size: cover;
  background-position: center;
  background-repeat: no-repeat;
}

.text-preview {
  position: absolute;
  transform: translate(-50%, -50%);
  white-space: pre;
  cursor: grab;
  z-index: 10;
}

.text-preview.dragging {
  cursor: grabbing;
}

.rotation-control label {
  display: flex;
  align-items: center;
  gap: 6px;
  font-size: 13px;
  color: var(--text-secondary);
}

.slider-row {
  display: flex;
  align-items: center;
  gap: 12px;
}

.rotate-buttons {
  display: flex;
  gap: 8px;
  margin-top: 8px;
}

.rotate-buttons button {
  flex: 1;
  border: 1px solid var(--border-color);
  background: var(--bg-tertiary);
  color: var(--text-primary);
  border-radius: 8px;
  padding: 6px 0;
  cursor: pointer;
  font-weight: 600;
}

.play-button {
  margin-top: 12px;
  display: inline-flex;
  align-items: center;
  gap: 6px;
  border: none;
  border-radius: 8px;
  padding: 8px 12px;
  background: var(--accent, #4c8dff);
  color: #fff;
  font-weight: 600;
  cursor: pointer;
}

.controls-panel {
  display: flex;
  flex-direction: column;
  gap: 16px;
}

.control-group {
  display: flex;
  flex-direction: column;
  gap: 6px;
  color: var(--text-secondary);
}

.control-group label {
  font-size: 13px;
  color: var(--text-primary);
}

.control-group textarea,
.control-group select,
.control-group input[type='number'],
.control-group input[type='color'] {
  width: 100%;
  border-radius: 8px;
  border: 1px solid var(--border-color);
  background: var(--bg-tertiary);
  padding: 8px 10px;
  color: var(--text-primary);
}

.control-group input[type='range'] {
  width: 100%;
}

.control-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
  gap: 12px;
}

.control-grid.compact {
  grid-template-columns: repeat(auto-fit, minmax(140px, 1fr));
}

.dual-input {
  display: flex;
  gap: 8px;
}

.dual-input input {
  flex: 1;
}

.modal-actions {
  display: flex;
  justify-content: flex-end;
  gap: 12px;
  margin-top: 8px;
}

.modal-actions button {
  border-radius: 8px;
  padding: 10px 18px;
  border: none;
  font-weight: 600;
  cursor: pointer;
}

.modal-actions .secondary {
  background: var(--bg-tertiary);
  color: var(--text-primary);
}

.modal-actions .primary {
  background: var(--accent, #4c8dff);
  color: #fff;
}

.marquee-left-right,
.marquee-right-left,
.marquee-top-bottom,
.marquee-bottom-top {
  animation-timing-function: linear;
}

.marquee-left-right {
  animation-name: marquee-left-right;
}

.marquee-right-left {
  animation-name: marquee-right-left;
}

.marquee-top-bottom {
  animation-name: marquee-top-bottom;
}

.marquee-bottom-top {
  animation-name: marquee-bottom-top;
}

@keyframes marquee-left-right {
  0% {
    transform: translate(calc(-50% - var(--animation-distance-x, 400px)), -50%);
  }
  100% {
    transform: translate(calc(50% + var(--animation-distance-x, 400px)), -50%);
  }
}

@keyframes marquee-right-left {
  0% {
    transform: translate(calc(50% + var(--animation-distance-x, 400px)), -50%);
  }
  100% {
    transform: translate(calc(-50% - var(--animation-distance-x, 400px)), -50%);
  }
}

@keyframes marquee-top-bottom {
  0% {
    transform: translate(-50%, calc(-50% - var(--animation-distance-y, 400px)));
  }
  100% {
    transform: translate(-50%, calc(50% + var(--animation-distance-y, 400px)));
  }
}

@keyframes marquee-bottom-top {
  0% {
    transform: translate(-50%, calc(50% + var(--animation-distance-y, 400px)));
  }
  100% {
    transform: translate(-50%, calc(-50% - var(--animation-distance-y, 400px)));
  }
}

@media (max-width: 768px) {
  .modal-body {
    grid-template-columns: 1fr;
  }
}
</style>


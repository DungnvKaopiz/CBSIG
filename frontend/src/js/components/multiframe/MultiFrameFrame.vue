<template>
  <div
    :style="frameStyle"
    :class="['frame', { selected: isSelected }]"
    @mousedown="handleMouseDown($event, 'drag')"
    @dblclick="handleDoubleClick"
  >
    <!-- Media Content -->
    <div class="frame-content">
      <div v-if="!frame.mediaUrl" class="frame-empty">
        <button
          @click.stop="triggerFileInput"
          class="upload-button"
          :class="{ visible: isSelected }"
        >
          <Upload :size="24" />
        </button>
      </div>
      <img
        v-else-if="frame.mediaType === 'image'"
        :src="frame.mediaUrl"
        :alt="frame.name"
        :style="{ objectFit: frame.imageFit || 'contain' }"
        class="frame-media"
      />
      <video
        v-else-if="frame.mediaType === 'video'"
        :src="frame.mediaUrl"
        :style="{ objectFit: frame.imageFit || 'contain' }"
        class="frame-media"
        autoplay
        loop
        muted
      />
    </div>

    <!-- Frame Label -->
    <div class="frame-label">
      <input
        v-if="isRenaming && isSelected"
        v-model="renameValue"
        @blur="handleRename"
        @keydown="handleRenameKeyDown"
        @mousedown.stop
        class="frame-label-input"
        ref="renameInput"
      />
      <span v-else>{{ frame.name }}</span>
    </div>

    <!-- Hidden File Input -->
    <input
      ref="fileInput"
      type="file"
      @change="handleMediaUpload"
      accept="image/*,video/*"
      class="hidden-input"
    />

    <!-- Selected Frame Controls -->
    <template v-if="isSelected">
      <!-- Delete Button -->
      <button
        @click.stop="handleDelete"
        class="delete-button"
        :aria-label="`Delete ${frame.name}`"
      >
        <Trash2 :size="14" />
      </button>

      <!-- Resize Handles -->
      <div
        class="resize-handle resize-tl"
        @mousedown.stop="handleMouseDown($event, 'resize-tl')"
      ></div>
      <div
        class="resize-handle resize-tr"
        @mousedown.stop="handleMouseDown($event, 'resize-tr')"
      ></div>
      <div
        class="resize-handle resize-bl"
        @mousedown.stop="handleMouseDown($event, 'resize-bl')"
      ></div>
      <div
        class="resize-handle resize-br"
        @mousedown.stop="handleMouseDown($event, 'resize-br')"
      ></div>
    </template>
  </div>
</template>

<script>
import { ref, computed, watch, nextTick } from 'vue';
import { Upload, Trash2 } from 'lucide-vue-next';

const SNAP_THRESHOLD = 10;

export default {
  name: 'MultiFrameFrame',
  components: {
    Upload,
    Trash2,
  },
  props: {
    frame: {
      type: Object,
      required: true,
    },
    isSelected: {
      type: Boolean,
      default: false,
    },
    canvasRef: {
      type: Object,
      default: null,
    },
  },
  emits: ['select', 'update', 'delete'],
  setup(props, { emit }) {
    const fileInput = ref(null);
    const renameInput = ref(null);
    const isRenaming = ref(false);
    const renameValue = ref(props.frame.name);

    watch(
      () => props.frame.name,
      (newName) => {
        if (!isRenaming.value) {
          renameValue.value = newName;
        }
      }
    );

    const frameStyle = computed(() => ({
      left: `${props.frame.x}px`,
      top: `${props.frame.y}px`,
      width: `${props.frame.width}px`,
      height: `${props.frame.height}px`,
      zIndex: props.frame.zIndex || 1,
    }));

    const triggerFileInput = () => {
      fileInput.value?.click();
    };

    const handleMediaUpload = (e) => {
      const file = e.target.files?.[0];
      if (!file) return;

      const mediaType = file.type.startsWith('image/')
        ? 'image'
        : file.type.startsWith('video/')
        ? 'video'
        : null;

      if (!mediaType) return;

      const reader = new FileReader();
      reader.onloadend = () => {
        emit('update', props.frame.id, {
          mediaUrl: reader.result,
          mediaType,
        });
      };
      reader.readAsDataURL(file);
    };

    const handleDelete = () => {
      emit('delete', props.frame.id);
    };

    const handleDoubleClick = () => {
      if (props.isSelected) {
        isRenaming.value = true;
        nextTick(() => {
          renameInput.value?.focus();
          renameInput.value?.select();
        });
      }
    };

    const handleRename = () => {
      if (renameValue.value.trim() && renameValue.value.trim() !== props.frame.name) {
        emit('update', props.frame.id, { name: renameValue.value.trim() });
      } else {
        renameValue.value = props.frame.name;
      }
      isRenaming.value = false;
    };

    const handleRenameKeyDown = (e) => {
      if (e.key === 'Enter') {
        handleRename();
      } else if (e.key === 'Escape') {
        renameValue.value = props.frame.name;
        isRenaming.value = false;
      }
    };

    const handleMouseDown = (e, action) => {
      if (isRenaming.value || e.target.tagName === 'INPUT') {
        return;
      }

      e.preventDefault();
      e.stopPropagation();
      emit('select', props.frame.id);

      const startX = e.clientX;
      const startY = e.clientY;
      const startFrameX = props.frame.x;
      const startFrameY = props.frame.y;
      const startFrameWidth = props.frame.width;
      const startFrameHeight = props.frame.height;

      const canvasRect = props.canvasRef?.getBoundingClientRect();
      if (!canvasRect) return;

      const gridLinesX = [
        0,
        canvasRect.width / 4,
        canvasRect.width / 2,
        (canvasRect.width * 3) / 4,
        canvasRect.width,
      ];
      const gridLinesY = [
        0,
        canvasRect.height / 4,
        canvasRect.height / 2,
        (canvasRect.height * 3) / 4,
        canvasRect.height,
      ];

      const handleMouseMove = (moveEvent) => {
        let dx = moveEvent.clientX - startX;
        let dy = moveEvent.clientY - startY;

        let newX = startFrameX;
        let newY = startFrameY;
        let newWidth = startFrameWidth;
        let newHeight = startFrameHeight;

        if (action === 'drag') {
          newX = startFrameX + dx;
          newY = startFrameY + dy;

          // Snap logic for drag
          for (const line of gridLinesX) {
            if (Math.abs(newX - line) < SNAP_THRESHOLD) newX = line;
            if (Math.abs(newX + newWidth - line) < SNAP_THRESHOLD)
              newX = line - newWidth;
          }
          for (const line of gridLinesY) {
            if (Math.abs(newY - line) < SNAP_THRESHOLD) newY = line;
            if (Math.abs(newY + newHeight - line) < SNAP_THRESHOLD)
              newY = line - newHeight;
          }
        } else {
          // Resizing
          if (action.includes('r')) newWidth = startFrameWidth + dx;
          if (action.includes('l')) {
            newWidth = startFrameWidth - dx;
            newX = startFrameX + dx;
          }
          if (action.includes('b')) newHeight = startFrameHeight + dy;
          if (action.includes('t')) {
            newHeight = startFrameHeight - dy;
            newY = startFrameY + dy;
          }

          // Snap logic for resize
          let finalX = newX;
          let finalY = newY;
          let finalWidth = newWidth;
          let finalHeight = newHeight;

          for (const line of gridLinesX) {
            if (action.includes('l')) {
              if (Math.abs(finalX - line) < SNAP_THRESHOLD) {
                finalWidth += finalX - line;
                finalX = line;
              }
            }
            if (action.includes('r')) {
              if (Math.abs(finalX + finalWidth - line) < SNAP_THRESHOLD) {
                finalWidth = line - finalX;
              }
            }
          }
          for (const line of gridLinesY) {
            if (action.includes('t')) {
              if (Math.abs(finalY - line) < SNAP_THRESHOLD) {
                finalHeight += finalY - line;
                finalY = line;
              }
            }
            if (action.includes('b')) {
              if (Math.abs(finalY + finalHeight - line) < SNAP_THRESHOLD) {
                finalHeight = line - finalY;
              }
            }
          }
          newX = finalX;
          newY = finalY;
          newWidth = finalWidth;
          newHeight = finalHeight;
        }

        // Constrain to canvas bounds
        newX = Math.max(0, Math.min(newX, canvasRect.width - newWidth));
        newY = Math.max(0, Math.min(newY, canvasRect.height - newHeight));
        newWidth = Math.max(20, Math.min(newWidth, canvasRect.width - newX));
        newHeight = Math.max(20, Math.min(newHeight, canvasRect.height - newY));

        emit('update', props.frame.id, {
          x: newX,
          y: newY,
          width: newWidth,
          height: newHeight,
        });
      };

      const handleMouseUp = () => {
        window.removeEventListener('mousemove', handleMouseMove);
        window.removeEventListener('mouseup', handleMouseUp);
      };

      window.addEventListener('mousemove', handleMouseMove);
      window.addEventListener('mouseup', handleMouseUp);
    };

    return {
      fileInput,
      renameInput,
      isRenaming,
      renameValue,
      frameStyle,
      triggerFileInput,
      handleMediaUpload,
      handleDelete,
      handleDoubleClick,
      handleRename,
      handleRenameKeyDown,
      handleMouseDown,
    };
  },
};
</script>

<style scoped>
.frame {
  position: absolute;
  background-color: rgba(0, 0, 0, 0.4);
  backdrop-filter: blur(4px);
  border: 1px solid rgba(107, 114, 128, 0.5);
  user-select: none;
  transition: border-color 0.1s ease-in-out;
  cursor: move;
}

.frame.selected {
  border: 2px solid #2563eb;
}

.frame-content {
  width: 100%;
  height: 100%;
  position: relative;
  overflow: hidden;
}

.frame-empty {
  width: 100%;
  height: 100%;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 8px;
}

.upload-button {
  opacity: 0;
  transition: opacity 0.2s;
  background-color: rgba(0, 0, 0, 0.5);
  color: #ffffff;
  padding: 8px;
  border-radius: 50%;
  border: none;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
}

.upload-button.visible,
.frame:hover .upload-button {
  opacity: 1;
}

.frame-media {
  width: 100%;
  height: 100%;
  display: block;
}

.frame-label {
  position: absolute;
  top: 0;
  left: 0;
  background-color: rgba(0, 0, 0, 0.5);
  color: #ffffff;
  font-size: 12px;
  padding: 4px 8px;
  max-width: calc(100% - 40px);
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
  z-index: 10;
}

.frame-label-input {
  background-color: #1a1a1a;
  color: #ffffff;
  font-size: 12px;
  width: 100%;
  height: 100%;
  padding: 0;
  margin: 0;
  border: none;
  outline: 1px solid #2563eb;
  padding: 2px 4px;
}

.frame-label-input:focus {
  outline: 2px solid #2563eb;
}

.hidden-input {
  display: none;
}

.delete-button {
  position: absolute;
  top: 4px;
  right: 4px;
  background-color: #dc2626;
  color: #ffffff;
  border-radius: 50%;
  padding: 6px;
  border: 2px solid #ffffff;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  opacity: 0.9;
  transition: all 0.2s;
  z-index: 20;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.3);
  width: 28px;
  height: 28px;
}

.delete-button:hover {
  opacity: 1;
  background-color: #b91c1c;
  transform: scale(1.1);
  box-shadow: 0 4px 8px rgba(0, 0, 0, 0.4);
}

.resize-handle {
  position: absolute;
  width: 8px;
  height: 8px;
  background-color: #2563eb;
  border: 1px solid #ffffff;
  z-index: 10;
}

.resize-tl {
  top: -4px;
  left: -4px;
  cursor: nwse-resize;
}

.resize-tr {
  top: -4px;
  right: -4px;
  cursor: nesw-resize;
}

.resize-bl {
  bottom: -4px;
  left: -4px;
  cursor: nesw-resize;
}

.resize-br {
  bottom: -4px;
  right: -4px;
  cursor: nwse-resize;
}
</style>


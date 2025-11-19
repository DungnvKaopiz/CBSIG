<template>
  <teleport to="body">
    <div v-if="open" class="playlist-modal-overlay" @click.self="emitClose">
      <div class="playlist-modal">
        <header class="playlist-header">
          <div>
            <h2>{{ modalTitle }}</h2>
            <p>Select content to build your playlist.</p>
          </div>
          <button class="icon-button" @click="emitClose">
            <X :size="16" />
          </button>
        </header>

        <div class="playlist-body">
          <label class="form-group">
            <span class="label">Playlist Name</span>
            <input
              v-model="playlistName"
              type="text"
              placeholder="Enter playlist name"
            />
          </label>

          <div class="content-filter">
            <div class="filter-tabs">
              <button
                v-for="option in filterOptions"
                :key="option.value"
                :class="['filter-tab', { active: filterType === option.value }]"
                @click="filterType = option.value"
              >
                {{ option.label }}
              </button>
            </div>
            <div class="search-input-wrapper">
              <Search :size="16" class="search-icon" />
              <input
                v-model="searchQuery"
                type="text"
                placeholder="Search content..."
              />
            </div>
          </div>

          <div class="content-grid">
            <label
              v-for="item in filteredContents"
              :key="item.id"
              class="content-tile"
              :class="{ selected: selectedIds.has(item.id) }"
            >
              <input
                type="checkbox"
                :value="item.id"
                v-model="selectedIdsArray"
              />
              <div class="tile-thumb">
                <div class="thumb-placeholder">
                  <component :is="item.type === 'Video' ? Film : Image" :size="32" />
                </div>
              </div>
              <div class="tile-info">
                <p class="tile-title">{{ item.title }}</p>
                <span class="tile-type">{{ item.type }}</span>
              </div>
              <Check class="check-icon" :size="16" />
            </label>
            <div v-if="filteredContents.length === 0" class="empty-state">
              No content found.
            </div>
          </div>
        </div>

        <footer class="playlist-footer">
          <button class="secondary" @click="emitClose">Cancel</button>
          <button
            class="primary"
            :disabled="!isValid"
            @click="submitPlaylist"
          >
            {{ primaryButtonLabel }}
          </button>
        </footer>
      </div>
    </div>
  </teleport>
</template>

<script>
import { computed, reactive, ref, watch } from 'vue';
import { Check, Film, Image, Search, X } from 'lucide-vue-next';

export default {
  name: 'PlaylistCreateModal',
  components: {
    Check,
    Film,
    Image,
    Search,
    X,
  },
  props: {
    open: {
      type: Boolean,
      default: false,
    },
    contents: {
      type: Array,
      default: () => [],
    },
    mode: {
      type: String,
      default: 'create',
    },
    initialPlaylist: {
      type: Object,
      default: null,
    },
  },
  emits: ['close', 'submit'],
  setup(props, { emit }) {
    const playlistName = ref('');
    const searchQuery = ref('');
    const filterType = ref('all');
    const selectedIds = reactive(new Set());

    const filterOptions = [
      { label: 'All', value: 'all' },
      { label: 'Images', value: 'Image' },
      { label: 'Videos', value: 'Video' },
    ];

    const selectedIdsArray = computed({
      get() {
        return Array.from(selectedIds);
      },
      set(values) {
        selectedIds.clear();
        values.forEach((id) => selectedIds.add(id));
      },
    });

    const normalizedContents = computed(() =>
      (props.contents || []).map((item) => ({
        id: item.id,
        title: item.title,
        type: item.type || 'Unknown',
      }))
    );

    const filteredContents = computed(() => {
      const query = searchQuery.value.toLowerCase();
      return normalizedContents.value.filter((item) => {
        const matchesType =
          filterType.value === 'all' || item.type === filterType.value;
        const matchesQuery =
          item.title.toLowerCase().includes(query);
        return matchesType && matchesQuery;
      });
    });

    const isValid = computed(
      () =>
        playlistName.value.trim().length > 0 &&
        selectedIds.size > 0
    );

    const applyInitialState = () => {
      const initial = props.mode === 'edit' && props.initialPlaylist
        ? props.initialPlaylist
        : null;
      playlistName.value = initial?.name || '';
      searchQuery.value = '';
      filterType.value = 'all';
      selectedIds.clear();
      (initial?.contentIds || []).forEach((id) => selectedIds.add(id));
    };

    const emitClose = () => {
      emit('close');
    };

    const submitPlaylist = () => {
      if (!isValid.value) return;
      emit('submit', {
        id: props.initialPlaylist?.id || null,
        name: playlistName.value.trim(),
        contentIds: Array.from(selectedIds),
      });
    };

    watch(
      () => props.open,
      (isOpen) => {
        if (isOpen) {
          applyInitialState();
        }
      }
    );

    watch(
      () => props.initialPlaylist,
      () => {
        if (props.open && props.mode === 'edit') {
          applyInitialState();
        }
      }
    );

    const modalTitle = computed(() =>
      props.mode === 'edit' ? 'Edit Playlist' : 'Create Playlist'
    );

    const primaryButtonLabel = computed(() =>
      props.mode === 'edit' ? 'Save Changes' : 'Create Playlist'
    );

    return {
      modalTitle,
      primaryButtonLabel,
      playlistName,
      searchQuery,
      filterType,
      filterOptions,
      filteredContents,
      selectedIds,
      selectedIdsArray,
      isValid,
      emitClose,
      submitPlaylist,
      Film,
      Image,
    };
  },
};
</script>

<style scoped>
.playlist-modal-overlay {
  position: fixed;
  inset: 0;
  background: rgba(0, 0, 0, 0.65);
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 24px;
  z-index: 3000;
}

.playlist-modal {
  width: min(520px, 100%);
  max-height: 90vh;
  background: var(--bg-primary);
  border-radius: 16px;
  border: 1px solid var(--border-color);
  display: flex;
  flex-direction: column;
  overflow: hidden;
  color: var(--text-primary);
}

.playlist-header {
  padding: 20px 24px 12px;
  display: flex;
  align-items: flex-start;
  justify-content: space-between;
  gap: 12px;
  border-bottom: 1px solid var(--border-color);
}

.playlist-header h2 {
  margin: 0;
  font-size: 20px;
}

.playlist-header p {
  margin: 4px 0 0;
  font-size: 13px;
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

.playlist-body {
  padding: 20px 24px;
  display: flex;
  flex-direction: column;
  gap: 16px;
  overflow-y: auto;
}

.form-group {
  display: flex;
  flex-direction: column;
  gap: 8px;
}

.label {
  font-size: 13px;
  color: var(--text-secondary);
}

.form-group input,
.form-group select {
  width: 100%;
  border-radius: 10px;
  border: 1px solid var(--border-subtle);
  background: var(--bg-secondary);
  color: var(--text-primary);
  padding: 10px 12px;
  font-size: 14px;
}

.content-filter {
  display: flex;
  flex-direction: column;
  gap: 12px;
}

.filter-tabs {
  display: inline-flex;
  border-radius: 999px;
  background: var(--bg-secondary);
  padding: 4px;
  width: fit-content;
}

.filter-tab {
  border: none;
  background: transparent;
  color: var(--text-secondary);
  padding: 6px 16px;
  border-radius: 999px;
  cursor: pointer;
  font-size: 13px;
}

.filter-tab.active {
  background: var(--button-primary-bg);
  color: var(--color-white);
}

.search-input-wrapper {
  position: relative;
}

.search-input-wrapper input {
  width: 100%;
  border-radius: 10px;
  border: 1px solid var(--border-subtle);
  background: var(--bg-secondary);
  color: var(--text-primary);
  padding: 10px 12px 10px 36px;
}

.search-icon {
  position: absolute;
  left: 12px;
  top: 50%;
  transform: translateY(-50%);
  color: var(--text-secondary);
}

.content-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(140px, 1fr));
  gap: 12px;
  max-height: 220px;
  overflow-y: auto;
  padding-right: 4px;
}

.content-grid::-webkit-scrollbar {
  width: 6px;
}

.content-grid::-webkit-scrollbar-thumb {
  background: var(--border-subtle);
  border-radius: 999px;
}

.content-tile {
  position: relative;
  display: flex;
  flex-direction: column;
  gap: 8px;
  border: 1px solid var(--border-color);
  border-radius: 12px;
  padding: 10px;
  cursor: pointer;
  transition: border-color 0.2s;
}

.content-tile input {
  position: absolute;
  opacity: 0;
  pointer-events: none;
}

.content-tile.selected {
  border-color: var(--button-primary-bg);
}

.tile-thumb {
  width: 100%;
  aspect-ratio: 16 / 9;
  border-radius: 8px;
  background: var(--bg-secondary);
  display: flex;
  align-items: center;
  justify-content: center;
}

.thumb-placeholder {
  color: var(--text-secondary);
}

.tile-info {
  display: flex;
  flex-direction: column;
  gap: 4px;
}

.tile-title {
  margin: 0;
  font-size: 13px;
  font-weight: 500;
  color: var(--text-primary);
}

.tile-type {
  font-size: 12px;
  color: var(--text-secondary);
}

.check-icon {
  position: absolute;
  top: 10px;
  right: 10px;
  opacity: 0;
  transition: opacity 0.2s;
  color: var(--button-primary-bg);
}

.content-tile.selected .check-icon {
  opacity: 1;
}

.empty-state {
  grid-column: 1 / -1;
  text-align: center;
  padding: 16px;
  color: var(--text-secondary);
  border: 1px dashed var(--border-color);
  border-radius: 12px;
}

.playlist-footer {
  padding: 16px 24px 20px;
  display: flex;
  justify-content: flex-end;
  gap: 12px;
  border-top: 1px solid var(--border-color);
  background: var(--bg-secondary);
}

.playlist-footer button {
  border-radius: 999px;
  padding: 10px 20px;
  border: none;
  font-weight: 600;
  cursor: pointer;
}

.playlist-footer .secondary {
  background: var(--bg-secondary);
  color: var(--text-primary);
}

.playlist-footer .primary {
  background: var(--button-primary-bg);
  color: var(--color-white);
}

.playlist-footer .primary:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

@media (max-width: 600px) {
  .playlist-modal {
    max-height: 95vh;
  }
}
</style>


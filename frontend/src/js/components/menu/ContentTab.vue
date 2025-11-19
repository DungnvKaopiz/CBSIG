<template>
  <div class="tab-content content-tab">
    <div class="header-section">
      <h1 class="page-title">Content Library</h1>
      <div class="header-actions">
        <button class="upload-button" @click="uploadContent">
          <span>Upload Content</span>
        </button>
        <button class="playlist-button" @click="openPlaylistModal">
          <span>Create Playlist</span>
        </button>
      </div>
    </div>

    <div class="content-grid">
      <div
        v-for="content in filteredContent"
        :key="content.id"
        class="content-card"
        @click="selectContent(content.id)"
      >
        <div class="card-thumbnail">
          <img
            v-if="content.thumbnail"
            :src="content.thumbnail"
            :alt="content.title"
            class="thumbnail-image"
          />
          <div v-else class="thumbnail-placeholder">
            <component :is="content.type === 'Video' ? Film : Image" :size="48" class="placeholder-icon" />
          </div>
          <button class="card-edit" @click.stop="openEditModal(content)">
            <Pencil :size="14" />
            <span>Edit</span>
          </button>
        </div>
        <div class="card-info">
          <div class="card-title">{{ content.title }}</div>
          <div class="card-type">
            <component :is="content.type === 'Video' ? Film : Image" :size="14" class="type-icon" />
            <span>{{ content.type }}</span>
          </div>
        </div>
      </div>
    </div>

    <div class="playlist-section">
      <div class="playlist-section-header">
        <div>
          <h2>Your Playlists</h2>
          <p>{{ playlists.length }} total</p>
        </div>
      </div>

      <div v-if="playlists.length" class="playlist-grid">
        <div class="playlist-card" v-for="playlist in playlists" :key="playlist.id">
          <div class="playlist-card-header">
            <div>
              <h3>{{ playlist.name }}</h3>
              <p>{{ playlist.contentIds.length }} items</p>
            </div>
            <button class="playlist-edit" @click="openEditPlaylistModal(playlist)">Edit</button>
          </div>
          <div class="playlist-content-preview">
            <span
              v-for="(title, index) in getPlaylistContentTitles(playlist.contentIds)"
              :key="`${playlist.id}-${index}`"
              class="playlist-chip"
            >
              {{ title }}
            </span>
          </div>
        </div>
      </div>
      <div v-else class="playlist-empty">
        No playlists yet. Create one to get started.
      </div>
    </div>

    <ContentEditModal
      :open="isEditModalOpen"
      :content="editingContent"
      @close="closeEditModal"
      @apply="handleApply"
    />
    <PlaylistCreateModal
      :open="isPlaylistModalOpen"
      :contents="content"
      :mode="playlistModalMode"
      :initial-playlist="editingPlaylist"
      @close="closePlaylistModal"
      @submit="handlePlaylistSubmit"
    />
  </div>
</template>

<script>
import { ref, computed } from 'vue';
import { Film, Image as ImageIcon, Pencil } from 'lucide-vue-next';
import ContentEditModal from './modals/ContentEditModal.vue';
import PlaylistCreateModal from './modals/PlaylistCreateModal.vue';

export default {
  name: 'ContentTab',
  components: {
    Film,
    Image: ImageIcon,
    Pencil,
    ContentEditModal,
    PlaylistCreateModal,
  },
  setup() {
    const selectedId = ref(null);
    const content = ref([
      {
        id: '1',
        title: 'Summer Sale.mp4',
        type: 'Video',
        thumbnail: 'https://images.unsplash.com/photo-1559056199-641a0ac8b55e?w=400&h=300&fit=crop',
        url: 'https://commondatastorage.googleapis.com/gtv-videos-bucket/sample/BigBuckBunny.mp4',
      },
      {
        id: '2',
        title: 'New Product.jpg',
        type: 'Image',
        thumbnail: 'https://images.unsplash.com/photo-1506905925346-21bda4d32df4?w=400&h=300&fit=crop',
        url: 'https://images.unsplash.com/photo-1506905925346-21bda4d32df4?w=1920&h=1080&fit=crop',
      },
      {
        id: '3',
        title: 'Corporate Intro.mp4',
        type: 'Video',
        thumbnail: 'https://images.unsplash.com/photo-1506905925346-21bda4d32df4?w=400&h=300&fit=crop',
        url: 'https://commondatastorage.googleapis.com/gtv-videos-bucket/sample/ElephantsDream.mp4',
      },
    ]);

    const filteredContent = computed(() => content.value);
    const playlists = ref([
      { id: 'pl-1', name: 'Storefront Loop', contentIds: ['1', '2'] },
      { id: 'pl-2', name: 'Morning Welcome', contentIds: ['3'] },
    ]);
    const contentMap = computed(() => {
      const map = new Map();
      content.value.forEach((item) => map.set(item.id, item));
      return map;
    });
    const selectContent = (id) => {
      selectedId.value = id;
    };

    const isEditModalOpen = ref(false);
    const editingContent = ref(null);
    const isPlaylistModalOpen = ref(false);
    const playlistModalMode = ref('create');
    const editingPlaylist = ref(null);

    const openEditModal = (item) => {
      editingContent.value = item;
      isEditModalOpen.value = true;
    };

    const closeEditModal = () => {
      isEditModalOpen.value = false;
      editingContent.value = null;
    };

    const handleApply = (settings) => {
      console.log('Applying edit to content', {
        contentId: editingContent.value?.id,
        settings,
      });
      closeEditModal();
    };

    const openPlaylistModal = () => {
      playlistModalMode.value = 'create';
      editingPlaylist.value = null;
      isPlaylistModalOpen.value = true;
    };

    const openEditPlaylistModal = (playlist) => {
      playlistModalMode.value = 'edit';
      editingPlaylist.value = { ...playlist };
      isPlaylistModalOpen.value = true;
    };

    const closePlaylistModal = () => {
      isPlaylistModalOpen.value = false;
      editingPlaylist.value = null;
    };

    const handlePlaylistSubmit = (payload) => {
      const { id, name, contentIds } = payload;
      if (playlistModalMode.value === 'edit' && id) {
        const index = playlists.value.findIndex((item) => item.id === id);
        if (index !== -1) {
          playlists.value[index] = { ...playlists.value[index], name, contentIds };
        }
      } else {
        playlists.value.push({
          id: `pl-${Date.now()}`,
          name,
          contentIds,
        });
      }
      closePlaylistModal();
    };

    const getPlaylistContentTitles = (ids = []) => {
      const titles = ids
        .map((id) => contentMap.value.get(id)?.title || 'Unknown')
        .filter(Boolean);
      if (titles.length > 3) {
        return [...titles.slice(0, 3), `+${titles.length - 3} more`];
      }
      return titles.length ? titles : ['No content selected'];
    };

    const uploadContent = () => {
      console.log('Upload content');
    };

    return {
      selectedId,
      filteredContent,
      selectContent,
      uploadContent,
      isEditModalOpen,
      editingContent,
      openEditModal,
      closeEditModal,
      handleApply,
      playlists,
      content,
      isPlaylistModalOpen,
      playlistModalMode,
      editingPlaylist,
      openPlaylistModal,
      openEditPlaylistModal,
      closePlaylistModal,
      handlePlaylistSubmit,
      getPlaylistContentTitles,
      Film,
      Image: ImageIcon,
    };
  },
};
</script>

<style scoped>
.tab-content {
  color: var(--text-primary);
}

.content-tab {
  display: flex;
  flex-direction: column;
  gap: 24px;
}

.header-section {
  display: flex;
  align-items: center;
  justify-content: space-between;
  margin-bottom: 8px;
  flex-wrap: wrap;
  gap: 12px;
}

.page-title {
  color: var(--text-primary);
  font-size: 24px;
  font-weight: 600;
  margin: 0;
}

.header-actions {
  display: flex;
  gap: 12px;
}

.upload-button,
.playlist-button {
  display: flex;
  align-items: center;
  gap: 8px;
  background: var(--button-primary-bg);
  border: none;
  color: var(--color-white);
  padding: 10px 16px;
  border-radius: 6px;
  font-size: 14px;
  font-weight: 500;
  cursor: pointer;
  transition: background-color 0.2s;
}

.upload-button:hover,
.playlist-button:hover {
  background: var(--button-primary-hover);
}

.playlist-button.ghost {
  background: var(--bg-secondary);
  color: var(--text-primary);
  border: 1px solid var(--border-color);
}

.playlist-button.ghost:hover {
  background: var(--bg-tertiary);
}

.plus-icon {
  flex-shrink: 0;
}

.content-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
  gap: 20px;
}

.content-card {
  background: var(--bg-card);
  border: 1px solid var(--border-color);
  border-radius: 8px;
  overflow: hidden;
  cursor: pointer;
  transition: all 0.2s;
}

.content-card:hover {
  border-color: var(--border-hover);
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.3);
}

.card-thumbnail {
  width: 100%;
  height: 200px;
  background: var(--bg-tertiary);
  overflow: hidden;
  position: relative;
}

.thumbnail-image {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.thumbnail-placeholder {
  width: 100%;
  height: 100%;
  display: flex;
  align-items: center;
  justify-content: center;
  background: var(--bg-tertiary);
}

.card-edit {
  position: absolute;
  right: 12px;
  top: 12px;
  display: flex;
  align-items: center;
  gap: 4px;
  padding: 6px 10px;
  border-radius: 999px;
  border: none;
  background: rgba(0, 0, 0, 0.55);
  color: #fff;
  font-size: 12px;
  cursor: pointer;
  transition: opacity 0.2s;
}

.card-edit:hover {
  opacity: 0.85;
}

.placeholder-icon {
  color: #4d4d4d;
}

.card-info {
  padding: 16px;
}

.card-title {
  color: var(--text-primary);
  font-size: 14px;
  font-weight: 500;
  margin-bottom: 8px;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.card-type {
  display: flex;
  align-items: center;
  gap: 6px;
  color: var(--text-secondary);
  font-size: 12px;
}

.type-icon {
  flex-shrink: 0;
  color: var(--text-secondary);
}

.playlist-section {
  display: flex;
  flex-direction: column;
  gap: 16px;
}

.playlist-section-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 12px;
}

.playlist-section-header h2 {
  margin: 0;
  font-size: 18px;
}

.playlist-section-header p {
  margin: 4px 0 0;
  font-size: 13px;
  color: var(--text-secondary);
}

.playlist-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(260px, 1fr));
  gap: 16px;
}

.playlist-card {
  background: var(--bg-card);
  border: 1px solid var(--border-color);
  border-radius: 12px;
  padding: 16px;
  display: flex;
  flex-direction: column;
  gap: 12px;
}

.playlist-card-header {
  display: flex;
  align-items: flex-start;
  justify-content: space-between;
  gap: 12px;
}

.playlist-card-header h3 {
  margin: 0;
  font-size: 16px;
}

.playlist-card-header p {
  margin: 4px 0 0;
  font-size: 13px;
  color: var(--text-secondary);
}

.playlist-edit {
  border: 1px solid var(--border-color);
  background: transparent;
  color: var(--text-primary);
  border-radius: 999px;
  padding: 6px 12px;
  cursor: pointer;
  font-size: 13px;
}

.playlist-content-preview {
  display: flex;
  flex-wrap: wrap;
  gap: 6px;
}

.playlist-chip {
  border-radius: 999px;
  padding: 4px 10px;
  background: var(--bg-secondary);
  color: var(--text-secondary);
  font-size: 12px;
}

.playlist-empty {
  border: 1px dashed var(--border-color);
  border-radius: 12px;
  padding: 24px;
  text-align: center;
  color: var(--text-secondary);
}

@media (max-width: 768px) {
  .header-section {
    flex-direction: column;
    align-items: stretch;
    gap: 12px;
  }

  .page-title {
    font-size: 20px;
  }

  .upload-button {
    width: 100%;
    justify-content: center;
  }

  .content-grid {
    grid-template-columns: 1fr;
  }
}
</style>


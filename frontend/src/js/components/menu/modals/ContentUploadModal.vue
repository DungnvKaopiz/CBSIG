<template>
  <teleport to="body">
    <div v-if="open" class="content-upload-modal-overlay" @click.self="emitClose">
      <div class="content-upload-modal">
        <header class="modal-header">
          <div>
            <h2>Upload Content</h2>
            <p>Choose a method to add content to your library.</p>
          </div>
          <button class="icon-button" @click="emitClose">
            <X :size="16" />
          </button>
        </header>

        <!-- Tabs -->
        <div class="tabs">
          <button
            v-for="tab in tabs"
            :key="tab.value"
            :class="['tab-button', { active: activeTab === tab.value }]"
            @click="activeTab = tab.value"
          >
            <component :is="tab.icon" :size="16" />
            <span>{{ tab.label }}</span>
          </button>
        </div>

        <div class="modal-body">
          <!-- File Upload Tab -->
          <div v-if="activeTab === 'file'" class="tab-content">
            <div
              class="upload-zone"
              :class="{ 'drag-over': isDragOver, 'has-file': selectedFile }"
              @drop.prevent="handleDrop"
              @dragover.prevent="isDragOver = true"
              @dragleave.prevent="isDragOver = false"
              @click="triggerFileInput"
            >
              <input
                ref="fileInput"
                type="file"
                accept="image/*,video/*"
                @change="handleFileSelect"
                class="hidden-input"
              />
              <div v-if="!selectedFile" class="upload-placeholder">
                <Upload :size="48" class="upload-icon" />
                <p class="upload-text">Drag & drop your file here</p>
                <p class="upload-hint">or click to browse</p>
                <p class="upload-formats">Supports: Images (JPG, PNG, GIF) and Videos (MP4, MOV, AVI)</p>
              </div>
              <div v-else class="file-preview">
                <div class="preview-thumbnail">
                  <img
                    v-if="isImage"
                    :src="filePreviewUrl"
                    alt="Preview"
                    class="preview-image"
                  />
                  <video
                    v-else-if="isVideo"
                    :src="filePreviewUrl"
                    class="preview-video"
                    muted
                  />
                  <File :size="48" v-else class="preview-icon" />
                </div>
                <div class="file-info">
                  <p class="file-name">{{ selectedFile.name }}</p>
                  <p class="file-size">{{ formatFileSize(selectedFile.size) }}</p>
                </div>
                <button class="remove-file" @click.stop="removeFile">
                  <X :size="16" />
                </button>
              </div>
            </div>

            <label class="form-group">
              <span class="label">Content Name <span class="required">*</span></span>
              <input
                v-model="fileForm.name"
                type="text"
                placeholder="Enter content name"
                :class="{ error: errors.name }"
              />
              <span v-if="errors.name" class="error-message">{{ errors.name }}</span>
            </label>
          </div>

          <!-- URL Upload Tab -->
          <div v-if="activeTab === 'url'" class="tab-content">
            <label class="form-group">
              <span class="label">Content Name <span class="required">*</span></span>
              <input
                v-model="urlForm.name"
                type="text"
                placeholder="Enter content name"
                :class="{ error: errors.name }"
              />
              <span v-if="errors.name" class="error-message">{{ errors.name }}</span>
            </label>

            <label class="form-group">
              <span class="label">File URL <span class="required">*</span></span>
              <input
                v-model="urlForm.url"
                type="url"
                placeholder="https://example.com/image.jpg or https://example.com/video.mp4"
                :class="{ error: errors.url }"
              />
              <span v-if="errors.url" class="error-message">{{ errors.url }}</span>
              <small class="hint">Enter direct URL to an image or video file</small>
            </label>

            <div v-if="urlPreview" class="url-preview">
              <div class="preview-thumbnail">
                <img
                  v-if="urlPreview.type === 'image'"
                  :src="urlForm.url"
                  alt="Preview"
                  class="preview-image"
                  @error="urlPreview = null"
                />
                <video
                  v-else-if="urlPreview.type === 'video'"
                  :src="urlForm.url"
                  class="preview-video"
                  muted
                  @error="urlPreview = null"
                />
              </div>
            </div>
          </div>

          <!-- YouTube Tab -->
          <div v-if="activeTab === 'youtube'" class="tab-content">
            <label class="form-group">
              <span class="label">Content Name <span class="required">*</span></span>
              <input
                v-model="youtubeForm.name"
                type="text"
                placeholder="Enter content name"
                :class="{ error: errors.name }"
              />
              <span v-if="errors.name" class="error-message">{{ errors.name }}</span>
            </label>

            <label class="form-group">
              <span class="label">YouTube URL <span class="required">*</span></span>
              <input
                v-model="youtubeForm.url"
                type="url"
                placeholder="https://www.youtube.com/watch?v=VIDEO_ID or https://youtu.be/VIDEO_ID"
                :class="{ error: errors.youtubeUrl }"
                @input="handleYouTubeUrlInput"
              />
              <span v-if="errors.youtubeUrl" class="error-message">{{ errors.youtubeUrl }}</span>
              <small class="hint">Paste YouTube video URL</small>
            </label>

            <div v-if="youtubePreview" class="youtube-preview">
              <div class="preview-thumbnail">
                <img
                  :src="youtubePreview.thumbnail"
                  alt="YouTube Preview"
                  class="preview-image"
                />
                <div v-if="isLoadingYouTubeInfo" class="loading-overlay">
                  <div class="loading-spinner"></div>
                </div>
              </div>
              <div class="youtube-info">
                <p class="youtube-title">{{ youtubePreview.title || 'YouTube Video' }}</p>
                <p v-if="youtubePreview.author" class="youtube-author">by {{ youtubePreview.author }}</p>
                <p class="youtube-id">Video ID: {{ youtubePreview.videoId }}</p>
              </div>
            </div>
          </div>

          <!-- Error Message -->
          <div v-if="error" class="error-alert">
            <span class="error-text">{{ error }}</span>
          </div>
        </div>

        <footer class="modal-footer">
          <button class="secondary" @click="emitClose" :disabled="isSubmitting">Cancel</button>
          <button
            class="primary"
            :disabled="!isValid || isSubmitting"
            @click="handleSubmit"
          >
            {{ isSubmitting ? 'Uploading...' : 'Upload' }}
          </button>
        </footer>
      </div>
    </div>
  </teleport>
</template>

<script>
import { computed, ref, watch } from 'vue';
import { Upload, Link, Youtube, X, File } from 'lucide-vue-next';
import { contentService } from '@/api/contentService';

export default {
  name: 'ContentUploadModal',
  components: {
    Upload,
    Link,
    Youtube,
    X,
    File,
  },
  props: {
    open: {
      type: Boolean,
      default: false,
    },
  },
  emits: ['close', 'success'],
  setup(props, { emit }) {
    const activeTab = ref('file');
    const isDragOver = ref(false);
    const selectedFile = ref(null);
    const filePreviewUrl = ref(null);
    const fileInput = ref(null);
    const isSubmitting = ref(false);
    const error = ref(null);
    const urlPreview = ref(null);
    const youtubePreview = ref(null);
    const isLoadingYouTubeInfo = ref(false);

    const tabs = [
      { value: 'file', label: 'Upload File', icon: Upload },
      { value: 'url', label: 'From URL', icon: Link },
      { value: 'youtube', label: 'YouTube', icon: Youtube },
    ];

    const fileForm = ref({
      name: '',
    });

    const urlForm = ref({
      name: '',
      url: '',
    });

    const youtubeForm = ref({
      name: '',
      url: '',
    });

    const errors = ref({});

    const isImage = computed(() => {
      if (!selectedFile.value) return false;
      return selectedFile.value.type.startsWith('image/');
    });

    const isVideo = computed(() => {
      if (!selectedFile.value) return false;
      return selectedFile.value.type.startsWith('video/');
    });

    const isValid = computed(() => {
      if (activeTab.value === 'file') {
        return selectedFile.value && fileForm.value.name.trim();
      } else if (activeTab.value === 'url') {
        return urlForm.value.name.trim() && urlForm.value.url.trim();
      } else if (activeTab.value === 'youtube') {
        return youtubeForm.value.name.trim() && youtubeForm.value.url.trim() && youtubePreview.value;
      }
      return false;
    });

    const triggerFileInput = () => {
      fileInput.value?.click();
    };

    const handleFileSelect = (e) => {
      const file = e.target.files?.[0];
      if (file) {
        processFile(file);
      }
    };

    const handleDrop = (e) => {
      isDragOver.value = false;
      const file = e.dataTransfer.files?.[0];
      if (file) {
        processFile(file);
      }
    };

    const processFile = (file) => {
      // Validate file type
      if (!file.type.startsWith('image/') && !file.type.startsWith('video/')) {
        error.value = 'Please select an image or video file';
        return;
      }

      // Validate file size (max 500MB)
      const maxSize = 500 * 1024 * 1024; // 500MB
      if (file.size > maxSize) {
        error.value = 'File size must be less than 500MB';
        return;
      }

      selectedFile.value = file;
      error.value = null;

      // Auto-fill name if empty
      if (!fileForm.value.name.trim()) {
        fileForm.value.name = file.name.replace(/\.[^/.]+$/, '');
      }

      // Create preview URL
      if (file.type.startsWith('image/')) {
        filePreviewUrl.value = URL.createObjectURL(file);
      } else if (file.type.startsWith('video/')) {
        filePreviewUrl.value = URL.createObjectURL(file);
      }
    };

    const removeFile = () => {
      if (filePreviewUrl.value) {
        URL.revokeObjectURL(filePreviewUrl.value);
      }
      selectedFile.value = null;
      filePreviewUrl.value = null;
      fileForm.value.name = '';
      if (fileInput.value) {
        fileInput.value.value = '';
      }
    };

    const formatFileSize = (bytes) => {
      if (bytes === 0) return '0 Bytes';
      const k = 1024;
      const sizes = ['Bytes', 'KB', 'MB', 'GB'];
      const i = Math.floor(Math.log(bytes) / Math.log(k));
      return Math.round(bytes / Math.pow(k, i) * 100) / 100 + ' ' + sizes[i];
    };

    const fetchYouTubeVideoInfo = async (url) => {
      try {
        isLoadingYouTubeInfo.value = true;
        
        // Use YouTube oEmbed API (no API key required)
        const oEmbedUrl = `https://www.youtube.com/oembed?url=${encodeURIComponent(url)}&format=json`;
        const response = await fetch(oEmbedUrl);
        
        if (!response.ok) {
          throw new Error('Failed to fetch video info');
        }
        
        const data = await response.json();
        return {
          title: data.title,
          thumbnail: data.thumbnail_url,
          author: data.author_name,
        };
      } catch (err) {
        console.error('Error fetching YouTube video info:', err);
        return null;
      } finally {
        isLoadingYouTubeInfo.value = false;
      }
    };

    const handleYouTubeUrlInput = async () => {
      const url = youtubeForm.value.url.trim();
      if (!url) {
        youtubePreview.value = null;
        return;
      }

      // Extract YouTube video ID
      const videoId = extractYouTubeVideoId(url);
      if (videoId) {
        // Set initial preview with video ID and thumbnail
        youtubePreview.value = {
          videoId,
          thumbnail: `https://img.youtube.com/vi/${videoId}/maxresdefault.jpg`,
          title: 'Loading...',
        };
        errors.value.youtubeUrl = null;

        // Fetch video info from YouTube API
        const videoInfo = await fetchYouTubeVideoInfo(url);
        if (videoInfo) {
          youtubePreview.value = {
            videoId,
            thumbnail: videoInfo.thumbnail || `https://img.youtube.com/vi/${videoId}/maxresdefault.jpg`,
            title: videoInfo.title,
            author: videoInfo.author,
          };
          
          // Auto-fill name if empty
          if (!youtubeForm.value.name.trim()) {
            youtubeForm.value.name = videoInfo.title;
          }
        } else {
          // Fallback if API fails
          youtubePreview.value = {
            videoId,
            thumbnail: `https://img.youtube.com/vi/${videoId}/maxresdefault.jpg`,
            title: 'YouTube Video',
          };
        }
      } else {
        youtubePreview.value = null;
        errors.value.youtubeUrl = 'Invalid YouTube URL';
      }
    };

    const extractYouTubeVideoId = (url) => {
      const patterns = [
        /(?:youtube\.com\/watch\?v=|youtu\.be\/|youtube\.com\/embed\/)([^&\n?#]+)/,
        /youtube\.com\/watch\?.*v=([^&\n?#]+)/,
      ];

      for (const pattern of patterns) {
        const match = url.match(pattern);
        if (match && match[1]) {
          return match[1];
        }
      }
      return null;
    };

    // Watch URL input for preview
    watch(
      () => urlForm.value.url,
      (url) => {
        if (!url.trim()) {
          urlPreview.value = null;
          return;
        }

        // Try to determine if it's an image or video
        const imageExtensions = /\.(jpg|jpeg|png|gif|webp|bmp)(\?|$)/i;
        const videoExtensions = /\.(mp4|webm|ogg|mov|avi|wmv|flv)(\?|$)/i;

        if (imageExtensions.test(url)) {
          urlPreview.value = { type: 'image' };
        } else if (videoExtensions.test(url)) {
          urlPreview.value = { type: 'video' };
        } else {
          urlPreview.value = null;
        }
      }
    );

    const validateForm = () => {
      errors.value = {};

      if (activeTab.value === 'file') {
        if (!fileForm.value.name.trim()) {
          errors.value.name = 'Content name is required';
        }
      } else if (activeTab.value === 'url') {
        if (!urlForm.value.name.trim()) {
          errors.value.name = 'Content name is required';
        }
        if (!urlForm.value.url.trim()) {
          errors.value.url = 'URL is required';
        } else if (!isValidUrl(urlForm.value.url)) {
          errors.value.url = 'Please enter a valid URL';
        }
      } else if (activeTab.value === 'youtube') {
        if (!youtubeForm.value.name.trim()) {
          errors.value.name = 'Content name is required';
        }
        if (!youtubeForm.value.url.trim()) {
          errors.value.youtubeUrl = 'YouTube URL is required';
        } else if (!extractYouTubeVideoId(youtubeForm.value.url)) {
          errors.value.youtubeUrl = 'Invalid YouTube URL';
        }
      }

      return Object.keys(errors.value).length === 0;
    };

    const isValidUrl = (string) => {
      try {
        new URL(string);
        return true;
      } catch (_) {
        return false;
      }
    };

    const handleSubmit = async () => {
      if (!validateForm()) {
        return;
      }

      isSubmitting.value = true;
      error.value = null;

      try {
        let response;
        
        if (activeTab.value === 'file') {
          // File upload: sử dụng FormData
          // Type: 1=video, 2=image (theo migration)
          const formData = new FormData();
          formData.append('file', selectedFile.value);
          formData.append('name', fileForm.value.name.trim());
          formData.append('type', isImage.value ? '2' : '1');

          response = await contentService.create(formData);
        } else if (activeTab.value === 'url') {
          // URL upload: sử dụng JSON
          // Type: 1=video, 2=image (theo migration)
          response = await contentService.create({
            name: urlForm.value.name.trim(),
            file_url: urlForm.value.url.trim(),
            type: urlPreview.value?.type === 'image' ? 2 : 1,
          });
        } else if (activeTab.value === 'youtube') {
          // YouTube: sử dụng JSON
          // Type: 4=youtube (theo migration)
          response = await contentService.create({
            name: youtubeForm.value.name.trim(),
            file_url: youtubeForm.value.url.trim(),
            checksum: youtubePreview.value.videoId,
            type: 4,
            thumbnail_url: youtubePreview.value.thumbnail,
          });
        }
        
        emit('success', response.data.data);

        emitClose();
      } catch (err) {
        console.error('Error uploading content:', err);
        error.value = err.response?.data?.message || err.message || 'Failed to upload content';
      } finally {
        isSubmitting.value = false;
      }
    };

    const emitClose = () => {
      // Reset forms
      activeTab.value = 'file';
      selectedFile.value = null;
      filePreviewUrl.value = null;
      fileForm.value = { name: '' };
      urlForm.value = { name: '', url: '' };
      youtubeForm.value = { name: '', url: '' };
      errors.value = {};
      error.value = null;
      urlPreview.value = null;
      youtubePreview.value = null;
      if (fileInput.value) {
        fileInput.value.value = '';
      }
      emit('close');
    };

    // Cleanup preview URLs when component unmounts
    watch(
      () => props.open,
      (isOpen) => {
        if (!isOpen && filePreviewUrl.value) {
          URL.revokeObjectURL(filePreviewUrl.value);
        }
      }
    );

    return {
      activeTab,
      tabs,
      isDragOver,
      selectedFile,
      filePreviewUrl,
      fileInput,
      fileForm,
      urlForm,
      youtubeForm,
      errors,
      error,
      isSubmitting,
      isValid,
      isImage,
      isVideo,
      urlPreview,
      youtubePreview,
      isLoadingYouTubeInfo,
      triggerFileInput,
      handleFileSelect,
      handleDrop,
      removeFile,
      formatFileSize,
      handleYouTubeUrlInput,
      handleSubmit,
      emitClose,
    };
  },
};
</script>

<style scoped>
.content-upload-modal-overlay {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(0, 0, 0, 0.6);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 1000;
  padding: 20px;
}

.content-upload-modal {
  background: var(--bg-primary);
  border-radius: 12px;
  width: 100%;
  max-width: 600px;
  max-height: 90vh;
  display: flex;
  flex-direction: column;
  box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.5);
}

.modal-header {
  display: flex;
  align-items: flex-start;
  justify-content: space-between;
  padding: 24px;
  border-bottom: 1px solid var(--border-color);
}

.modal-header h2 {
  margin: 0 0 4px;
  font-size: 20px;
  font-weight: 600;
  color: var(--text-primary);
}

.modal-header p {
  margin: 0;
  font-size: 14px;
  color: var(--text-secondary);
}

.icon-button {
  background: transparent;
  border: none;
  color: var(--text-secondary);
  cursor: pointer;
  padding: 4px;
  border-radius: 4px;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: all 0.2s;
}

.icon-button:hover {
  background: var(--bg-hover);
  color: var(--text-primary);
}

.tabs {
  display: flex;
  border-bottom: 1px solid var(--border-color);
  padding: 0 24px;
  gap: 8px;
}

.tab-button {
  display: flex;
  align-items: center;
  gap: 8px;
  padding: 12px 16px;
  background: transparent;
  border: none;
  border-bottom: 2px solid transparent;
  color: var(--text-secondary);
  font-size: 14px;
  font-weight: 500;
  cursor: pointer;
  transition: all 0.2s;
}

.tab-button:hover {
  color: var(--text-primary);
  background: var(--bg-hover);
}

.tab-button.active {
  color: var(--button-primary-bg);
  border-bottom-color: var(--button-primary-bg);
}

.modal-body {
  flex: 1;
  overflow-y: auto;
  padding: 24px;
}

.tab-content {
  display: flex;
  flex-direction: column;
  gap: 16px;
}

.upload-zone {
  border: 2px dashed var(--border-color);
  border-radius: 8px;
  padding: 48px 24px;
  text-align: center;
  cursor: pointer;
  transition: all 0.2s;
  background: var(--bg-secondary);
}

.upload-zone:hover {
  border-color: var(--button-primary-bg);
  background: var(--bg-tertiary);
}

.upload-zone.drag-over {
  border-color: var(--button-primary-bg);
  background: var(--bg-tertiary);
}

.upload-zone.has-file {
  padding: 16px;
}

.upload-placeholder {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 8px;
}

.upload-icon {
  color: var(--text-secondary);
  margin-bottom: 8px;
}

.upload-text {
  font-size: 16px;
  font-weight: 500;
  color: var(--text-primary);
  margin: 0;
}

.upload-hint {
  font-size: 14px;
  color: var(--text-secondary);
  margin: 0;
}

.upload-formats {
  font-size: 12px;
  color: var(--text-secondary);
  margin: 8px 0 0;
}

.hidden-input {
  display: none;
}

.file-preview {
  display: flex;
  align-items: center;
  gap: 16px;
  position: relative;
}

.preview-thumbnail {
  width: 120px;
  height: 120px;
  border-radius: 8px;
  overflow: hidden;
  background: var(--bg-tertiary);
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
}

.preview-image,
.preview-video {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.preview-icon {
  color: var(--text-secondary);
}

.file-info {
  flex: 1;
  min-width: 0;
}

.file-name {
  font-size: 14px;
  font-weight: 500;
  color: var(--text-primary);
  margin: 0 0 4px;
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
}

.file-size {
  font-size: 12px;
  color: var(--text-secondary);
  margin: 0;
}

.remove-file {
  background: var(--bg-tertiary);
  border: none;
  color: var(--text-secondary);
  cursor: pointer;
  padding: 8px;
  border-radius: 4px;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: all 0.2s;
}

.remove-file:hover {
  background: var(--bg-hover);
  color: var(--text-primary);
}

.form-group {
  display: flex;
  flex-direction: column;
  gap: 6px;
}

.label {
  font-size: 14px;
  font-weight: 500;
  color: var(--text-primary);
}

.required {
  color: #dc2626;
}

.form-group input {
  padding: 10px 12px;
  border: 1px solid var(--border-color);
  border-radius: 6px;
  background: var(--bg-primary);
  color: var(--text-primary);
  font-size: 14px;
  transition: all 0.2s;
}

.form-group input:focus {
  outline: none;
  border-color: var(--button-primary-bg);
  box-shadow: 0 0 0 2px rgba(37, 99, 235, 0.2);
}

.form-group input.error {
  border-color: #dc2626;
}

.error-message {
  font-size: 12px;
  color: #dc2626;
}

.hint {
  font-size: 12px;
  color: var(--text-secondary);
}

.url-preview,
.youtube-preview {
  margin-top: 8px;
}

.youtube-preview {
  display: flex;
  gap: 16px;
  align-items: flex-start;
}

.loading-overlay {
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(0, 0, 0, 0.5);
  display: flex;
  align-items: center;
  justify-content: center;
  border-radius: 8px;
}

.loading-spinner {
  width: 32px;
  height: 32px;
  border: 3px solid rgba(255, 255, 255, 0.3);
  border-top-color: white;
  border-radius: 50%;
  animation: spin 0.8s linear infinite;
}

@keyframes spin {
  to {
    transform: rotate(360deg);
  }
}

.youtube-info {
  flex: 1;
}

.youtube-title {
  font-size: 14px;
  font-weight: 500;
  color: var(--text-primary);
  margin: 0 0 4px;
}

.youtube-author {
  font-size: 12px;
  color: var(--text-secondary);
  margin: 0 0 4px;
}

.youtube-id {
  font-size: 12px;
  color: var(--text-secondary);
  margin: 0;
}

.error-alert {
  padding: 12px;
  background: rgba(220, 38, 38, 0.1);
  border: 1px solid rgba(220, 38, 38, 0.3);
  border-radius: 6px;
  margin-top: 16px;
}

.error-text {
  font-size: 14px;
  color: #dc2626;
}

.modal-footer {
  display: flex;
  align-items: center;
  justify-content: flex-end;
  gap: 12px;
  padding: 24px;
  border-top: 1px solid var(--border-color);
}

.modal-footer button {
  padding: 10px 20px;
  border-radius: 6px;
  font-size: 14px;
  font-weight: 500;
  cursor: pointer;
  transition: all 0.2s;
  border: none;
}

.modal-footer button.secondary {
  background: var(--bg-secondary);
  color: var(--text-primary);
  border: 1px solid var(--border-color);
}

.modal-footer button.secondary:hover:not(:disabled) {
  background: var(--bg-hover);
}

.modal-footer button.primary {
  background: var(--button-primary-bg);
  color: var(--color-white);
}

.modal-footer button.primary:hover:not(:disabled) {
  background: var(--button-primary-hover);
}

.modal-footer button:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}
</style>


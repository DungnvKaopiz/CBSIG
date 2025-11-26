<template>
  <teleport to="body">
    <div v-if="open" class="device-modal-overlay" @click.self="emitClose">
      <div class="device-modal">
        <header class="device-header">
          <div>
            <h2>Add Device</h2>
            <p>Enter device information to add a new digital signage device.</p>
          </div>
          <button class="icon-button" @click="emitClose">
            <X :size="16" />
          </button>
        </header>

        <div class="device-body">
          <label class="form-group">
            <span class="label">Device UID <span class="required">*</span></span>
            <input
              v-model="form.device_uid"
              type="text"
              placeholder="e.g., STB-001-ABC123"
              :class="{ error: errors.device_uid }"
            />
            <span v-if="errors.device_uid" class="error-message">{{ errors.device_uid }}</span>
            <small class="hint">Mã định danh duy nhất của thiết bị (unique)</small>
          </label>

          <label class="form-group">
            <span class="label">Device Name <span class="required">*</span></span>
            <input
              v-model="form.name"
              type="text"
              placeholder="e.g., Lobby Display"
              :class="{ error: errors.name }"
            />
            <span v-if="errors.name" class="error-message">{{ errors.name }}</span>
            <small class="hint">Tên thân thiện của thiết bị</small>
          </label>

          <label class="form-group">
            <span class="label">Location</span>
            <input
              v-model="form.location"
              type="text"
              placeholder="e.g., Tầng 1 - Sảnh chính"
            />
          </label>

          <label class="form-group">
            <span class="label">Status</span>
            <select v-model.number="form.status">
              <option :value="1">Online</option>
              <option :value="2">Offline</option>
              <option :value="3">Syncing</option>
              <option :value="4">Error</option>
              <option :value="5">Pending</option>
            </select>
            <small class="hint">1=online, 2=offline, 3=syncing, 4=error, 5=pending</small>
          </label>

          <label class="form-group">
            <span class="label">IP Address</span>
            <input
              v-model="form.ip_address"
              type="text"
              placeholder="e.g., 192.168.1.100"
              maxlength="45"
              :class="{ error: errors.ip_address }"
            />
            <span v-if="errors.ip_address" class="error-message">{{ errors.ip_address }}</span>
          </label>

          <label class="form-group">
            <span class="label">Firmware Version</span>
            <input
              v-model="form.firmware_version"
              type="text"
              placeholder="e.g., v1.2.3"
              maxlength="50"
            />
          </label>

          <div class="form-row">
            <label class="form-group">
              <span class="label">Canvas Width (px)</span>
              <input
                v-model.number="form.canvas_width"
                type="number"
                min="1"
                placeholder="1280"
                :class="{ error: errors.canvas_width }"
              />
              <span v-if="errors.canvas_width" class="error-message">{{ errors.canvas_width }}</span>
            </label>

            <label class="form-group">
              <span class="label">Canvas Height (px)</span>
              <input
                v-model.number="form.canvas_height"
                type="number"
                min="1"
                placeholder="720"
                :class="{ error: errors.canvas_height }"
              />
              <span v-if="errors.canvas_height" class="error-message">{{ errors.canvas_height }}</span>
            </label>
          </div>

          <!-- Error Message -->
          <div v-if="error" class="error-alert">
            <span class="error-text">{{ error }}</span>
          </div>
        </div>

        <footer class="device-footer">
          <button class="secondary" @click="emitClose" :disabled="isSubmitting">Cancel</button>
          <button
            class="primary"
            :disabled="!isValid || isSubmitting"
            @click="submitDevice"
          >
            {{ isSubmitting ? 'Adding...' : 'Add Device' }}
          </button>
        </footer>
      </div>
    </div>
  </teleport>
</template>

<script>
import { computed, reactive, ref, watch } from 'vue';
import { X } from 'lucide-vue-next';

export default {
  name: 'DeviceCreateModal',
  components: {
    X,
  },
  props: {
    open: {
      type: Boolean,
      default: false,
    },
    isLoading: {
      type: Boolean,
      default: false,
    },
    error: {
      type: String,
      default: null,
    },
  },
  emits: ['close', 'submit'],
  setup(props, { emit }) {
    const isSubmitting = computed(() => props.isLoading);
    const errors = reactive({});

    const form = reactive({
      device_uid: '',
      name: '',
      location: '',
      status: 5, // default: pending
      ip_address: '',
      firmware_version: '',
      canvas_width: 1280,
      canvas_height: 720,
    });

    const isValid = computed(() => {
      return (
        form.device_uid.trim().length > 0 &&
        form.name.trim().length > 0 &&
        form.canvas_width > 0 &&
        form.canvas_height > 0
      );
    });

    const validateForm = () => {
      errors.device_uid = '';
      errors.name = '';
      errors.ip_address = '';
      errors.canvas_width = '';
      errors.canvas_height = '';

      if (!form.device_uid.trim()) {
        errors.device_uid = 'Device UID is required';
      }

      if (!form.name.trim()) {
        errors.name = 'Device name is required';
      }

      // Validate IP address format if provided
      if (form.ip_address.trim()) {
        const ipRegex = /^(?:(?:25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.){3}(?:25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)$/;
        if (!ipRegex.test(form.ip_address.trim())) {
          errors.ip_address = 'Invalid IP address format';
        }
      }

      if (!form.canvas_width || form.canvas_width < 1) {
        errors.canvas_width = 'Canvas width must be at least 1';
      }

      if (!form.canvas_height || form.canvas_height < 1) {
        errors.canvas_height = 'Canvas height must be at least 1';
      }

      return (
        !errors.device_uid &&
        !errors.name &&
        !errors.ip_address &&
        !errors.canvas_width &&
        !errors.canvas_height
      );
    };

    const resetForm = () => {
      form.device_uid = '';
      form.name = '';
      form.location = '';
      form.status = 5;
      form.ip_address = '';
      form.firmware_version = '';
      form.canvas_width = 1280;
      form.canvas_height = 720;
      errors.device_uid = '';
      errors.name = '';
      errors.ip_address = '';
      errors.canvas_width = '';
      errors.canvas_height = '';
    };

    const emitClose = () => {
      resetForm();
      emit('close');
    };

    const submitDevice = async () => {
      if (!validateForm()) {
        return;
      }

      try {
        emit('submit', {
          device_uid: form.device_uid.trim(),
          name: form.name.trim(),
          location: form.location.trim() || null,
          status: form.status,
          ip_address: form.ip_address.trim() || null,
          firmware_version: form.firmware_version.trim() || null,
          canvas_width: form.canvas_width,
          canvas_height: form.canvas_height,
        });
      } catch (error) {
        console.error('Error submitting device:', error);
      }
    };

    watch(
      () => props.open,
      (isOpen) => {
        if (isOpen) {
          resetForm();
        }
      }
    );

    // Reset form on successful submission (when modal closes and no error)
    watch(
      () => [props.open, props.error],
      ([isOpen, error]) => {
        if (!isOpen && !error) {
          resetForm();
        }
      }
    );

    return {
      form,
      errors,
      isValid,
      isSubmitting,
      error: computed(() => props.error),
      emitClose,
      submitDevice,
    };
  },
};
</script>

<style scoped>
.device-modal-overlay {
  position: fixed;
  inset: 0;
  background: rgba(0, 0, 0, 0.65);
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 24px;
  z-index: 3000;
}

.device-modal {
  width: min(600px, 100%);
  max-height: 90vh;
  background: var(--bg-primary);
  border-radius: 16px;
  border: 1px solid var(--border-color);
  display: flex;
  flex-direction: column;
  overflow: hidden;
  color: var(--text-primary);
}

.device-header {
  padding: 20px 24px 12px;
  display: flex;
  align-items: flex-start;
  justify-content: space-between;
  gap: 12px;
  border-bottom: 1px solid var(--border-color);
}

.device-header h2 {
  margin: 0;
  font-size: 20px;
}

.device-header p {
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
  display: flex;
  align-items: center;
  justify-content: center;
  transition: all 0.2s;
  flex-shrink: 0;
}

.icon-button:hover {
  background: var(--bg-hover);
  border-color: var(--border-hover);
}

.device-body {
  padding: 20px 24px;
  display: flex;
  flex-direction: column;
  gap: 20px;
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
  font-weight: 500;
}

.required {
  color: var(--color-red, #ef4444);
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
  transition: border-color 0.2s;
}

.form-group input:focus,
.form-group select:focus {
  outline: none;
  border-color: var(--border-hover);
}

.form-group input.error {
  border-color: var(--color-red, #ef4444);
}

.error-message {
  font-size: 12px;
  color: var(--color-red, #ef4444);
  margin-top: -4px;
}

.hint {
  font-size: 12px;
  color: var(--text-secondary);
  margin-top: -4px;
}

.form-row {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 16px;
}

.error-alert {
  padding: 12px 16px;
  background: rgba(239, 68, 68, 0.1);
  border: 1px solid var(--color-red, #ef4444);
  border-radius: 8px;
  margin-top: 8px;
}

.error-text {
  color: var(--color-red, #ef4444);
  font-size: 13px;
  display: block;
}

.device-footer {
  padding: 16px 24px 20px;
  display: flex;
  justify-content: flex-end;
  gap: 12px;
  border-top: 1px solid var(--border-color);
  background: var(--bg-secondary);
}

.device-footer button {
  border-radius: 999px;
  padding: 10px 20px;
  border: none;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.2s;
}

.device-footer .secondary {
  background: var(--bg-secondary);
  color: var(--text-primary);
  border: 1px solid var(--border-color);
}

.device-footer .secondary:hover {
  background: var(--bg-hover);
}

.device-footer .primary {
  background: var(--button-primary-bg);
  color: var(--color-white);
}

.device-footer .primary:hover:not(:disabled) {
  background: var(--button-primary-hover);
}

.device-footer .primary:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

@media (max-width: 600px) {
  .device-modal {
    max-height: 95vh;
  }

  .device-modal-overlay {
    padding: 16px;
  }

  .form-row {
    grid-template-columns: 1fr;
  }
}
</style>


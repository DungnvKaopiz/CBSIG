<template>
  <teleport to="body">
    <div v-if="open" class="modal-overlay" @click.self="emitClose">
      <div class="modal-panel" role="dialog" aria-modal="true">
        <header class="modal-header">
          <div>
            <h2>Create Schedule</h2>
          </div>
          <button class="icon-btn" @click="emitClose" aria-label="Close modal">
            <X />
          </button>
        </header>

        <div class="modal-body">
          <div class="form-section">
            <label>
              <span class="label">Schedule Name</span>
              <input v-model="form.name" type="text" placeholder="Enter schedule name" />
            </label>
            <label>
              <span class="label">Description</span>
              <textarea v-model="form.description" rows="3" placeholder="Enter description" />
            </label>
          </div>

          <div class="form-section">
            <span class="label">Select Content</span>
            <div class="content-select-wrapper" ref="contentDropdownRef">
              <button class="content-select" type="button" @click="toggleContentDropdown">
                <div class="content-info">
                  <p class="content-title">{{ form.content.name }}</p>
                  <span class="pill">{{ form.content.type }}</span>
                </div>
                <ChevronDown class="chevron" :class="{ open: showContentDropdown }" />
              </button>

              <div v-if="showContentDropdown" class="content-dropdown">
                <div class="dropdown-search">
                  <Search class="search-icon" />
                  <input
                    v-model="contentSearchQuery"
                    type="text"
                    placeholder="Search content..."
                    class="search-input"
                    @click.stop
                  />
                </div>
                <div
                  v-for="item in filteredContentOptions"
                  :key="item.id"
                  class="content-option"
                  @click="selectContent(item)"
                >
                  <div class="content-option-info">
                    <p class="content-option-title">{{ item.name }}</p>
                    <span class="pill">{{ item.type }}</span>
                  </div>
                  <p class="content-option-description">{{ item.description }}</p>
                </div>
                <div v-if="filteredContentOptions.length === 0" class="no-results">
                  No content found
                </div>
              </div>
            </div>
          </div>

          <div class="form-section">
            <span class="label">Assign Devices</span>
            <div class="device-select" ref="deviceDropdownRef">
              <button class="content-select" type="button" @click="toggleDeviceDropdown">
                <Monitor class="content-icon" />
                <div class="content-info">
                  <p class="content-title">{{ selectedDevicesLabel }}</p>
                </div>
                <ChevronDown class="chevron" :class="{ open: showDeviceDropdown }" />
              </button>
              <div v-if="showDeviceDropdown" class="device-dropdown">
                <div class="dropdown-search">
                  <Search class="search-icon" />
                  <input
                    v-model="deviceSearchQuery"
                    type="text"
                    placeholder="Search devices..."
                    class="search-input"
                    @click.stop
                  />
                </div>
                <label
                  v-for="device in filteredDevices"
                  :key="device"
                  class="device-option"
                >
                  <input
                    type="checkbox"
                    :value="device"
                    v-model="form.devices"
                  />
                  <span>{{ device }}</span>
                </label>
                <div v-if="filteredDevices.length === 0" class="no-results">
                  No devices found
                </div>
              </div>
            </div>
          </div>

          <div class="form-section">
            <div class="time-grid">
              <label>
                <span class="label">Start Time</span>
                <div class="input-icon">
                  <input v-model="form.startTime" type="time" />
                  <Clock3 class="input-icon__icon" />
                </div>
              </label>
              <label>
                <span class="label">End Time</span>
                <div class="input-icon">
                  <input v-model="form.endTime" type="time" />
                  <Clock3 class="input-icon__icon" />
                </div>
              </label>
            </div>
          </div>

          <div class="form-section">
            <span class="label">Repeat</span>
            <div class="select-field">
              <select v-model="form.repeat" @change="handleRepeatChange">
                <option v-for="option in repeatOptions" :key="option" :value="option">
                  {{ option }}
                </option>
              </select>
              <ChevronDown class="select-icon" />
            </div>
          </div>

          <div v-if="form.repeat === 'Custom Date'" class="form-section">
            <span class="label">Select Dates</span>
            <div class="date-picker-wrapper">
              <DatePicker
                :is-dark="isDark"
                v-model="form.customDates"
                is-inline
                is-multiple
                :min-date="minCustomDate"
                :model-config="datePickerConfig"
              />
              <div v-if="form.customDates.length > 0" class="selected-dates">
                <span
                  v-for="date in form.customDates"
                  :key="date"
                  class="date-chip"
                >
                  {{ formatDate(date) }}
                  <button type="button" class="chip-remove" @click="removeCustomDate(date)">
                    <X class="chip-icon" />
                  </button>
                </span>
              </div>
            </div>
          </div>

          <div class="form-section">
            <span class="label">Days of Week</span>
            <div class="day-grid">
              <button
                v-for="day in daysOfWeek"
                :key="day"
                type="button"
                :class="['day-btn', { active: form.days.includes(day), disabled: form.repeat === 'Custom Date' }]"
                :disabled="form.repeat === 'Custom Date'"
                @click="toggleDay(day)"
              >
                {{ day }}
              </button>
            </div>
          </div>
        </div>

        <footer class="modal-footer">
          <button class="primary" @click="handleSubmit">Create Schedule</button>
          <button class="secondary" @click="emitClose">Cancel</button>
        </footer>
      </div>
    </div>
  </teleport>
</template>

<script setup>
import { computed, onBeforeUnmount, onMounted, reactive, ref } from 'vue';
import { DatePicker } from 'v-calendar';
import 'v-calendar/style.css';
import { ChevronDown, Clock3, Monitor, PlayCircle, Search, X } from 'lucide-vue-next';
import { useTheme } from '@/composables/useTheme';

const { isDark } = useTheme();

const props = defineProps({
  open: {
    type: Boolean,
    default: false,
  },
  defaultContent: {
    type: Object,
    default: () => ({
      name: 'Holiday Promotions',
      type: 'playlist',
    }),
  },
  availableDevices: {
    type: Array,
    default: () => [],
  },
});

const emit = defineEmits(['close', 'submit']);

const daysOfWeek = ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'];
const repeatOptions = ['Everyday', 'Weekdays Only', 'Weekends Only', 'Custom', 'Custom Date'];
const contentOptions = [
  {
    id: 1,
    name: 'Holiday Promotions',
    type: 'playlist',
    description: '4k promotional loop for seasonal sales.',
  },
  {
    id: 2,
    name: 'Menu Highlights',
    type: 'image',
    description: 'Dynamic lunch menu boards with pricing.',
  },
  {
    id: 3,
    name: 'Welcome Message',
    type: 'video',
    description: 'Short greeting for new visitors.',
  },
];

const form = reactive({
  name: '',
  description: '',
  content: { ...props.defaultContent },
  devices: [],
  startTime: '09:00',
  endTime: '17:00',
  repeat: 'Weekdays Only',
  days: ['Mon', 'Tue', 'Wed', 'Thu', 'Fri'],
  customDates: [],
});

const showDeviceDropdown = ref(false);
const deviceDropdownRef = ref(null);
const showContentDropdown = ref(false);
const contentDropdownRef = ref(null);
const contentSearchQuery = ref('');
const deviceSearchQuery = ref('');

const selectedDevicesLabel = computed(() => {
  if (form.devices.length === 0) return 'Select devices';
  if (form.devices.length <= 2) return form.devices.join(', ');
  return `${form.devices.length} devices selected`;
});

const filteredContentOptions = computed(() => {
  if (!contentSearchQuery.value.trim()) {
    return contentOptions;
  }
  const query = contentSearchQuery.value.toLowerCase();
  return contentOptions.filter((item) => {
    return (
      item.name.toLowerCase().includes(query) ||
      item.type.toLowerCase().includes(query) ||
      item.description.toLowerCase().includes(query)
    );
  });
});

const filteredDevices = computed(() => {
  if (!deviceSearchQuery.value.trim()) {
    return props.availableDevices;
  }
  const query = deviceSearchQuery.value.toLowerCase();
  return props.availableDevices.filter((device) =>
    device.toLowerCase().includes(query)
  );
});

const datePickerConfig = {
  type: 'string',
  mask: 'YYYY-MM-DD',
};

const minCustomDate = new Date();
minCustomDate.setHours(0, 0, 0, 0);

const toggleDay = (day) => {
  if (form.repeat === 'Custom Date') return;
  
  if (form.days.includes(day)) {
    form.days = form.days.filter((d) => d !== day);
  } else {
    form.days.push(day);
  }
  
  // Auto-select "Custom" when manually selecting days
  if (form.repeat !== 'Custom' && form.repeat !== 'Custom Date') {
    form.repeat = 'Custom';
  }
};

const handleRepeatChange = () => {
  switch (form.repeat) {
    case 'Everyday':
      form.days = [...daysOfWeek];
      form.customDates = [];
      break;
    case 'Weekdays Only':
      form.days = ['Mon', 'Tue', 'Wed', 'Thu', 'Fri'];
      form.customDates = [];
      break;
    case 'Weekends Only':
      form.days = ['Sat', 'Sun'];
      form.customDates = [];
      break;
    case 'Custom':
      // Keep current selection
      form.customDates = [];
      break;
    case 'Custom Date':
      // Clear days when switching to Custom Date
      form.days = [];
      break;
  }
};

const formatDate = (dateString) => {
  const date = new Date(dateString);
  const options = { year: 'numeric', month: 'short', day: 'numeric' };
  return date.toLocaleDateString('en-US', options);
};

const removeCustomDate = (date) => {
  form.customDates = form.customDates.filter((d) => d !== date);
};

const toggleContentDropdown = () => {
  showContentDropdown.value = !showContentDropdown.value;
  if (!showContentDropdown.value) {
    contentSearchQuery.value = '';
  }
};

const toggleDeviceDropdown = () => {
  showDeviceDropdown.value = !showDeviceDropdown.value;
  if (!showDeviceDropdown.value) {
    deviceSearchQuery.value = '';
  }
};

const selectContent = (item) => {
  form.content = { name: item.name, type: item.type };
  showContentDropdown.value = false;
  contentSearchQuery.value = '';
};

const handleClickOutside = (event) => {
  const clickedDevice = deviceDropdownRef.value && deviceDropdownRef.value.contains(event.target);
  const clickedContent = contentDropdownRef.value && contentDropdownRef.value.contains(event.target);

  if (!clickedDevice) {
    showDeviceDropdown.value = false;
    deviceSearchQuery.value = '';
  }

  if (!clickedContent) {
    showContentDropdown.value = false;
    contentSearchQuery.value = '';
  }
};

onMounted(() => {
  document.addEventListener('click', handleClickOutside);
});

onBeforeUnmount(() => {
  document.removeEventListener('click', handleClickOutside);
});

const handleSubmit = () => {
  emit('submit', { ...form });
};

const emitClose = () => emit('close');
</script>

<style scoped>
.modal-overlay {
  position: fixed;
  inset: 0;
  background: rgba(0, 0, 0, 0.6);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 1000;
  padding: 24px;
}

.modal-panel {
  width: min(560px, 100%);
  max-height: 60vh;
  background: var(--bg-primary);
  color: var(--text-primary, #fff);
  border-radius: 16px;
  display: flex;
  flex-direction: column;
  border: 1px solid var(--border-color, #333);
}

.modal-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 20px 24px;
  border-bottom: 1px solid var(--border-color, #333);
}

.modal-header h2 {
  margin: 0;
  font-size: 20px;
  font-weight: 600;
}

.icon-btn {
  background: transparent;
  border: none;
  color: var(--text-secondary, #aaa);
  cursor: pointer;
  display: inline-flex;
  padding: 4px;
}

.modal-body {
  padding: 24px;
  overflow-y: auto;
  display: flex;
  flex-direction: column;
  gap: 20px;
}

.modal-footer {
  display: flex;
  justify-content: space-between;
  gap: 12px;
  padding: 20px 24px;
  border-top: 1px solid var(--border-color, #333);
}

.modal-footer button {
  flex: 1;
  padding: 12px;
  border-radius: 10px;
  border: none;
  font-weight: 600;
  cursor: pointer;
}

.modal-footer .primary {
  background: var(--button-primary-bg, #2563eb);
  color: #fff;
}

.modal-footer .secondary {
  background: transparent;
  border: 1px solid var(--border-color, #333);
  color: var(--text-secondary, #ccc);
}

.form-section {
  display: flex;
  flex-direction: column;
  gap: 10px;
}

.label {
  font-size: 14px;
  color: var(--text-secondary, #bbb);
  font-weight: 500;
}

input,
textarea,
select {
  width: 100%;
  border-radius: 10px;
  border: 1px solid var(--border-color, #333);
  background: var(--bg-secondary);
  color: var(--text-primary, #fff);
  padding: 10px 12px;
  font-size: 14px;
}

textarea {
  resize: none;
}

.content-select {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 12px;
  border-radius: 12px;
  border: 1px solid var(--border-color, #333);
  background: var(--bg-secondary);
  color: var(--text-primary, #fff);
  cursor: default;
}

.content-icon {
  width: 24px;
  height: 24px;
  color: var(--button-primary-bg, #2563eb);
}

.content-info {
  flex: 1;
  margin: 0 12px;
  display: flex;
  align-items: center;
  gap: 8px;
}

.content-title {
  margin: 0;
  font-weight: 600;
}

.pill {
  font-size: 12px;
  text-transform: uppercase;
  border-radius: 999px;
  padding: 2px 8px;
  background: var(--text-primary);
  color: var(--text-primary-button, #bbb);
}

.chevron {
  width: 18px;
  height: 18px;
  color: var(--text-secondary, #bbb);
  transition: transform 0.2s ease;
}

.chevron.open {
  transform: rotate(180deg);
}

.device-select,
.content-select-wrapper {
  position: relative;
}
.content-dropdown {
  position: absolute;
  left: 0;
  right: 0;
  top: calc(100% + 8px);
  background: var(--bg-secondary);
  border: 1px solid var(--border-color, #333);
  border-radius: 12px;
  max-height: 240px;
  overflow-y: auto;
  box-shadow: 0 20px 40px rgba(0, 0, 0, 0.45);
  z-index: 25;
}

.dropdown-search {
  position: relative;
  padding: 12px;
  border-bottom: 1px solid rgba(255, 255, 255, 0.05);
  background: var(--bg-secondary);
}

.dropdown-search .search-icon {
  position: absolute;
  left: 20px;
  top: 50%;
  transform: translateY(-50%);
  width: 16px;
  height: 16px;
  color: var(--text-secondary, #bbb);
  pointer-events: none;
}

.dropdown-search .search-input {
  width: 100%;
  padding: 8px 12px 8px 36px;
  border: 1px solid var(--border-color, #333);
  border-radius: 8px;
  background: var(--bg-primary);
  color: var(--text-primary, #fff);
  font-size: 14px;
}

.dropdown-search .search-input:focus {
  outline: none;
  border-color: var(--button-primary-bg, #2563eb);
}

.dropdown-search .search-input::placeholder {
  color: var(--text-secondary, #bbb);
}

.no-results {
  padding: 16px;
  text-align: center;
  color: var(--text-secondary, #bbb);
  font-size: 14px;
}

.content-option {
  padding: 12px 16px;
  border-bottom: 1px solid rgba(255, 255, 255, 0.05);
  cursor: pointer;
}

.content-option:last-child {
  border-bottom: none;
}

.content-option:hover {
  background: rgba(255, 255, 255, 0.04);
}

.content-option-info {
  display: flex;
  align-items: center;
  gap: 8px;
  margin-bottom: 6px;
}

.content-option-title {
  font-weight: 600;
}

.content-option-description {
  margin: 0;
  font-size: 13px;
  color: var(--text-secondary, #bbb);
}


.time-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
  gap: 16px;
}

.input-icon {
  position: relative;
}

.input-icon__icon {
  position: absolute;
  right: 12px;
  top: 50%;
  transform: translateY(-50%);
  width: 16px;
  height: 16px;
  color: var(--text-secondary, #bbb);
}

.input-icon input {
  padding-right: 36px;
}

.select-field {
  position: relative;
  display: flex;
  flex-direction: column;
  gap: 8px;
}

.select-field select {
  appearance: none;
  cursor: pointer;
}

.select-field.multi select {
  appearance: auto;
  cursor: default;
  min-height: 120px;
  padding-right: 12px;
}

.select-icon {
  position: absolute;
  right: 12px;
  top: 50%;
  transform: translateY(-50%);
  width: 18px;
  height: 18px;
  color: var(--text-secondary, #bbb);
  pointer-events: none;
}

.day-grid {
  display: flex;
  flex-wrap: wrap;
  gap: 8px;
}

.field-hint {
  margin: 0;
  font-size: 12px;
  color: var(--text-secondary, #bbb);
}

.device-dropdown {
  position: absolute;
  left: 0;
  right: 0;
  top: calc(100% + 8px);
  background: var(--bg-secondary);
  border: 1px solid var(--border-color, #333);
  border-radius: 12px;
  max-height: 220px;
  overflow-y: auto;
  box-shadow: 0 20px 40px rgba(0, 0, 0, 0.45);
  padding: 8px;
  z-index: 20;
}

.device-option {
  display: flex;
  align-items: center;
  gap: 10px;
  padding: 8px 10px;
  border-radius: 8px;
  cursor: pointer;
  color: var(--text-primary, #fff);
}

.device-option:hover {
  background: rgba(255, 255, 255, 0.04);
}

.device-option input {
  width: 16px;
  height: 16px;
}

.day-btn {
  border-radius: 999px;
  border: 1px solid var(--border-color, #333);
  background: transparent;
  color: var(--text-secondary, #bbb);
  padding: 6px 12px;
  cursor: pointer;
  transition: all 0.2s ease;
}

.day-btn.active {
  background: rgba(59, 130, 246, 0.2);
  color: var(--text-primary);
  border-color: rgba(59, 130, 246, 0.6);
}

.day-btn.disabled {
  opacity: 0.4;
  cursor: not-allowed;
  pointer-events: none;
}

.date-picker-wrapper {
  display: flex;
  flex-direction: column;
  gap: 10px;
}

.selected-dates {
  display: flex;
  flex-wrap: wrap;
  gap: 8px;
  margin-top: 8px;
}

.date-chip {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  padding: 6px 10px;
  border-radius: 999px;
  background: rgba(59, 130, 246, 0.2);
  color: #fff;
  font-size: 13px;
}

.date-chip .chip-remove {
  background: transparent;
  border: none;
  padding: 0;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  color: inherit;
}

.date-chip .chip-icon {
  width: 12px;
  height: 12px;
}

@media (max-width: 600px) {
  .modal-panel {
    max-height: 95vh;
  }

  .modal-body {
    padding: 16px;
  }

  .modal-footer {
    flex-direction: column;
  }
}
</style>


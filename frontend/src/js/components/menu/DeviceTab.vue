<template>
  <div class="tab-content device-tab">
    <!-- Header Section -->
    <div class="header-section">
      <div class="header-content">
        <h1 class="page-title">Devices</h1>
        <p class="page-subtitle">Manage your digital signage devices</p>
      </div>
      <button class="add-device-button" @click="addDevice">
        <Plus :size="16" class="plus-icon" />
        <span>Add Device</span>
      </button>
    </div>

    <!-- Summary Cards -->
    <div class="summary-cards">
      <div class="summary-card">
        <div class="summary-icon blue">
          <Monitor :size="24" />
        </div>
        <div class="summary-content">
          <div class="summary-label">Total Devices</div>
          <div class="summary-value">{{ totalDevices }}</div>
        </div>
      </div>

      <div class="summary-card">
        <div class="summary-icon green">
          <Wifi :size="24" />
        </div>
        <div class="summary-content">
          <div class="summary-label">Online</div>
          <div class="summary-value">{{ onlineDevices }}</div>
        </div>
      </div>

      <div class="summary-card">
        <div class="summary-icon red">
          <WifiOff :size="24" />
        </div>
        <div class="summary-content">
          <div class="summary-label">Offline</div>
          <div class="summary-value">{{ offlineDevices }}</div>
        </div>
      </div>
    </div>

    <!-- Filters Section -->
    <div class="filters-section">
      <div class="search-container">
        <Search :size="18" class="search-icon" />
        <input
          type="text"
          v-model="searchQuery"
          placeholder="Search devices..."
          class="search-input"
        />
      </div>
      <select v-model="statusFilter" class="status-filter">
        <option value="all">All Status</option>
        <option :value="1">Online</option>
        <option :value="2">Offline</option>
        <option :value="3">Syncing</option>
        <option :value="4">Error</option>
        <option :value="5">Pending</option>
      </select>
    </div>

    <!-- Loading State -->
    <div v-if="isLoadingDevices" class="loading-state">
      <div class="loading-spinner"></div>
      <p>Loading devices...</p>
    </div>

    <!-- Error State -->
    <div v-else-if="error && devices.length === 0" class="error-state">
      <p class="error-message">{{ error }}</p>
      <button class="retry-button" @click="fetchDevices">Retry</button>
    </div>

    <!-- Device Cards Grid -->
    <div v-else class="devices-grid">
      <div
        v-for="device in filteredDevices"
        :key="device.id"
        class="device-card"
      >
        <div class="device-card-header">
          <div class="device-title-section">
            <Monitor :size="18" class="device-title-icon" />
            <h3 class="device-title">{{ device.name }}</h3>
          </div>
        </div>

        <div class="device-card-body">
          <div class="device-info-row">
            <span class="device-info-label">Location:</span>
            <span class="device-info-value">{{ device.location }}</span>
          </div>

          <div class="device-status-row">
            <span
              :class="[
                'status-badge',
                getStatusClass(device.status)
              ]"
            >
              {{ getStatusLabel(device.status) }}
            </span>
          </div>

          <div class="device-info-row">
            <Monitor :size="14" class="info-icon" />
            <span class="device-info-label">Device:</span>
            <span class="device-info-value">{{ device.deviceId }}</span>
          </div>

          <div class="device-info-row">
            <Clock :size="14" class="info-icon" />
            <span class="device-info-label">Last synced:</span>
            <span class="device-info-value">{{ device.lastSynced }}</span>
          </div>

          <div class="device-info-row">
            <Play :size="14" class="info-icon" />
            <span class="device-info-label">Playing:</span>
            <span class="device-info-value">{{ device.playing }}</span>
          </div>
        </div>

        <div class="device-card-footer">
          <div class="footer-buttons">
            <button class="manage-button" @click="manageDevice(device.id)">
              <Settings :size="14" class="manage-icon" />
              <span>Manage</span>
            </button>
            <button class="delete-button" @click="confirmDeleteDevice(device)" title="Delete device">
              <Trash2 :size="14" class="delete-icon" />
            </button>
          </div>
          <label class="toggle-switch">
            <input
              type="checkbox"
              v-model="device.enabled"
              @change="toggleDevice(device.id, device.enabled)"
            />
            <span class="toggle-slider"></span>
          </label>
        </div>
      </div>

      <!-- Empty State -->
      <div v-if="!isLoadingDevices && filteredDevices.length === 0" class="empty-state">
        <Monitor :size="48" class="empty-icon" />
        <p class="empty-message">
          {{ devices.length === 0 ? 'No devices found. Add your first device to get started.' : 'No devices match your filters.' }}
        </p>
        <button v-if="devices.length === 0" class="add-first-device-button" @click="addDevice">
          <Plus :size="16" />
          <span>Add Device</span>
        </button>
      </div>
    </div>

    <!-- Add Device Modal -->
    <DeviceCreateModal
      :open="showAddDeviceModal"
      :isLoading="isLoading"
      :error="error"
      @close="closeAddDeviceModal"
      @submit="handleAddDevice"
    />

    <!-- Device Details Modal -->
    <div v-if="selectedDevice" class="modal-overlay" @click="closeModal">
      <div class="modal-content" @click.stop>
        <div class="modal-header">
          <h2 class="modal-title">{{ selectedDevice.name }} - Device Details</h2>
          <button class="modal-close-button" @click="closeModal">
            <X :size="20" />
          </button>
        </div>

        <div class="modal-body">
          <!-- Left Section: Device Control -->
          <div class="modal-left-section">
            <div class="device-preview-panel">
              <Monitor :size="64" class="preview-monitor-icon" />
              <div class="playing-text">Playing: {{ selectedDevice.playing }}</div>
            </div>

            <button class="sync-device-button" @click="syncDevice">
              Sync Device
            </button>

            <div class="device-power-control">
              <span class="power-label">Device Power</span>
              <label class="toggle-switch">
                <input
                  type="checkbox"
                  v-model="selectedDevice.powerOn"
                  @change="togglePower(selectedDevice.id, selectedDevice.powerOn)"
                />
                <span class="toggle-slider"></span>
              </label>
            </div>
          </div>

          <!-- Right Section: Device Information -->
          <div class="modal-right-section">
            <h3 class="info-section-title">Device Information</h3>
            <div class="info-list">
              <div class="info-item">
                <span class="info-label">Device Name:</span>
                <span class="info-value">{{ selectedDevice.deviceId }}</span>
              </div>
              <div class="info-item">
                <span class="info-label">Status:</span>
                <span
                  :class="[
                    'status-badge',
                    getStatusClass(selectedDevice.status)
                  ]"
                >
                  {{ getStatusLabel(selectedDevice.status) }}
                </span>
              </div>
              <div class="info-item">
                <span class="info-label">Total Runtime:</span>
                <span class="info-value">{{ selectedDevice.totalRuntime || '12.5h' }}</span>
              </div>
              <div class="info-item">
                <span class="info-label">Last Synced:</span>
                <span class="info-value">{{ selectedDevice.lastSynced }}</span>
              </div>
              <div class="info-item">
                <span class="info-label">Location:</span>
                <span class="info-value">{{ selectedDevice.location }}</span>
              </div>
              <div class="info-item">
                <span class="info-label">Time Zone:</span>
                <span class="info-value">{{ selectedDevice.timeZone || 'EST' }}</span>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { ref, computed, onMounted } from 'vue';
import {
  Plus,
  Monitor,
  Wifi,
  WifiOff,
  Search,
  Clock,
  Play,
  Settings,
  X,
  Trash2,
} from 'lucide-vue-next';
import DeviceCreateModal from './modals/DeviceCreateModal.vue';
import { deviceService } from '@/api/deviceService';

export default {
  name: 'DeviceTab',
  components: {
    Plus,
    Monitor,
    Wifi,
    WifiOff,
    Search,
    Clock,
    Play,
    Settings,
    X,
    Trash2,
    DeviceCreateModal,
  },
  setup() {
    const searchQuery = ref('');
    const statusFilter = ref('all');
    const selectedDevice = ref(null);
    const showAddDeviceModal = ref(false);
    const isLoading = ref(false);
    const isLoadingDevices = ref(false);
    const error = ref(null);

    // Status mapping: 1=online, 2=offline, 3=syncing, 4=error, 5=pending
    const getStatusLabel = (status) => {
      const statusMap = {
        1: 'online',
        2: 'offline',
        3: 'syncing',
        4: 'error',
        5: 'pending',
      };
      return statusMap[status] || 'unknown';
    };

    const getStatusClass = (status) => {
      if (status === 1) return 'status-online';
      if (status === 2) return 'status-offline';
      if (status === 3) return 'status-syncing';
      if (status === 4) return 'status-error';
      if (status === 5) return 'status-pending';
      return 'status-offline';
    };

    // Devices from API
    const devices = ref([]);

    /**
     * Map API device data to UI format
     */
    const mapDeviceToUI = (device) => {
      return {
        id: String(device.id),
        name: device.name,
        location: device.location || 'Not specified',
        status: device.status,
        deviceId: device.device_uid,
        device_uid: device.device_uid,
        ip_address: device.ip_address,
        firmware_version: device.firmware_version,
        canvas_width: device.canvas_width,
        canvas_height: device.canvas_height,
        lastSynced: device.last_seen_at
          ? formatLastSeen(device.last_seen_at)
          : 'Never',
        playing: 'No content', // TODO: Get from schedule/playlist
        enabled: device.status === 1,
        powerOn: device.status === 1,
        totalRuntime: '0h', // TODO: Calculate from analytics
        timeZone: 'EST', // TODO: Get from device settings
      };
    };

    /**
     * Format last_seen_at timestamp to relative time
     */
    const formatLastSeen = (timestamp) => {
      if (!timestamp) return 'Never';
      
      const now = new Date();
      const lastSeen = new Date(timestamp);
      const diffMs = now - lastSeen;
      const diffMins = Math.floor(diffMs / 60000);
      const diffHours = Math.floor(diffMs / 3600000);
      const diffDays = Math.floor(diffMs / 86400000);

      if (diffMins < 1) return 'Just now';
      if (diffMins < 60) return `${diffMins} ${diffMins === 1 ? 'min' : 'mins'} ago`;
      if (diffHours < 24) return `${diffHours} ${diffHours === 1 ? 'hour' : 'hours'} ago`;
      return `${diffDays} ${diffDays === 1 ? 'day' : 'days'} ago`;
    };

    /**
     * Fetch devices from API
     */
    const fetchDevices = async () => {
      isLoadingDevices.value = true;
      error.value = null;

      try {
        const response = await deviceService.getAll();
        const devicesData = response.data?.data || response.data || [];
        
        // Map API data to UI format
        devices.value = devicesData.map(mapDeviceToUI);
      } catch (err) {
        console.error('Error fetching devices:', err);
        error.value = err.response?.data?.message || err.message || 'Failed to fetch devices';
        devices.value = []; // Reset to empty array on error
      } finally {
        isLoadingDevices.value = false;
      }
    };

    const totalDevices = computed(() => devices.value.length);
    const onlineDevices = computed(() =>
      devices.value.filter((d) => d.status === 1).length
    );
    const offlineDevices = computed(() =>
      devices.value.filter((d) => d.status === 2).length
    );

    const filteredDevices = computed(() => {
      let filtered = devices.value;

      // Filter by search query
      if (searchQuery.value) {
        const query = searchQuery.value.toLowerCase();
        filtered = filtered.filter(
          (device) =>
            device.name.toLowerCase().includes(query) ||
            device.location.toLowerCase().includes(query) ||
            device.deviceId.toLowerCase().includes(query)
        );
      }

      // Filter by status
      if (statusFilter.value !== 'all') {
        filtered = filtered.filter(
          (device) => device.status === Number(statusFilter.value)
        );
      }

      return filtered;
    });

    const addDevice = () => {
      showAddDeviceModal.value = true;
    };

    const closeAddDeviceModal = () => {
      showAddDeviceModal.value = false;
      error.value = null; // Clear error when closing modal
    };

    const handleAddDevice = async (deviceData) => {
      isLoading.value = true;
      error.value = null;

      try {
        const apiData = {
          device_uid: deviceData.device_uid,
          name: deviceData.name,
          location: deviceData.location || null,
          status: deviceData.status,
          ip_address: deviceData.ip_address || null,
          firmware_version: deviceData.firmware_version || null,
          canvas_width: deviceData.canvas_width,
          canvas_height: deviceData.canvas_height,
        };

        const response = await deviceService.create(apiData);
        const device = response.data.data || null;
        
        devices.value.push(device);
        
        closeAddDeviceModal();
        error.value = null;
      } catch (err) {
        console.error('Error creating device:', err);
        error.value = err.response?.data?.message || err.response?.data?.error || err.message || 'Failed to create device';
      } finally {
        isLoading.value = false;
      }
    };

    const manageDevice = (id) => {
      const device = devices.value.find((d) => d.id === id);
      if (device) {
        selectedDevice.value = { ...device };
      }
    };

    const closeModal = () => {
      selectedDevice.value = null;
    };

    const syncDevice = () => {
      if (selectedDevice.value) {
        console.log('Sync device:', selectedDevice.value.id);
        // Placeholder for future API call
        // Update last synced time
        selectedDevice.value.lastSynced = 'Just now';
        const device = devices.value.find(
          (d) => d.id === selectedDevice.value.id
        );
        if (device) {
          device.lastSynced = 'Just now';
        }
      }
    };

    const togglePower = (id, powerOn) => {
      console.log('Toggle power:', id, powerOn);
      // Placeholder for future API call
      const device = devices.value.find((d) => d.id === id);
      if (device) {
        device.powerOn = powerOn;
      }
    };

    const toggleDevice = (id, enabled) => {
      console.log('Toggle device:', id, enabled);
      // Placeholder for future API call
    };

    const confirmDeleteDevice = (device) => {
      if (confirm(`Are you sure you want to delete "${device.name}"? This action cannot be undone.`)) {
        deleteDevice(device.id);
      }
    };

    const deleteDevice = async (deviceId) => {
      isLoading.value = true;
      error.value = null;

      try {
        await deviceService.delete(deviceId);
        
        devices.value = devices.value.filter((d) => d.id !== deviceId);
        
        closeModal();
      } catch (err) {
        console.error('Error deleting device:', err);
        error.value = err.response?.data?.message || err.response?.data?.error || err.message || 'Failed to delete device';
      } finally {
        isLoading.value = false;
      }
    };

    // Fetch devices when component mounts
    onMounted(() => {
      fetchDevices();
    });

    return {
      searchQuery,
      statusFilter,
      devices,
      selectedDevice,
      showAddDeviceModal,
      isLoading,
      isLoadingDevices,
      error,
      totalDevices,
      onlineDevices,
      offlineDevices,
      filteredDevices,
      getStatusLabel,
      getStatusClass,
      addDevice,
      closeAddDeviceModal,
      handleAddDevice,
      manageDevice,
      closeModal,
      syncDevice,
      togglePower,
      toggleDevice,
      confirmDeleteDevice,
      deleteDevice,
      fetchDevices,
    };
  },
};
</script>

<style scoped>
.tab-content {
  color: var(--text-primary);
}

.device-tab {
  display: flex;
  flex-direction: column;
  gap: 24px;
}

/* Header Section */
.header-section {
  display: flex;
  align-items: flex-start;
  justify-content: space-between;
  margin-bottom: 8px;
}

.header-content {
  flex: 1;
}

.page-title {
  color: var(--text-primary);
  font-size: 28px;
  font-weight: 600;
  margin: 0 0 4px 0;
}

.page-subtitle {
  color: var(--text-secondary);
  font-size: 14px;
  margin: 0;
}

.add-device-button {
  display: flex;
  align-items: center;
  gap: 8px;
  padding: 10px 16px;
  border-radius: 6px;
  font-size: 14px;
  font-weight: 500;
  background: var(--button-primary-bg);
  border: none;
  color: var(--color-white);
  cursor: pointer;
  transition: background-color 0.2s;
  white-space: nowrap;
}

.add-device-button:hover {
  background: var(--button-primary-hover);
}

.plus-icon {
  flex-shrink: 0;
}

/* Summary Cards */
.summary-cards {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 20px;
}

.summary-card {
  background: var(--bg-card);
  border: 1px solid var(--border-color);
  border-radius: 8px;
  padding: 20px;
  display: flex;
  align-items: center;
  gap: 16px;
  transition: all 0.2s;
}

.summary-card:hover {
  border-color: var(--border-hover);
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
}

.summary-icon {
  width: 56px;
  height: 56px;
  border-radius: 8px;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
}

.summary-icon.blue {
  background: rgba(59, 130, 246, 0.1);
  color: var(--color-blue);
}

.summary-icon.green {
  background: rgba(34, 197, 94, 0.1);
  color: var(--color-green);
}

.summary-icon.red {
  background: rgba(239, 68, 68, 0.1);
  color: var(--color-red);
}

.summary-content {
  flex: 1;
  min-width: 0;
}

.summary-label {
  color: var(--text-secondary);
  font-size: 13px;
  margin-bottom: 4px;
}

.summary-value {
  color: var(--text-primary);
  font-size: 24px;
  font-weight: 600;
  line-height: 1;
}

/* Filters Section */
.filters-section {
  display: flex;
  align-items: center;
  gap: 16px;
}

.search-container {
  position: relative;
  flex: 1;
  max-width: 400px;
}

.search-icon {
  position: absolute;
  left: 12px;
  top: 50%;
  transform: translateY(-50%);
  color: var(--text-secondary);
  pointer-events: none;
}

.search-input {
  width: 100%;
  padding: 10px 12px 10px 40px;
  background: var(--bg-card);
  border: 1px solid var(--border-color);
  border-radius: 6px;
  color: var(--text-primary);
  font-size: 14px;
  transition: border-color 0.2s;
}

.search-input:focus {
  outline: none;
  border-color: var(--border-hover);
}

.search-input::placeholder {
  color: var(--color-gray);
}

.status-filter {
  padding: 10px 12px;
  background: var(--bg-card);
  border: 1px solid var(--border-color);
  border-radius: 6px;
  color: var(--text-primary);
  font-size: 14px;
  cursor: pointer;
  transition: border-color 0.2s;
  min-width: 140px;
}

.status-filter:focus {
  outline: none;
  border-color: var(--border-hover);
}

.status-filter option {
  background: var(--bg-card);
  color: var(--text-primary);
}

/* Devices Grid */
.devices-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
  gap: 20px;
}

.device-card {
  background: var(--bg-card);
  border: 1px solid var(--border-color);
  border-radius: 8px;
  padding: 20px;
  display: flex;
  flex-direction: column;
  gap: 16px;
  transition: all 0.2s;
}

.device-card:hover {
  border-color: var(--border-hover);
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
}

.device-card-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
}

.device-title-section {
  display: flex;
  align-items: center;
  gap: 8px;
}

.device-title-icon {
  color: var(--text-secondary);
  flex-shrink: 0;
}

.device-title {
  color: var(--text-primary);
  font-size: 16px;
  font-weight: 600;
  margin: 0;
}

.device-card-body {
  display: flex;
  flex-direction: column;
  gap: 12px;
}

.device-info-row {
  display: flex;
  align-items: center;
  gap: 8px;
  font-size: 13px;
}

.info-icon {
  color: var(--text-secondary);
  flex-shrink: 0;
}

.device-info-label {
  color: var(--text-secondary);
}

.device-info-value {
  color: var(--text-primary);
  margin-left: auto;
}

.device-status-row {
  margin: 4px 0;
}

.status-badge {
  display: inline-block;
  padding: 4px 12px;
  border-radius: 9999px;
  font-size: 12px;
  font-weight: 500;
  text-transform: lowercase;
}

.status-online {
  background-color: var(--status-online);
  color: var(--color-white);
}

.status-offline {
  background-color: var(--status-offline);
  color: var(--color-white);
}

.status-syncing {
  background-color: var(--color-blue, #3b82f6);
  color: var(--color-white);
}

.status-error {
  background-color: var(--color-red, #ef4444);
  color: var(--color-white);
}

.status-pending {
  background-color: var(--color-gray, #6b7280);
  color: var(--color-white);
}

.device-card-footer {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding-top: 12px;
  border-top: 1px solid var(--border-subtle);
}

.footer-buttons {
  display: flex;
  align-items: center;
  gap: 8px;
}

.manage-button {
  display: flex;
  align-items: center;
  gap: 6px;
  padding: 8px 16px;
  background: var(--button-secondary-bg);
  border: none;
  border-radius: 6px;
  color: var(--color-white);
  font-size: 13px;
  font-weight: 500;
  cursor: pointer;
  transition: background-color 0.2s;
}

.manage-button:hover {
  background: var(--button-secondary-hover);
}

.manage-icon {
  flex-shrink: 0;
}

.delete-button {
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 8px;
  background: transparent;
  border: 1px solid var(--border-color);
  border-radius: 6px;
  color: var(--color-red, #ef4444);
  cursor: pointer;
  transition: all 0.2s;
  min-width: 36px;
  height: 36px;
}

.delete-button:hover {
  background: rgba(239, 68, 68, 0.1);
  border-color: var(--color-red, #ef4444);
}

.delete-icon {
  flex-shrink: 0;
}

/* Toggle Switch */
.toggle-switch {
  position: relative;
  display: inline-block;
  width: 44px;
  height: 24px;
  cursor: pointer;
}

.toggle-switch input {
  opacity: 0;
  width: 0;
  height: 0;
}

.toggle-slider {
  position: absolute;
  cursor: pointer;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background-color: var(--border-hover);
  transition: 0.3s;
  border-radius: 24px;
}

.toggle-slider:before {
  position: absolute;
  content: '';
  height: 18px;
  width: 18px;
  left: 3px;
  bottom: 3px;
  background-color: var(--text-primary);
  transition: 0.3s;
  border-radius: 50%;
}

.toggle-switch input:checked + .toggle-slider {
  background-color: var(--status-online);
}

.toggle-switch input:checked + .toggle-slider:before {
  transform: translateX(20px);
}

/* Responsive */
@media (max-width: 1200px) {
  .summary-cards {
    grid-template-columns: repeat(3, 1fr);
  }

  .devices-grid {
    grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
  }
}

@media (max-width: 768px) {
  .header-section {
    flex-direction: column;
    gap: 16px;
  }

  .add-device-button {
    width: 100%;
    justify-content: center;
  }

  .summary-cards {
    grid-template-columns: 1fr;
  }

  .filters-section {
    flex-direction: column;
    align-items: stretch;
  }

  .search-container {
    max-width: 100%;
  }

  .devices-grid {
    grid-template-columns: 1fr;
  }
}

/* Modal Styles */
.modal-overlay {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(0, 0, 0, 0.75);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 1000;
  padding: 20px;
}

.modal-content {
  background: var(--bg-card);
  border: 1px solid var(--border-color);
  border-radius: 12px;
  width: 100%;
  max-width: 900px;
  max-height: 90vh;
  overflow-y: auto;
  display: flex;
  flex-direction: column;
}

.modal-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 20px 24px;
  border-bottom: 1px solid var(--border-color);
}

.modal-title {
  color: var(--text-primary);
  font-size: 20px;
  font-weight: 600;
  margin: 0;
}

.modal-close-button {
  background: transparent;
  border: none;
  color: var(--text-secondary);
  cursor: pointer;
  padding: 4px;
  display: flex;
  align-items: center;
  justify-content: center;
  border-radius: 4px;
  transition: all 0.2s;
}

.modal-close-button:hover {
  background: var(--bg-hover);
  color: var(--text-primary);
}

.modal-body {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 24px;
  padding: 24px;
}

.modal-left-section {
  display: flex;
  flex-direction: column;
  gap: 20px;
}

.device-preview-panel {
  background: var(--bg-tertiary);
  border: 1px solid var(--border-color);
  border-radius: 8px;
  padding: 40px 20px;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  gap: 16px;
  min-height: 200px;
}

.preview-monitor-icon {
  color: var(--text-secondary);
}

.playing-text {
  color: var(--text-secondary);
  font-size: 14px;
  text-align: center;
}

.sync-device-button {
  width: 100%;
  padding: 12px 20px;
  background: var(--button-primary-bg);
  border: none;
  border-radius: 8px;
  color: var(--color-white);
  font-size: 14px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.2s;
}

.sync-device-button:hover {
  background: var(--button-primary-hover);
  transform: translateY(-1px);
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.2);
}

.device-power-control {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 16px 0;
}

.power-label {
  color: var(--text-primary);
  font-size: 14px;
  font-weight: 500;
}

.modal-right-section {
  display: flex;
  flex-direction: column;
  gap: 20px;
}

.info-section-title {
  color: var(--text-primary);
  font-size: 18px;
  font-weight: 600;
  margin: 0;
}

.info-list {
  display: flex;
  flex-direction: column;
  gap: 16px;
}

.info-item {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding-bottom: 12px;
  border-bottom: 1px solid var(--border-subtle);
}

.info-item:last-child {
  border-bottom: none;
  padding-bottom: 0;
}

.info-label {
  color: var(--text-secondary);
  font-size: 14px;
}

.info-value {
  color: var(--text-primary);
  font-size: 14px;
  font-weight: 500;
}

@media (max-width: 768px) {
  .modal-body {
    grid-template-columns: 1fr;
  }

  .modal-overlay {
    padding: 10px;
  }
}

/* Loading State */
.loading-state {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  padding: 60px 20px;
  gap: 16px;
}

.loading-spinner {
  width: 40px;
  height: 40px;
  border: 4px solid var(--border-color);
  border-top-color: var(--button-primary-bg);
  border-radius: 50%;
  animation: spin 1s linear infinite;
}

@keyframes spin {
  to {
    transform: rotate(360deg);
  }
}

.loading-state p {
  color: var(--text-secondary);
  font-size: 14px;
  margin: 0;
}

/* Error State */
.error-state {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  padding: 60px 20px;
  gap: 16px;
}

.error-message {
  color: var(--color-red, #ef4444);
  font-size: 14px;
  text-align: center;
  margin: 0;
}

.retry-button {
  padding: 10px 20px;
  background: var(--button-primary-bg);
  color: var(--color-white);
  border: none;
  border-radius: 6px;
  font-size: 14px;
  font-weight: 500;
  cursor: pointer;
  transition: background-color 0.2s;
}

.retry-button:hover {
  background: var(--button-primary-hover);
}

/* Empty State */
.empty-state {
  grid-column: 1 / -1;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  padding: 60px 20px;
  gap: 16px;
  text-align: center;
}

.empty-icon {
  color: var(--text-secondary);
  opacity: 0.5;
}

.empty-message {
  color: var(--text-secondary);
  font-size: 14px;
  margin: 0;
}

.add-first-device-button {
  display: flex;
  align-items: center;
  gap: 8px;
  padding: 10px 20px;
  background: var(--button-primary-bg);
  color: var(--color-white);
  border: none;
  border-radius: 6px;
  font-size: 14px;
  font-weight: 500;
  cursor: pointer;
  transition: background-color 0.2s;
  margin-top: 8px;
}

.add-first-device-button:hover {
  background: var(--button-primary-hover);
}
</style>


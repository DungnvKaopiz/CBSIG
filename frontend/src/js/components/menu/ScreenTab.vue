<template>
  <div class="tab-content screen-tab">
    <!-- Stats + Actions -->
    <div class="stats-actions">
      <div class="stats">
        <div class="stat-card">
          <div class="stat-icon-wrapper">
            <svg class="monitor-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <rect x="2" y="3" width="20" height="14" rx="2"/>
              <path d="M8 21h8M12 17v4"/>
            </svg>
          </div>
          <div class="stat-text">
            <div class="stat-title">Total</div>
            <div class="stat-value">{{ total }}</div>
          </div>
        </div>
        <span class="operator">=</span>

        <div class="stat-card">
          <div class="stat-icon-wrapper">
            <svg class="monitor-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <rect x="2" y="3" width="20" height="14" rx="2"/>
              <path d="M8 21h8M12 17v4"/>
            </svg>
            <span class="status-dot dot-online"></span>
          </div>
          <div class="stat-text">
            <div class="stat-title">Online</div>
            <div class="stat-value">{{ onlineCount }}</div>
          </div>
        </div>
        <span class="operator">+</span>

        <div class="stat-card">
          <div class="stat-icon-wrapper">
            <svg class="monitor-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <rect x="2" y="3" width="20" height="14" rx="2"/>
              <path d="M8 21h8M12 17v4"/>
            </svg>
            <span class="status-dot dot-not-logged">
              <svg class="clock-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <circle cx="12" cy="12" r="10"/>
                <path d="M12 6v6l4 2"/>
              </svg>
            </span>
          </div>
          <div class="stat-text">
            <div class="stat-title">Not Logged</div>
            <div class="stat-value">{{ notLoggedCount }}</div>
          </div>
        </div>
        <span class="operator">+</span>

        <div class="stat-card">
          <div class="stat-icon-wrapper">
            <svg class="monitor-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <rect x="2" y="3" width="20" height="14" rx="2"/>
              <path d="M8 21h8M12 17v4"/>
            </svg>
            <span class="status-dot dot-offline">
              <span class="minus-icon">−</span>
            </span>
          </div>
          <div class="stat-text">
            <div class="stat-title">Offline</div>
            <div class="stat-value">{{ offlineCount }}</div>
          </div>
        </div>
      </div>

      <div class="actions">
        <input
          v-model="searchQuery"
          class="search-input"
          type="text"
          placeholder="Screen Name"
        />
      </div>
    </div>

    <!-- Table -->
    <div class="table-container">
      <div class="table">
        <div class="table-header">
          <div class="th name">Screen Name</div>
          <div class="th ip">Screen IP</div>
          <div class="th size">Screen Size</div>
          <div class="th refresh-col">
            <button class="refresh-button" @click="refresh">
              <svg class="refresh-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M21.5 2v6h-6M2.5 22v-6h6M2 11.5a10 10 0 0118.8-4.3M22 12.5a10 10 0 01-18.8 4.2"/>
              </svg>
              <span>Refresh</span>
            </button>
          </div>
        </div>
        <div
          v-for="screen in filteredScreens"
          :key="screen.id"
          :class="['table-row', { selected: selectedId === screen.id }]"
          @click="select(screen.id)"
        >
          <div class="td name">
            <span class="status">
              <span :class="['dot', screen.status ? 'dot-online' : 'dot-offline']"></span>
            </span>
            <span class="name-text">{{ screen.name }}</span>
          </div>
          <div class="td ip">{{ screen.ip }}</div>
          <div class="td size">{{ screen.size }}</div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { ref, computed } from 'vue';

export default {
  name: 'ScreenTab',
  setup() {
    const searchQuery = ref('');
    const selectedId = ref(null);

    // Mock data to resemble screenshot
    const screens = ref([
      { id: '1', name: 'test-1', ip: '169.254.43.106', size: '400*400', status: true, logged: true },
      { id: '2', name: 'test-2', ip: '169.254.43.107', size: '400*400', status: true, logged: false },
      { id: '3', name: 'test-3', ip: '169.254.43.108', size: '400*400', status: false, logged: false },
      // More rows can be added here
    ]);

    const onlineCount = computed(() => screens.value.filter(s => s.status && s.logged).length);
    const notLoggedCount = computed(() => screens.value.filter(s => s.status && !s.logged).length);
    const offlineCount = computed(() => screens.value.filter(s => !s.status).length);
    const total = computed(() => onlineCount.value + notLoggedCount.value + offlineCount.value);

    const filteredScreens = computed(() => {
      const q = searchQuery.value.trim().toLowerCase();
      if (!q) return screens.value;
      return screens.value.filter(s =>
        s.name.toLowerCase().includes(q) || s.ip.toLowerCase().includes(q)
      );
    });

    const select = (id) => {
      selectedId.value = id;
    };

    const refresh = () => {
      // Placeholder for future API call
      // For now, just log
      console.log('Refresh screens list');
    };

    return {
      searchQuery,
      selectedId,
      total,
      onlineCount,
      offlineCount,
      notLoggedCount,
      filteredScreens,
      select,
      refresh,
    };
  },
};
</script>

<style scoped>
.tab-content {
  color: #fff;
}

.screen-tab {
  display: flex;
  flex-direction: column;
  gap: 16px;
}

.stats-actions {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 16px;
  background-color: #1e1e1e;
  border: 1px solid #3d3d3d;
  border-radius: 6px;
  padding: 12px 16px;
  flex-wrap: wrap;
}

.stats {
  display: flex;
  align-items: center;
  gap: 12px;
  flex-wrap: wrap;
}

.operator {
  color: #fff;
  font-size: 16px;
  font-weight: 400;
  margin: 0 2px;
  line-height: 1;
}

.stat-card {
  display: flex;
  align-items: center;
  gap: 10px;
  color: #fff;
}

.stat-icon-wrapper {
  position: relative;
  width: 28px;
  height: 28px;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
}

.monitor-icon {
  width: 20px;
  height: 20px;
  color: #fff;
  stroke: currentColor;
  stroke-width: 1.5;
  fill: none;
}

.status-dot {
  position: absolute;
  bottom: -2px;
  left: -2px;
  width: 12px;
  height: 12px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  border: 1.5px solid #1e1e1e;
}

.status-dot.dot-online {
  background: #22c55e;
}

.status-dot.dot-not-logged {
  background: #f59e0b;
}

.status-dot.dot-offline {
  background: #6b7280;
}

.status-dot .clock-icon {
  width: 7px;
  height: 7px;
  color: #fff;
  stroke: currentColor;
  fill: none;
  stroke-width: 1.5;
}

.status-dot .minus-icon {
  color: #fff;
  font-size: 9px;
  font-weight: bold;
  line-height: 1;
  display: flex;
  align-items: center;
  justify-content: center;
}

.stat-text {
  display: flex;
  flex-direction: column;
  gap: 2px;
}

.stat-title {
  color: #fff;
  font-size: 12px;
  font-weight: 400;
}

.stat-value {
  color: #fff;
  font-weight: 600;
  font-size: 14px;
}

.actions {
  display: flex;
  align-items: center;
  gap: 10px;
  margin-left: auto;
}
.search-input {
  background: #2a2a2a;
  border: 1px solid #3d3d3d;
  color: #e5e7eb;
  padding: 8px 10px;
  border-radius: 6px;
  min-width: 220px;
}
.search-input::placeholder {
  color: #9ca3af;
}
.refresh-button {
  display: flex;
  align-items: center;
  gap: 6px;
  background: transparent;
  border: none;
  color: #3b82f6;
  cursor: pointer;
  font-size: 13px;
  font-weight: 500;
  padding: 4px 8px;
  transition: opacity 0.2s;
}

.refresh-button:hover {
  opacity: 0.8;
}

.refresh-icon {
  width: 16px;
  height: 16px;
  color: #3b82f6;
}

.table-container {
  background: #1e1e1e;
  border: 1px solid #3d3d3d;
  border-radius: 6px;
  overflow-x: auto;
  overflow-y: hidden;
  -webkit-overflow-scrolling: touch;
}
.table {
  width: 100%;
  min-width: 720px;
}
.table-header {
  display: grid;
  grid-template-columns: 1.2fr 1fr 0.8fr auto;
  color: #cbd5e1;
  background: #2a2a2a;
  border-bottom: 1px solid #3d3d3d;
  font-weight: 600;
}
.th {
  padding: 12px 16px;
  font-size: 13px;
}
.th.refresh-col {
  padding: 12px 16px;
  display: flex;
  justify-content: flex-end;
  align-items: center;
}
.table-row {
  display: grid;
  grid-template-columns: 1.2fr 1fr 0.8fr;
  color: #e5e7eb;
  border-bottom: 1px solid #2c2c2c;
  cursor: pointer;
}
.table-row:hover {
  background: #2a2a2a;
}
.table-row.selected {
  background: #2563eb;
  color: #fff;
}
.td {
  padding: 12px 16px;
  font-size: 13px;
  display: flex;
  align-items: center;
}

.td.name {
  gap: 8px;
}

.td.name .status {
  display: flex;
  align-items: center;
  flex-shrink: 0;
  background-color: transparent;
}

.td.name .dot {
  width: 10px;
  height: 10px;
  border-radius: 50%;
  display: inline-block;
}

.td.name .dot-online {
  background: #22c55e;
}

.td.name .dot-offline {
  background: #6b7280;
}

.td.name .name-text {
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
  flex: 1;
  background-color: transparent;
  color: #fff;
}

@media (max-width: 768px) {
  .table-header,
  .table-row {}
  .stats-actions {
    flex-direction: column;
    align-items: stretch;
    gap: 12px;
  }
  .stats {
    justify-content: space-between;
    gap: 10px 8px;
  }
  .stat-card {
    min-width: calc(50% - 8px);
  }
  .operator {
    display: none;
  }
  .actions {
    width: 100%;
    margin-left: 0;
    justify-content: stretch;
  }
  .search-input {
    width: 100%;
    min-width: 0;
  }
}
</style>


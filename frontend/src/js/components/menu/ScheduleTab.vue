<template>
  <div class="tab-content schedule-tab">
    <div class="schedule-layout">
      <!-- Left Sidebar -->
      <div class="sidebar">
        <div class="sidebar-search">
          <input
            v-model="scheduleSearchQuery"
            class="search-input"
            type="text"
            placeholder="Enter a schedule na..."
          />
          <svg class="search-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <circle cx="11" cy="11" r="8"/>
            <path d="m21 21-4.35-4.35"/>
          </svg>
        </div>

        <div class="schedule-list">
          <div
            v-for="schedule in filteredSchedules"
            :key="schedule.id"
            :class="['schedule-item', { active: selectedScheduleId === schedule.id }]"
            @click="selectSchedule(schedule.id)"
          >
            <svg class="screen-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <rect x="2" y="3" width="20" height="14" rx="2"/>
              <path d="M8 21h8M12 17v4"/>
            </svg>
            <div class="schedule-info">
              <div class="schedule-name">{{ schedule.name }}</div>
              <div class="schedule-resolution">{{ schedule.resolution }}</div>
            </div>
            <div class="schedule-actions">
              <button class="action-btn send-btn" @click.stop="sendSchedule(schedule.id)">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                  <path d="M22 2L11 13M22 2l-7 20-4-9-9-4 20-7z"/>
                </svg>
              </button>
              <button class="action-btn menu-btn" @click.stop="openMenu(schedule.id)">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                  <circle cx="12" cy="12" r="1"/>
                  <circle cx="12" cy="5" r="1"/>
                  <circle cx="12" cy="19" r="1"/>
                </svg>
              </button>
            </div>
          </div>
        </div>

        <div class="sidebar-footer">
          <button class="btn btn-primary" @click="newSchedule">
            + New Schedule
          </button>
          <div class="footer-row">
            <button class="btn btn-secondary" @click="importSchedule">
              Import
            </button>
            <button class="btn btn-secondary" @click="usbPlayback">
              USB playback
            </button>
          </div>
        </div>
      </div>

      <!-- Right Main Content -->
      <div class="main-content">
        <!-- Sub Navigation -->
        <div class="sub-nav">
          <button
            :class="['sub-nav-btn', { active: viewMode === 'list' }]"
            @click="viewMode = 'list'"
          >
            List View
          </button>
          <button
            :class="['sub-nav-btn', { active: viewMode === 'calendar' }]"
            @click="viewMode = 'calendar'"
          >
            Calendar View
          </button>
        </div>

        <!-- Action Buttons -->
        <div class="action-buttons">
          <button class="btn btn-primary" @click="addSolution">
            + Add Solution
          </button>
          <button class="btn btn-secondary" :disabled="selectedSolutions.length === 0" @click="removeSolutions">
            Remove
          </button>
        </div>

        <!-- Table Container -->
        <div class="table-section">
          <div class="table-header-row">
            <div class="table-header">
              <div class="th checkbox-col">
                <input
                  type="checkbox"
                  :checked="allSelected"
                  @change="toggleSelectAll"
                />
              </div>
              <div class="th name-col">
                Solution Name
                <svg class="sort-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                  <path d="M7 13l5 5 5-5M7 6l5-5 5 5"/>
                </svg>
              </div>
              <div class="th resolution-col">
                Resolution
                <svg class="sort-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                  <path d="M7 13l5 5 5-5M7 6l5-5 5 5"/>
                </svg>
              </div>
              <div class="th size-col">
                File Size
                <svg class="sort-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                  <path d="M7 13l5 5 5-5M7 6l5-5 5 5"/>
                </svg>
              </div>
              <div class="th properties-col">Properties</div>
              <div class="th modified-col">
                Last Modified
                <svg class="sort-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                  <path d="M7 13l5 5 5-5M7 6l5-5 5 5"/>
                </svg>
              </div>
              <div class="th operation-col">Operation</div>
            </div>
            <div class="table-search">
              <input
                v-model="solutionSearchQuery"
                class="search-input"
                type="text"
                placeholder="Solution Name"
              />
              <svg class="search-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <circle cx="11" cy="11" r="8"/>
                <path d="m21 21-4.35-4.35"/>
              </svg>
            </div>
          </div>

          <!-- Empty State -->
          <div v-if="filteredSolutions.length === 0" class="empty-state">
            <svg class="empty-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1">
              <rect x="2" y="3" width="20" height="14" rx="2"/>
              <path d="M8 21h8M12 17v4"/>
              <rect x="4" y="7" width="4" height="4" rx="1"/>
              <rect x="10" y="7" width="4" height="4" rx="1"/>
              <rect x="16" y="7" width="4" height="4" rx="1"/>
            </svg>
            <p class="empty-text">
              No solutions, please <a href="#" @click.prevent="addSolution" class="empty-link">Add Solution</a>
            </p>
          </div>

          <!-- Table Rows -->
          <div v-else class="table-body">
            <div
              v-for="solution in filteredSolutions"
              :key="solution.id"
              class="table-row"
            >
              <div class="td checkbox-col">
                <input
                  type="checkbox"
                  :checked="selectedSolutions.includes(solution.id)"
                  @change="toggleSolution(solution.id)"
                />
              </div>
              <div class="td name-col">{{ solution.name }}</div>
              <div class="td resolution-col">{{ solution.resolution }}</div>
              <div class="td size-col">{{ solution.fileSize }}</div>
              <div class="td properties-col">{{ solution.properties }}</div>
              <div class="td modified-col">{{ solution.lastModified }}</div>
              <div class="td operation-col">
                <button class="operation-btn" @click="editSolution(solution.id)">Edit</button>
                <button class="operation-btn" @click="deleteSolution(solution.id)">Delete</button>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { ref, computed } from 'vue';

export default {
  name: 'ScheduleTab',
  setup() {
    const scheduleSearchQuery = ref('');
    const solutionSearchQuery = ref('');
    const selectedScheduleId = ref(null);
    const viewMode = ref('list');
    const selectedSolutions = ref([]);

    // Mock data
    const schedules = ref([
      { id: '1', name: 'Schedule1', resolution: '1366*768' },
    ]);

    const solutions = ref([
      // Empty for now to show empty state
    ]);

    const filteredSchedules = computed(() => {
      const q = scheduleSearchQuery.value.trim().toLowerCase();
      if (!q) return schedules.value;
      return schedules.value.filter(s =>
        s.name.toLowerCase().includes(q)
      );
    });

    const filteredSolutions = computed(() => {
      const q = solutionSearchQuery.value.trim().toLowerCase();
      let result = solutions.value;
      if (q) {
        result = result.filter(s =>
          s.name.toLowerCase().includes(q)
        );
      }
      return result;
    });

    const allSelected = computed(() => {
      return filteredSolutions.value.length > 0 &&
        selectedSolutions.value.length === filteredSolutions.value.length;
    });

    const selectSchedule = (id) => {
      selectedScheduleId.value = id;
    };

    const sendSchedule = (id) => {
      console.log('Send schedule:', id);
    };

    const openMenu = (id) => {
      console.log('Open menu:', id);
    };

    const newSchedule = () => {
      console.log('New schedule');
    };

    const importSchedule = () => {
      console.log('Import schedule');
    };

    const usbPlayback = () => {
      console.log('USB playback');
    };

    const addSolution = () => {
      console.log('Add solution');
    };

    const removeSolutions = () => {
      console.log('Remove solutions:', selectedSolutions.value);
      selectedSolutions.value = [];
    };

    const toggleSelectAll = (e) => {
      if (e.target.checked) {
        selectedSolutions.value = filteredSolutions.value.map(s => s.id);
      } else {
        selectedSolutions.value = [];
      }
    };

    const toggleSolution = (id) => {
      const index = selectedSolutions.value.indexOf(id);
      if (index > -1) {
        selectedSolutions.value.splice(index, 1);
      } else {
        selectedSolutions.value.push(id);
      }
    };

    const editSolution = (id) => {
      console.log('Edit solution:', id);
    };

    const deleteSolution = (id) => {
      console.log('Delete solution:', id);
    };

    return {
      scheduleSearchQuery,
      solutionSearchQuery,
      selectedScheduleId,
      viewMode,
      selectedSolutions,
      filteredSchedules,
      filteredSolutions,
      allSelected,
      selectSchedule,
      sendSchedule,
      openMenu,
      newSchedule,
      importSchedule,
      usbPlayback,
      addSolution,
      removeSolutions,
      toggleSelectAll,
      toggleSolution,
      editSolution,
      deleteSolution,
    };
  },
};
</script>

<style scoped>
.tab-content {
  color: #fff;
}

.schedule-tab {
  height: 100%;
  display: flex;
  flex-direction: column;
}

.schedule-layout {
  display: flex;
  gap: 16px;
  height: 100%;
  min-height: calc(100vh - 200px);
}

/* Left Sidebar */
.sidebar {
  width: 215px;
  background: #1e1e1e;
  border: 1px solid #3d3d3d;
  border-radius: 6px;
  display: flex;
  flex-direction: column;
  padding: 16px;
  gap: 16px;
}

.sidebar-search {
  position: relative;
}

.sidebar-search .search-input {
  width: 100%;
  background: #2a2a2a;
  border: 1px solid #3d3d3d;
  color: #e5e7eb;
  padding: 8px 36px 8px 10px;
  border-radius: 6px;
  font-size: 13px;
}

.sidebar-search .search-input::placeholder {
  color: #9ca3af;
}

.sidebar-search .search-icon {
  position: absolute;
  right: 10px;
  top: 50%;
  transform: translateY(-50%);
  width: 16px;
  height: 16px;
  color: #9ca3af;
  pointer-events: none;
}

.schedule-list {
  flex: 1;
  overflow-y: auto;
  display: flex;
  flex-direction: column;
  gap: 8px;
}

.schedule-item {
  display: flex;
  align-items: center;
  gap: 10px;
  padding: 10px;
  background: #2a2a2a;
  border: 1px solid #3d3d3d;
  border-radius: 6px;
  cursor: pointer;
  transition: all 0.2s;
}

.schedule-item:hover {
  background: #333;
  border-color: #4d4d4d;
}

.schedule-item.active {
  background: #2563eb;
  border-color: #2563eb;
}

.screen-icon {
  width: 20px;
  height: 20px;
  color: #9ca3af;
  flex-shrink: 0;
}

.schedule-item.active .screen-icon {
  color: #fff;
}

.schedule-info {
  flex: 1;
  min-width: 0;
}

.schedule-name {
  font-size: 13px;
  font-weight: 500;
  color: #fff;
  margin-bottom: 2px;
}

.schedule-resolution {
  font-size: 11px;
  color: #9ca3af;
}

.schedule-actions {
  display: flex;
  gap: 4px;
  flex-shrink: 0;
}

.action-btn {
  background: transparent;
  border: none;
  color: #9ca3af;
  cursor: pointer;
  padding: 4px;
  display: flex;
  align-items: center;
  justify-content: center;
  border-radius: 4px;
  transition: all 0.2s;
}

.action-btn:hover {
  background: #3d3d3d;
  color: #fff;
}

.action-btn svg {
  width: 16px;
  height: 16px;
}

.sidebar-footer {
  display: flex;
  flex-direction: column;
  gap: 8px;
}

.footer-row {
  display: flex;
  justify-content: space-between;
}

.btn {
  padding: 10px 10px;
  border-radius: 6px;
  font-size: 13px;
  font-weight: 500;
  cursor: pointer;
  border: none;
  transition: all 0.2s;
}

.btn-primary {
  background: #2563eb;
  color: #fff;
}

.btn-primary:hover {
  background: #1d4ed8;
}

.btn-secondary {
  background: #3d3d3d;
  color: #fff;
}

.btn-secondary:hover {
  background: #4d4d4d;
}

.btn-secondary:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

/* Right Main Content */
.main-content {
  flex: 1;
  display: flex;
  flex-direction: column;
  gap: 16px;
}

.sub-nav {
  display: flex;
  gap: 8px;
  border-bottom: 1px solid #3d3d3d;
}

.sub-nav-btn {
  background: transparent;
  border: none;
  color: #9ca3af;
  padding: 10px 16px;
  font-size: 13px;
  font-weight: 500;
  cursor: pointer;
  border-bottom: 2px solid transparent;
  transition: all 0.2s;
}

.sub-nav-btn:hover {
  color: #fff;
}

.sub-nav-btn.active {
  color: #2563eb;
  border-bottom-color: #2563eb;
}

.action-buttons {
  display: flex;
  gap: 10px;
}

.table-section {
  flex: 1;
  background: #1e1e1e;
  border: 1px solid #3d3d3d;
  border-radius: 6px;
  overflow: auto;
  display: flex;
  flex-direction: column;
}

.table-header-row {
  display: flex;
  align-items: center;
  background: #2a2a2a;
  border-bottom: 1px solid #3d3d3d;
  padding: 12px 16px;
  gap: 16px;
  min-width: 900px;
}

.table-header {
  display: grid;
  grid-template-columns: 40px 1.5fr 1fr 1fr 1fr 1.2fr 1fr;
  gap: 16px;
  flex: 1;
  color: #cbd5e1;
  font-weight: 600;
  font-size: 13px;
  width: 100%;
}

.th {
  display: flex;
  align-items: center;
  gap: 6px;
}

.sort-icon {
  width: 12px;
  height: 12px;
  color: #9ca3af;
  opacity: 0.6;
}

.table-search {
  position: relative;
  width: 200px;
}

.table-search .search-input {
  width: 100%;
  background: #1e1e1e;
  border: 1px solid #3d3d3d;
  color: #e5e7eb;
  padding: 6px 32px 6px 10px;
  border-radius: 6px;
  font-size: 12px;
}

.table-search .search-input::placeholder {
  color: #9ca3af;
}

.table-search .search-icon {
  position: absolute;
  right: 8px;
  top: 50%;
  transform: translateY(-50%);
  width: 14px;
  height: 14px;
  color: #9ca3af;
  pointer-events: none;
}

.empty-state {
  flex: 1;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  padding: 60px 20px;
  color: #9ca3af;
}

.empty-icon {
  width: 120px;
  height: 120px;
  color: #4d4d4d;
  margin-bottom: 24px;
}

.empty-text {
  font-size: 14px;
  color: #9ca3af;
}

.empty-link {
  color: #2563eb;
  text-decoration: none;
  cursor: pointer;
}

.empty-link:hover {
  text-decoration: underline;
}

.table-body {
  flex: 1;
  overflow-y: auto;
  /* Match scroll width with header */
  min-width: 900px;
}

.table-row {
  display: grid;
  grid-template-columns: 40px 1.5fr 1fr 1fr 1fr 1.2fr 1fr;
  gap: 16px;
  padding: 12px 16px;
  border-bottom: 1px solid #2c2c2c;
  color: #e5e7eb;
  font-size: 13px;
  align-items: center;
}

.table-row:hover {
  background: #2a2a2a;
}

.td {
  display: flex;
  align-items: center;
}

.checkbox-col input[type="checkbox"] {
  width: 16px;
  height: 16px;
  cursor: pointer;
}

.operation-col {
  display: flex;
  gap: 8px;
}

.operation-btn {
  background: transparent;
  border: 1px solid #3d3d3d;
  color: #cbd5e1;
  padding: 4px 12px;
  border-radius: 4px;
  font-size: 12px;
  cursor: pointer;
  transition: all 0.2s;
}

.operation-btn:hover {
  background: #3d3d3d;
  border-color: #4d4d4d;
}

@media (max-width: 1200px) {
  .schedule-layout {
    flex-direction: column;
  }

  .sidebar {
    width: 100%;
  }
}
</style>

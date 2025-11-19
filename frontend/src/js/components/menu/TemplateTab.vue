<template>
  <div class="tab-content template-tab">
    <!-- Header with Title and Action Buttons -->
    <div class="header-section">
      <h1 class="page-title">Templates</h1>
      <div class="header-buttons">
        <button class="action-button new-template-button" @click="newTemplate">
          <Plus :size="16" class="plus-icon" />
          <span>New Template</span>
        </button>
      </div>
    </div>

    <!-- Table -->
    <div class="table-container">
      <div class="table">
        <div class="table-header">
          <div class="th template-name">TEMPLATE NAME</div>
          <div class="th description">DESCRIPTION</div>
          <div class="th assigned-devices">ASSIGNED DEVICES</div>
          <div class="th actions">ACTIONS</div>
        </div>
        <div
          v-for="template in filteredTemplates"
          :key="template.id"
          :class="['table-row', { selected: selectedId === template.id }]"
          @click="select(template.id)"
        >
          <div class="td template-name">{{ template.name }}</div>
          <div class="td description">{{ template.description }}</div>
          <div class="td assigned-devices">
            <span class="device-badge">
              <Monitor :size="18" class="monitor-icon"/>
              <span class="device-count">{{ template.assignedDevices }}</span>
            </span>
          </div>
          <div class="td actions">
            <button class="edit-button" @click.stop="editTemplate(template.id)">
              <Edit :size="14" />
              <span>Edit</span>
            </button>
            <button class="assign-button" @click.stop="assignTemplate(template.id)">
              <LinkIcon :size="14" />
              <span>Assign</span>
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { ref, computed } from 'vue';
import { Wand2, Plus, Monitor, Edit, Link as LinkIcon } from 'lucide-vue-next';

export default {
  name: 'TemplateTab',
  components: {
    Wand2,
    Plus,
    Monitor,
    Edit,
    LinkIcon,
  },
  setup() {
    const selectedId = ref(null);

    // Mock data to resemble screenshot
    const templates = ref([
      { 
        id: '1', 
        name: 'Morning Promotion', 
        description: 'Campaign for the morning hours',
        assignedDevices: 2
      },
      { 
        id: '2', 
        name: 'Weekend Special', 
        description: 'Content for Saturdays and Sundays',
        assignedDevices: 1
      },
      // More rows can be added here
    ]);

    const filteredTemplates = computed(() => {
      return templates.value;
    });

    const select = (id) => {
      selectedId.value = id;
    };

    const newTemplate = () => {
      // Placeholder for future API call
      console.log('Create new template');
    };

    const editTemplate = (id) => {
      // Placeholder for future API call
      console.log('Edit template:', id);
    };

    const assignTemplate = (id) => {
      // Placeholder for future API call
      console.log('Assign template:', id);
    };

    return {
      selectedId,
      filteredTemplates,
      select,
      newTemplate,
      editTemplate,
      assignTemplate,
    };
  },
};
</script>

<style scoped>
.tab-content {
  color: var(--text-primary);
}

.template-tab {
  display: flex;
  flex-direction: column;
  gap: 16px;
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

.header-buttons {
  display: flex;
  align-items: center;
  gap: 12px;
  flex-wrap: wrap;
}

.action-button {
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

.action-button:hover {
  background: var(--button-secondary-hover);
}

.plus-icon {
  flex-shrink: 0;
}

.table-container {
  background: var(--bg-card);
  border: 1px solid var(--border-color);
  border-radius: 6px;
  overflow-x: auto;
  overflow-y: hidden;
  -webkit-overflow-scrolling: touch;
}

.table {
  width: 100%;
  min-width: 800px;
}

.table-header {
  display: grid;
  grid-template-columns: 1.5fr 2fr 1.2fr 1.5fr;
  color: var(--text-primary);
  background: var(--bg-tertiary);
  border-bottom: 1px solid var(--border-color);
  font-weight: 600;
  text-transform: uppercase;
  font-size: 12px;
  letter-spacing: 0.5px;
}

.th {
  padding: 12px 16px;
  font-size: 12px;
}

.table-row {
  display: grid;
  grid-template-columns: 1.5fr 2fr 1.2fr 1.5fr;
  color: var(--text-primary);
  border-bottom: 1px solid var(--border-subtle);
  cursor: pointer;
  transition: background-color 0.2s;
}

.table-row:hover {
  background: var(--bg-tertiary);
}

.table-row.selected {
  background: var(--primary-button-bg);
  color: var(--text-primary);
}

.td {
  padding: 12px 16px;
  font-size: 13px;
  display: flex;
  align-items: center;
}

.td.assigned-devices {
  gap: 8px;
}

.device-badge {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  background: var(--bg-tertiary);
  border: 1px solid var(--border-color);
  border-radius: 8px;
  padding: 8px 14px;
  transition: all 0.2s;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
}

.device-badge:hover {
  background: var(--bg-hover);
  box-shadow: 0 2px 6px rgba(0, 0, 0, 0.15);
  transform: translateY(-1px);
}

.monitor-icon {
  color: var(--color-blue);
  flex-shrink: 0;
}

.device-count {
  color: var(--color-blue);
  font-size: 15px;
  font-weight: 600;
  line-height: 1;
}

.td.actions {
  gap: 12px;
  cursor: default;
}

.edit-button,
.assign-button {
  display: inline-flex;
  align-items: center;
  gap: 4px;
  background: transparent;
  border: none;
  cursor: pointer;
  font-size: 13px;
  padding: 0;
  transition: opacity 0.2s;
}

.edit-button {
  color: var(--color-blue);
}

.assign-button {
  color: var(--color-green);
}

.edit-button:hover,
.assign-button:hover {
  opacity: 0.8;
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

  .header-buttons {
    width: 100%;
    flex-direction: column;
  }

  .action-button {
    width: 100%;
    justify-content: center;
  }

  .table-container {
    overflow-x: auto;
  }
}
</style>


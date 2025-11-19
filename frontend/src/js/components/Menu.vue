<template>
  <div class="menu-container">
    <!-- Sidebar Navigation -->
    <aside :class="['menu-sidebar', { collapsed: isCollapsed }]">
      <div class="sidebar-header">
        <button 
          class="collapse-toggle" 
          @click="handleToggleCollapse" 
          :disabled="activeTab === 'multiframe'"
          :aria-label="isCollapsed ? 'Expand menu' : 'Collapse menu'"
          :class="{ disabled: activeTab === 'multiframe' }"
        >
          <ChevronLeft :size="20" class="toggle-icon" :class="{ rotated: isCollapsed }" />
        </button>
      </div>
      <nav class="menu-nav-vertical">
        <button
          v-for="tab in tabs"
          :key="tab.id"
          :class="['nav-item', { active: activeTab === tab.id, collapsed: isCollapsed }]"
          @click="activeTab = tab.id"
          :title="isCollapsed ? tab.label : ''"
        >
          <component :is="tab.icon" class="nav-icon" />
          <span v-if="!isCollapsed" class="nav-label">{{ tab.label }}</span>
        </button>
      </nav>
    </aside>

    <!-- Content Area -->
    <main class="menu-content">
      <DashboardTab v-if="activeTab === 'dashboard'" />
      <TemplateTab v-if="activeTab === 'template'" />
      <ContentTab v-if="activeTab === 'content'" />
      <ScheduleTab v-if="activeTab === 'schedule'" />
      <ControlTab v-if="activeTab === 'control'" />
      <DeviceTab v-if="activeTab === 'devices'" />
      <MultiFrameTab v-if="activeTab === 'multiframe'" />
    </main>
  </div>
</template>

<script>
import { ref, watch } from 'vue';
import { Monitor, Play, Calendar, Menu as MenuIcon, FileText, ChevronLeft, Film, LayoutDashboard, Layout } from 'lucide-vue-next';
import ScheduleTab from './menu/ScheduleTab.vue';
import ControlTab from './menu/ControlTab.vue';
import TemplateTab from './menu/TemplateTab.vue';
import ContentTab from './menu/ContentTab.vue';
import DashboardTab from './menu/DashboardTab.vue';
import DeviceTab from './menu/DeviceTab.vue';
import MultiFrameTab from './menu/MultiFrameTab.vue';

export default {
  name: 'Menu',
  components: {
    DashboardTab,
    ScheduleTab,
    ControlTab,
    TemplateTab,
    ContentTab,
    ChevronLeft,
    DeviceTab,
    MultiFrameTab,
  },
  setup() {
    const activeTab = ref('dashboard');
    const isCollapsed = ref(false);

    const tabs = [
      { id: 'dashboard', label: 'Dashboard', icon: LayoutDashboard },
      { id: 'devices', label: 'Devices', icon: Monitor },
      { id: 'schedule', label: 'Schedule', icon: Calendar },
      { id: 'control', label: 'Control', icon: MenuIcon },
      { id: 'content', label: 'Content', icon: Film },
      { id: 'template', label: 'Templates', icon: FileText },
      { id: 'multiframe', label: 'Multi-Frame', icon: Layout },
    ];

    // Auto collapse when switching to multiframe tab
    watch(activeTab, (newTab) => {
      if (newTab === 'multiframe') {
        isCollapsed.value = true;
      }
    });

    // Handle toggle collapse - prevent expand when on multiframe tab
    const handleToggleCollapse = () => {
      if (activeTab.value === 'multiframe') {
        return; // Do nothing when on multiframe tab
      }
      isCollapsed.value = !isCollapsed.value;
    };

    return {
      activeTab,
      isCollapsed,
      tabs,
      handleToggleCollapse,
    };
  },
};
</script>

<style scoped>
.menu-container {
  display: flex;
  min-height: calc(100vh - 80px);
  background-color: var(--bg-secondary);
  color: var(--text-primary);
  transition: background-color 0.3s ease, color 0.3s ease;
}

.menu-sidebar {
  display: flex;
  flex-direction: column;
  width: 240px;
  background-color: var(--bg-primary);
  border-right: 2px solid var(--border-color);
  transition: width 0.25s ease, background-color 0.3s ease, border-color 0.3s ease;
}

.menu-sidebar.collapsed {
  width: 64px;
}

.sidebar-header {
  display: flex;
  align-items: center;
  justify-content: flex-end;
  gap: 8px;
  padding: 12px;
  border-bottom: 1px solid var(--border-color);
  transition: border-color 0.3s ease;
}

.collapse-toggle {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  padding: 8px 10px;
  background: transparent;
  border: 1px solid var(--border-color);
  color: var(--text-secondary);
  cursor: pointer;
  border-radius: 6px;
  transition: all 0.2s ease;
}

.collapse-toggle:hover:not(:disabled) {
  color: var(--text-primary);
  border-color: var(--border-color);
  background-color: var(--bg-secondary);
}

.collapse-toggle:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

.toggle-icon {
  display: inline-block;
  transition: transform 0.2s ease;
  flex-shrink: 0;
}

.toggle-icon.rotated {
  transform: rotate(180deg);
}

.menu-nav-vertical {
  display: flex;
  flex-direction: column;
  padding: 8px;
  gap: 4px;
}

.nav-item {
  display: flex;
  align-items: center;
  gap: 8px;
  padding: 12px 14px;
  background: transparent;
  border: none;
  color: var(--text-secondary);
  cursor: pointer;
  transition: all 0.3s ease;
  border-left: 3px solid transparent;
  font-size: 14px;
  font-weight: 500;
}

.nav-item:hover {
  color: var(--text-primary);
  background-color: var(--bg-secondary);
}

.nav-item.active {
  color: #3b82f6;
  border-left-color: #3b82f6;
  background-color: var(--bg-secondary);
}

.nav-icon {
  width: 20px;
  height: 20px;
  stroke: currentColor;
  flex-shrink: 0;
}

.nav-label {
  white-space: nowrap;
}

.menu-content {
  flex: 1;
  padding: 32px;
  min-width: 0;
  overflow-x: hidden;
  overflow-y: auto;
}

@media (max-width: 768px) {
  .menu-container {
    flex-direction: column;
  }
  .sidebar-header {
    display: none;
  }
  .menu-sidebar {
    width: 100% !important;
    border-right: none;
    border-bottom: 2px solid var(--border-color);
  }
  .menu-sidebar.collapsed {
    width: 100% !important;
  }
  .menu-nav-vertical {
    flex-direction: row;
    padding: 0;
    gap: 0;
    overflow-x: auto;
  }
  .nav-item {
    justify-content: center;
    padding: 12px 16px;
    border-left: none;
    border-bottom: 3px solid transparent;
    flex: 1 0 auto;
  }
  .nav-item.active {
    border-bottom-color: #3b82f6;
  }
  .nav-label {
    display: inline;
  }
  .menu-content {
    padding: 16px;
    min-height: auto;
  }
}
</style>


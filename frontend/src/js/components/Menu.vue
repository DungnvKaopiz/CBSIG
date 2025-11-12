<template>
  <div class="menu-container">
    <!-- Sidebar Navigation -->
    <aside :class="['menu-sidebar', { collapsed: isCollapsed }]">
      <div class="sidebar-header">
        <button class="collapse-toggle" @click="isCollapsed = !isCollapsed" :aria-label="isCollapsed ? 'Expand menu' : 'Collapse menu'">
          <span class="toggle-icon" :class="{ rotated: isCollapsed }">❮</span>
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
      <ScreenTab v-if="activeTab === 'screen'" />
      <SolutionsTab v-if="activeTab === 'solutions'" />
      <ScheduleTab v-if="activeTab === 'schedule'" />
      <ControlTab v-if="activeTab === 'control'" />
    </main>
  </div>
</template>

<script>
import { ref } from 'vue';
import ScreenTab from './menu/ScreenTab.vue';
import SolutionsTab from './menu/SolutionsTab.vue';
import ScheduleTab from './menu/ScheduleTab.vue';
import ControlTab from './menu/ControlTab.vue';

// Navigation Tab Icons
const MonitorIcon = {
  template: `
    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
    </svg>
  `
};

const PlayIcon = {
  template: `
    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z" />
      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
    </svg>
  `
};

const CalendarIcon = {
  template: `
    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
    </svg>
  `
};

const MenuIcon = {
  template: `
    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
    </svg>
  `
};

export default {
  name: 'Menu',
  components: {
    ScreenTab,
    SolutionsTab,
    ScheduleTab,
    ControlTab,
  },
  setup() {
    const activeTab = ref('screen');
    const isCollapsed = ref(false);

    const tabs = [
      { id: 'screen', label: 'Screens', icon: MonitorIcon },
      { id: 'solutions', label: 'Solutions', icon: PlayIcon },
      { id: 'schedule', label: 'Schedule', icon: CalendarIcon },
      { id: 'control', label: 'Control', icon: MenuIcon },
    ];

    return {
      activeTab,
      isCollapsed,
      tabs,
    };
  },
};
</script>

<style scoped>
.menu-container {
  display: flex;
  min-height: calc(100vh - 80px);
  background-color: #2d2d2d;
  color: #ffffff;
}

.menu-sidebar {
  display: flex;
  flex-direction: column;
  width: 240px;
  background-color: #1e1e1e;
  border-right: 2px solid #3d3d3d;
  transition: width 0.25s ease;
}

.menu-sidebar.collapsed {
  width: 64px;
}

.sidebar-header {
  display: flex;
  align-items: center;
  justify-content: flex-end;
  padding: 12px;
  border-bottom: 1px solid #3d3d3d;
}

.collapse-toggle {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  padding: 8px 10px;
  background: transparent;
  border: 1px solid #3d3d3d;
  color: #bbb;
  cursor: pointer;
  border-radius: 6px;
  transition: all 0.2s ease;
}

.collapse-toggle:hover {
  color: #fff;
  border-color: #4b4b4b;
  background-color: #2a2a2a;
}

.toggle-icon {
  display: inline-block;
  transition: transform 0.2s ease;
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
  color: #999;
  cursor: pointer;
  transition: all 0.3s ease;
  border-left: 3px solid transparent;
  font-size: 14px;
  font-weight: 500;
}

.nav-item:hover {
  color: #fff;
  background-color: #2d2d2d;
}

.nav-item.active {
  color: #3b82f6;
  border-left-color: #3b82f6;
  background-color: #2d2d2d;
}

.nav-icon {
  width: 20px;
  height: 20px;
  stroke: currentColor;
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
    border-bottom: 2px solid #3d3d3d;
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
    min-height: auto; /* avoid double scroll on small screens */
  }
}
</style>


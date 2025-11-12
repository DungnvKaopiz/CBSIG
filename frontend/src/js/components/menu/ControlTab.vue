<template>
  <div class="tab-content control-content">
    <div class="control-grid">
      <button
        v-for="control in controls"
        :key="control.id"
        class="control-button"
        @click="handleControlClick(control.id)"
      >
        <component :is="control.icon" class="control-icon" />
        <span class="control-label">{{ control.label }}</span>
      </button>
    </div>
  </div>
</template>

<script>
// Control Function Icons
const PlaybackIcon = {
  template: `
    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
      <circle cx="12" cy="12" r="10" stroke-width="2" fill="none"/>
      <path d="M10 8l6 4-6 4V8z" fill="currentColor"/>
    </svg>
  `
};

const BrightnessIcon = {
  template: `
    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
      <circle cx="12" cy="12" r="5" stroke-width="2"/>
      <path d="M12 1v4M12 19v4M23 12h-4M5 12H1M19.07 4.93l-2.83 2.83M7.76 16.24l-2.83 2.83M19.07 19.07l-2.83-2.83M7.76 7.76L4.93 4.93" stroke-width="2"/>
    </svg>
  `
};

const VideoSourceIcon = {
  template: `
    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
      <rect x="2" y="3" width="20" height="14" rx="2" stroke-width="2"/>
      <path d="M8 21h8M12 17v4" stroke-width="2"/>
      <circle cx="18" cy="7" r="2" fill="currentColor"/>
    </svg>
  `
};

const ScreenStatusIcon = {
  template: `
    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
      <rect x="2" y="3" width="20" height="14" rx="2" stroke-width="2"/>
      <path d="M8 21h8M12 17v4" stroke-width="2"/>
      <path d="M12 7l-3 3h6l-3-3z" fill="currentColor"/>
    </svg>
  `
};

const TimeSyncIcon = {
  template: `
    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
      <circle cx="12" cy="12" r="10" stroke-width="2"/>
      <path d="M12 6v6l4 2" stroke-width="2"/>
      <rect x="2" y="2" width="4" height="4" rx="1" fill="currentColor"/>
    </svg>
  `
};

const RestartIcon = {
  template: `
    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
      <path d="M12 2L15 8L22 9L17 13L18 20L12 17L6 20L7 13L2 9L9 8L12 2Z" stroke-width="2" fill="none"/>
    </svg>
  `
};

const ColorTempIcon = {
  template: `
    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
      <rect x="8" y="4" width="8" height="16" rx="2" stroke-width="2"/>
      <text x="12" y="14" text-anchor="middle" font-size="10" font-weight="bold" fill="currentColor">K</text>
    </svg>
  `
};

const MonitoringIcon = {
  template: `
    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
      <path d="M3 12h4M7 8v8M11 6v12M15 4v16M19 10v4" stroke-width="2" stroke-linecap="round"/>
      <path d="M2 18h20" stroke-width="2"/>
    </svg>
  `
};

const PlayLogsIcon = {
  template: `
    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
      <rect x="3" y="4" width="18" height="16" rx="2" stroke-width="2"/>
      <path d="M7 8h10M7 12h10M7 16h6" stroke-width="2"/>
    </svg>
  `
};

const FontIcon = {
  template: `
    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
      <rect x="6" y="4" width="12" height="16" rx="2" stroke-width="2"/>
      <text x="12" y="14" text-anchor="middle" font-size="10" font-weight="bold" fill="currentColor">F</text>
    </svg>
  `
};

const NetworkIcon = {
  template: `
    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
      <path d="M12 2C8 2 4.5 4.5 4.5 8.5c0 2 1 3.8 2.5 5M12 2c4 0 7.5 2.5 7.5 6.5 0 2-1 3.8-2.5 5M12 2v6M4.5 8.5h15M6.5 13.5c1.5 1.2 3.5 1.2 5 0M17.5 13.5c-1.5 1.2-3.5 1.2-5 0" stroke-width="2"/>
      <circle cx="12" cy="18" r="2" fill="currentColor"/>
    </svg>
  `
};

const ServerIcon = {
  template: `
    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
      <rect x="2" y="4" width="20" height="4" rx="1" stroke-width="2"/>
      <rect x="2" y="10" width="20" height="4" rx="1" stroke-width="2"/>
      <rect x="2" y="16" width="20" height="4" rx="1" stroke-width="2"/>
    </svg>
  `
};

const UpgradeIcon = {
  template: `
    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
      <circle cx="12" cy="12" r="10" stroke-width="2"/>
      <path d="M12 8v8M8 12l4-4 4 4" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
    </svg>
  `
};

const PowerIcon = {
  template: `
    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
      <circle cx="12" cy="12" r="10" stroke-width="2"/>
      <path d="M12 6v6" stroke-width="2" stroke-linecap="round"/>
    </svg>
  `
};

const RFIcon = {
  template: `
    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
      <circle cx="12" cy="12" r="8" stroke-width="2" fill="none"/>
      <circle cx="12" cy="12" r="4" stroke-width="2" fill="none"/>
      <circle cx="12" cy="12" r="1.5" fill="currentColor"/>
    </svg>
  `
};

export default {
  name: 'ControlTab',
  setup() {
    const controls = [
      { id: 'playback', label: 'Playback Management', icon: PlaybackIcon },
      { id: 'brightness', label: 'Brightness adjustment', icon: BrightnessIcon },
      { id: 'video-source', label: 'Video source', icon: VideoSourceIcon },
      { id: 'screen-status', label: 'Screen Status Control', icon: ScreenStatusIcon },
      { id: 'time-sync', label: 'Time synchronization management', icon: TimeSyncIcon },
      { id: 'restart', label: 'Restart Configuration', icon: RestartIcon },
      { id: 'color-temp', label: 'Color temperature', icon: ColorTempIcon },
      { id: 'monitoring', label: 'Monitoring', icon: MonitoringIcon },
      { id: 'play-logs', label: 'Play logs', icon: PlayLogsIcon },
      { id: 'font', label: 'Font management', icon: FontIcon },
      { id: 'network', label: 'Network configuration', icon: NetworkIcon },
      { id: 'server', label: 'Server configuration', icon: ServerIcon },
      { id: 'upgrade', label: 'Player Upgrade', icon: UpgradeIcon },
      { id: 'power', label: 'Power control', icon: PowerIcon },
      { id: 'rf', label: 'RF Configuration', icon: RFIcon },
    ];

    const handleControlClick = (controlId) => {
      console.log('Control clicked:', controlId);
      // Handle control button click
    };

    return {
      controls,
      handleControlClick,
    };
  },
};
</script>

<style scoped>
.tab-content {
  color: #fff;
}

.control-content {
  width: 100%;
}

.control-grid {
  display: grid;
  grid-template-columns: repeat(5, 1fr);
  gap: 24px;
  max-width: 1400px;
  margin: 0 auto;
}

.control-button {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  gap: 12px;
  padding: 32px 16px;
  background-color: #3d3d3d;
  border: 2px solid transparent;
  border-radius: 8px;
  color: #fff;
  cursor: pointer;
  transition: all 0.3s ease;
  min-height: 140px;
}

.control-button:hover {
  background-color: #4d4d4d;
  border-color: #5d5d5d;
  transform: translateY(-2px);
}

.control-icon {
  width: 48px;
  height: 48px;
  stroke: currentColor;
  fill: none;
}

.control-label {
  font-size: 14px;
  text-align: center;
  line-height: 1.4;
  font-weight: 500;
}

@media (max-width: 1400px) {
  .control-grid {
    grid-template-columns: repeat(4, 1fr);
  }
}

@media (max-width: 1024px) {
  .control-grid {
    grid-template-columns: repeat(3, 1fr);
  }
}

@media (max-width: 768px) {
  .control-grid {
    grid-template-columns: repeat(2, 1fr);
  }
}

@media (max-width: 480px) {
  .control-grid {
    grid-template-columns: 1fr;
  }
}
</style>


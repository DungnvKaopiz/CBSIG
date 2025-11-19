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
import { 
  Play, 
  Sun, 
  Monitor, 
  Activity, 
  Clock, 
  RefreshCw, 
  Thermometer, 
  BarChart3, 
  FileText, 
  Type, 
  Network, 
  Server, 
  ArrowUp, 
  Power, 
  Radio 
} from 'lucide-vue-next';

export default {
  name: 'ControlTab',
  components: {
    Play,
    Sun,
    Monitor,
    Activity,
    Clock,
    RefreshCw,
    Thermometer,
    BarChart3,
    FileText,
    Type,
    Network,
    Server,
    ArrowUp,
    Power,
    Radio,
  },
  setup() {
    const controls = [
      { id: 'playback', label: 'Playback Management', icon: Play },
      { id: 'brightness', label: 'Brightness adjustment', icon: Sun },
      { id: 'video-source', label: 'Video source', icon: Monitor },
      { id: 'screen-status', label: 'Screen Status Control', icon: Activity },
      { id: 'time-sync', label: 'Time synchronization management', icon: Clock },
      { id: 'restart', label: 'Restart Configuration', icon: RefreshCw },
      { id: 'color-temp', label: 'Color temperature', icon: Thermometer },
      { id: 'monitoring', label: 'Monitoring', icon: BarChart3 },
      { id: 'play-logs', label: 'Play logs', icon: FileText },
      { id: 'font', label: 'Font management', icon: Type },
      { id: 'network', label: 'Network configuration', icon: Network },
      { id: 'server', label: 'Server configuration', icon: Server },
      { id: 'upgrade', label: 'Player Upgrade', icon: ArrowUp },
      { id: 'power', label: 'Power control', icon: Power },
      { id: 'rf', label: 'RF Configuration', icon: Radio },
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
  color: var(--text-primary);
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
  background-color: var(--border-color);
  border: 2px solid transparent;
  border-radius: 8px;
  color: var(--text-primary);
  cursor: pointer;
  transition: all 0.3s ease;
  min-height: 140px;
}

.control-button:hover {
  background-color: var(--border-hover);
  border-color: #5d5d5d;
  transform: translateY(-2px);
}

.control-icon {
  width: 48px;
  height: 48px;
  stroke: currentColor;
  fill: none;
  flex-shrink: 0;
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


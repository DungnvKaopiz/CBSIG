<template>
  <div class="tab-content dashboard-tab">
    <!-- Metrics Cards Row -->
    <div class="metrics-row">
      <div class="metric-card">
        <div class="metric-icon blue">
          <Monitor :size="24" />
        </div>
        <div class="metric-content">
          <div class="metric-label">Total Devices</div>
          <div class="metric-value">{{ totalDevices }}</div>
        </div>
      </div>

      <div class="metric-card">
        <div class="metric-icon green">
          <Users :size="24" />
        </div>
        <div class="metric-content">
          <div class="metric-label">Online Devices</div>
          <div class="metric-value">{{ onlineDevices }}</div>
        </div>
      </div>

      <div class="metric-card">
        <div class="metric-icon purple">
          <Eye :size="24" />
        </div>
        <div class="metric-content">
          <div class="metric-label">Total Views Today</div>
          <div class="metric-value">{{ totalViewsToday.toLocaleString() }}</div>
        </div>
      </div>

      <div class="metric-card">
        <div class="metric-icon pink">
          <Target :size="24" />
        </div>
        <div class="metric-content">
          <div class="metric-label">Average Viewer Age</div>
          <div class="metric-value">{{ averageViewerAge }}</div>
        </div>
      </div>
    </div>

    <!-- Charts Row -->
    <div class="charts-row">
      <!-- Viewer Demographics Chart -->
      <div class="chart-card">
        <div class="chart-header">
          <h3 class="chart-title">Viewer Demographics</h3>
        </div>
        <div class="chart-content">
          <apexchart
            ref="demographicsChartRef"
            type="bar"
            height="350"
            :options="demographicsChartOptions"
            :series="demographicsSeries"
          ></apexchart>
        </div>
      </div>

      <!-- Views by Hour Chart -->
      <div class="chart-card">
        <div class="chart-header">
          <h3 class="chart-title">Views by Hour</h3>
        </div>
        <div class="chart-content">
          <apexchart
            ref="viewsByHourChartRef"
            type="line"
            height="350"
            :options="viewsByHourChartOptions"
            :series="viewsByHourSeries"
          ></apexchart>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { ref, onMounted, onUnmounted, nextTick } from 'vue';
import { Monitor, Users, Eye, Target } from 'lucide-vue-next';

export default {
  name: 'DashboardTab',
  components: {
    Monitor,
    Users,
    Eye,
    Target,
  },
  setup() {
    const demographicsChartRef = ref(null);
    const viewsByHourChartRef = ref(null);
    // Metrics data
    const totalDevices = ref(3);
    const onlineDevices = ref(2);
    const totalViewsToday = ref(1489);
    const averageViewerAge = ref(32);

    // Viewer Demographics Chart (Stacked Bar Chart)
    const demographicsSeries = ref([
      {
        name: 'Female',
        data: [150, 280, 200, 120, 60]
      },
      {
        name: 'Male',
        data: [130, 270, 180, 100, 40]
      }
    ]);

    const demographicsChartOptions = ref({
      chart: {
        type: 'bar',
        stacked: true,
        toolbar: {
          show: false
        },
        background: 'transparent',
        width: '100%',
        redrawOnParentResize: true,
        redrawOnWindowResize: true
      },
      colors: ['#ec4899', '#3b82f6'],
      plotOptions: {
        bar: {
          horizontal: false,
          columnWidth: '60%',
        }
      },
      dataLabels: {
        enabled: false
      },
      xaxis: {
        categories: ['18-24', '25-34', '35-44', '45-54', '55+'],
        labels: {
          style: {
            colors: '#9ca3af'
          }
        }
      },
      yaxis: {
        labels: {
          style: {
            colors: '#9ca3af'
          }
        }
      },
      fill: {
        opacity: 1
      },
      legend: {
        position: 'top',
        labels: {
          colors: '#9ca3af'
        }
      },
      grid: {
        borderColor: '#3d3d3d',
        strokeDashArray: 4
      },
      theme: {
        mode: 'dark'
      }
    });

    // Views by Hour Chart (Line Chart)
    const viewsByHourSeries = ref([
      {
        name: 'views',
        data: [50, 80, 120, 200, 180, 150, 130, 110]
      }
    ]);

    const viewsByHourChartOptions = ref({
      chart: {
        type: 'line',
        toolbar: {
          show: false
        },
        background: 'transparent',
        width: '100%',
        redrawOnParentResize: true,
        redrawOnWindowResize: true
      },
      colors: ['#3b82f6'],
      stroke: {
        curve: 'smooth',
        width: 3
      },
      markers: {
        size: 5,
        colors: ['#3b82f6'],
        strokeColors: '#fff',
        strokeWidth: 2
      },
      xaxis: {
        categories: ['09:00', '10:00', '11:00', '12:00', '13:00', '14:00', '15:00', '16:00'],
        labels: {
          style: {
            colors: '#9ca3af'
          }
        }
      },
      yaxis: {
        labels: {
          style: {
            colors: '#9ca3af'
          }
        }
      },
      grid: {
        borderColor: '#3d3d3d',
        strokeDashArray: 4
      },
      legend: {
        show: true,
        labels: {
          colors: '#9ca3af'
        }
      },
      theme: {
        mode: 'dark'
      }
    });

    let resizeObserver = null;

    // Handle resize when sidebar toggles
    const handleResize = () => {
      nextTick(() => {
        if (demographicsChartRef.value) {
          demographicsChartRef.value.updateOptions({}, false, true, true);
        }
        if (viewsByHourChartRef.value) {
          viewsByHourChartRef.value.updateOptions({}, false, true, true);
        }
      });
    };

    onMounted(() => {
      // Listen for window resize events
      window.addEventListener('resize', handleResize);
      
      // Use MutationObserver to detect sidebar class changes
      const sidebar = document.querySelector('.menu-sidebar');
      if (sidebar) {
        resizeObserver = new MutationObserver(() => {
          setTimeout(handleResize, 300); // Delay to allow transition to complete
        });
        
        resizeObserver.observe(sidebar, {
          attributes: true,
          attributeFilter: ['class']
        });
      }
    });

    onUnmounted(() => {
      window.removeEventListener('resize', handleResize);
      if (resizeObserver) {
        resizeObserver.disconnect();
      }
    });

    return {
      totalDevices,
      onlineDevices,
      totalViewsToday,
      averageViewerAge,
      demographicsSeries,
      demographicsChartOptions,
      viewsByHourSeries,
      viewsByHourChartOptions,
      demographicsChartRef,
      viewsByHourChartRef,
    };
  },
};
</script>

<style scoped>
.tab-content {
  color: var(--text-primary);
}

.dashboard-tab {
  display: flex;
  flex-direction: column;
  gap: 24px;
}

.metrics-row {
  display: grid;
  grid-template-columns: repeat(4, 1fr);
  gap: 20px;
}

.metric-card {
  background: var(--bg-card);
  border: 1px solid var(--border-color);
  border-radius: 8px;
  padding: 20px;
  display: flex;
  align-items: center;
  gap: 16px;
  transition: all 0.2s;
}

.metric-card:hover {
  border-color: var(--border-hover);
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
}

.metric-icon {
  width: 56px;
  height: 56px;
  border-radius: 8px;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
}

.metric-icon.blue {
  background: rgba(59, 130, 246, 0.1);
  color: var(--color-blue);
}

.metric-icon.green {
  background: rgba(34, 197, 94, 0.1);
  color: var(--color-green);
}

.metric-icon.purple {
  background: rgba(147, 51, 234, 0.1);
  color: var(--color-purple);
}

.metric-icon.pink {
  background: rgba(236, 72, 153, 0.1);
  color: var(--color-pink);
}

.metric-content {
  flex: 1;
  min-width: 0;
}

.metric-label {
  color: var(--text-secondary);
  font-size: 13px;
  margin-bottom: 4px;
}

.metric-value {
  color: var(--text-primary);
  font-size: 24px;
  font-weight: 600;
  line-height: 1;
}

.charts-row {
  display: grid;
  grid-template-columns: repeat(2, 1fr);
  gap: 20px;
  min-width: 0;
}

.chart-card {
  background: var(--bg-card);
  border: 1px solid var(--border-color);
  border-radius: 8px;
  padding: 20px;
  min-width: 0;
  overflow: hidden;
}

.chart-header {
  margin-bottom: 16px;
}

.chart-title {
  color: var(--text-primary);
  font-size: 16px;
  font-weight: 600;
  margin: 0;
}

.chart-content {
  width: 100%;
  overflow: hidden;
}

.chart-content :deep(.apexcharts-canvas) {
  width: 100% !important;
}

.chart-content :deep(svg) {
  max-width: 100%;
}

@media (max-width: 1200px) {
  .metrics-row {
    grid-template-columns: repeat(2, 1fr);
  }

  .charts-row {
    grid-template-columns: 1fr;
  }
}

@media (max-width: 768px) {
  .metrics-row {
    grid-template-columns: 1fr;
  }
}
</style>


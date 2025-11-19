<template>
  <div class="schedule-tab">
    <header class="page-header">
      <div class="title-group">
        <p class="eyebrow">Schedule</p>
        <h1>Manage content scheduling across your devices</h1>
        <p class="subtitle">
          Keep every display up to date with curated playlists, device groupings, and time ranges.
        </p>
      </div>
      <button class="primary-btn" @click="createSchedule">
        <Plus class="btn-icon" />
        Create Schedule
      </button>
    </header>

    <section class="schedule-list">
      <article v-for="schedule in schedules" :key="schedule.id" class="schedule-card">
        <div class="card-header">
          <div class="card-title">
            <component :is="schedule.icon" class="card-icon" />
            <div>
              <h2>{{ schedule.title }}</h2>
              <p class="card-description">{{ schedule.description }}</p>
            </div>
          </div>
          <div class="card-controls">
            <span :class="['status-pill', schedule.status]">
              <span class="status-dot" />
              {{ schedule.statusLabel }}
            </span>
            <button
              v-if="schedule.status === 'paused'"
              class="ghost-btn"
              @click="resumeSchedule(schedule)"
            >
              <Play class="ghost-icon" />
            </button>
            <button
              v-else
              class="ghost-btn"
              @click="pauseSchedule(schedule)"
            >
              <Pause class="ghost-icon" />
            </button>
            <div class="menu-wrapper" @click.stop>
              <button class="ghost-btn" @click="toggleActionMenu(schedule)">
                <MoreHorizontal class="ghost-icon" />
              </button>
              <div v-if="activeMenuId === schedule.id" class="action-menu">
                <button class="menu-item" @click="editSchedule(schedule)">Edit</button>
                <button class="menu-item danger" @click="deleteSchedule(schedule)">Delete</button>
              </div>
            </div>
          </div>
        </div>

        <div class="card-body">
          <div class="meta-row">
            <ListChecks class="meta-icon" />
            <div>
              <p class="meta-label">Content</p>
              <p class="meta-value link">{{ schedule.content }}</p>
            </div>
          </div>
          <div class="meta-row">
            <Monitor class="meta-icon" />
            <div>
              <p class="meta-label">Devices</p>
              <p class="meta-value">{{ schedule.devices.join(', ') }}</p>
            </div>
          </div>
          <div class="meta-row">
            <Clock3 class="meta-icon" />
            <div>
              <p class="meta-label">Time</p>
              <p class="meta-value">{{ schedule.time }}</p>
            </div>
          </div>
          <div class="meta-row">
            <CalendarDays class="meta-icon" />
            <div>
              <p class="meta-label">Days</p>
              <div class="day-pills">
                <span
                  v-for="day in allDays"
                  :key="day"
                  :class="['day-pill', { active: schedule.days.includes(day) }]"
                >
                  {{ day }}
                </span>
              </div>
            </div>
          </div>
        </div>
      </article>
    </section>
    <ScheduleCreateModal
      :open="showCreateModal"
      :available-devices="availableDevices"
      :default-content="defaultContent"
      @close="showCreateModal = false"
      @submit="handleCreateSubmit"
    />
  </div>
</template>

<script setup>
import {
  CalendarDays,
  Clock3,
  ListChecks,
  Monitor,
  MoreHorizontal,
  Pause,
  Play,
  PlayCircle,
  Plus,
  Video,
} from 'lucide-vue-next';
import { computed, onBeforeUnmount, onMounted, ref } from 'vue';
import ScheduleCreateModal from './modals/ScheduleCreateModal.vue';

const baseSchedules = ref([
  {
    id: 1,
    title: 'Morning Promotions',
    description: 'Daily morning promotional content for all lobby displays',
    content: 'Holiday Promotions',
    devices: ['Lobby Display', 'Reception Display'],
    time: '08:00 - 12:00',
    days: ['Mon', 'Tue', 'Wed', 'Thu', 'Fri'],
    status: 'active',
    statusLabel: 'Active',
    icon: PlayCircle,
  },
  {
    id: 2,
    title: 'Lunch Menu Display',
    description: 'Show daily lunch specials during lunch hours',
    content: 'Menu Items',
    devices: ['Cafeteria Screen'],
    time: '11:30 - 14:30',
    days: ['Mon', 'Tue', 'Wed', 'Thu', 'Fri'],
    status: 'active',
    statusLabel: 'Active',
    icon: PlayCircle,
  },
  {
    id: 3,
    title: 'Welcome Message',
    description: 'Welcome video for new visitors',
    content: 'Welcome Message',
    devices: ['Reception Display'],
    time: '09:00 - 17:00',
    days: ['Mon', 'Tue', 'Wed', 'Thu', 'Fri'],
    status: 'paused',
    statusLabel: 'Paused',
    icon: Video,
  },
  {
    id: 4,
    title: 'Weekend Promotions',
    description: 'Special weekend promotional content',
    content: 'Weekend Specials',
    devices: ['Lobby Display', 'Store Front'],
    time: '10:00 - 20:00',
    days: ['Sat', 'Sun'],
    status: 'active',
    statusLabel: 'Active',
    icon: PlayCircle,
  },
]);
const showCreateModal = ref(false);
const availableDevices = [
  'Lobby Display',
  'Reception Display',
  'Cafeteria Screen',
  'Conference Room A',
  'Store Front',
];
const defaultContent = {
  name: 'Holiday Promotions',
  type: 'playlist',
};

const allDays = ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'];

const schedules = computed(() =>
  baseSchedules.value.map((schedule) => ({
    ...schedule,
    statusLabel: schedule.status === 'active' ? 'Active' : 'Paused',
  }))
);

const activeMenuId = ref(null);

const createSchedule = () => {
  showCreateModal.value = true;
};

const pauseSchedule = (schedule) => {
  console.log('Pause schedule', schedule.id);
};

const resumeSchedule = (schedule) => {
  console.log('Resume schedule', schedule.id);
};

const toggleActionMenu = (schedule) => {
  activeMenuId.value = activeMenuId.value === schedule.id ? null : schedule.id;
};

const editSchedule = (schedule) => {
  console.log('Edit schedule', schedule.id);
  activeMenuId.value = null;
};

const deleteSchedule = (schedule) => {
  console.log('Delete schedule', schedule.id);
  activeMenuId.value = null;
};

const handleCreateSubmit = (payload) => {
  const currentIds = baseSchedules.value.map((s) => s.id);
  const nextId = (currentIds.length ? Math.max(...currentIds) : 0) + 1;
  const formatTime = (time) => {
    const [hourPart, minutePart] = time.split(':');
    const hours = parseInt(hourPart, 10);
    const suffix = hours >= 12 ? 'PM' : 'AM';
    const hour12 = ((hours + 11) % 12) + 1;
    return `${hour12.toString().padStart(2, '0')}:${minutePart} ${suffix}`;
  };

  baseSchedules.value.unshift({
    id: nextId,
    title: payload.name || 'Untitled Schedule',
    description: payload.description || 'Custom schedule',
    content: payload.content?.name || defaultContent.name,
    devices: payload.devices.length ? payload.devices : ['Lobby Display'],
    time: `${formatTime(payload.startTime)} - ${formatTime(payload.endTime)}`,
    days: payload.days.length ? payload.days : allDays,
    status: 'active',
    icon: PlayCircle,
  });

  showCreateModal.value = false;
};

const closeMenu = () => {
  activeMenuId.value = null;
};

onMounted(() => {
  document.addEventListener('click', closeMenu);
});

onBeforeUnmount(() => {
  document.removeEventListener('click', closeMenu);
});
</script>

<style scoped>
.schedule-tab {
  display: flex;
  flex-direction: column;
  gap: 24px;
  color: var(--text-primary);
}

.page-header {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  gap: 16px;
}

.title-group h1 {
  font-size: 26px;
  font-weight: 600;
  margin-bottom: 4px;
}

.title-group .eyebrow {
  font-size: 12px;
  text-transform: uppercase;
  letter-spacing: 0.08em;
  color: var(--text-secondary);
  margin-bottom: 4px;
}

.subtitle {
  color: var(--text-secondary);
  font-size: 14px;
  max-width: 520px;
}

.primary-btn {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  padding: 10px 18px;
  background: var(--button-primary-bg, #2563eb);
  color: #fff;
  border-radius: 8px;
  border: none;
  font-weight: 600;
  cursor: pointer;
  transition: background 0.2s ease;
}

.primary-btn:hover {
  background: var(--button-primary-hover, #1d4ed8);
}

.btn-icon {
  width: 18px;
  height: 18px;
}

.schedule-list {
  display: flex;
  flex-direction: column;
  gap: 16px;
}

.schedule-card {
  background: var(--bg-card);
  border: 1px solid var(--border-color);
  border-radius: 14px;
  padding: 20px;
  display: flex;
  flex-direction: column;
  gap: 20px;
  box-shadow: 0 10px 35px rgba(15, 15, 15, 0.25);
}

.card-header {
  display: flex;
  justify-content: space-between;
  gap: 16px;
  flex-wrap: wrap;
}

.card-title {
  display: flex;
  gap: 12px;
  align-items: flex-start;
}

.card-title h2 {
  font-size: 18px;
  font-weight: 600;
  margin-bottom: 4px;
}

.card-description {
  color: var(--text-secondary);
  font-size: 14px;
  margin: 0;
}

.card-icon {
  width: 36px;
  height: 36px;
  padding: 8px;
  border-radius: 10px;
  background: rgba(59, 130, 246, 0.1);
  color: var(--button-primary-bg, #2563eb);
}

.card-controls {
  display: inline-flex;
  align-items: center;
  gap: 8px;
}

.status-pill {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  padding: 6px 12px;
  border-radius: 999px;
  font-size: 12px;
  font-weight: 600;
  text-transform: uppercase;
  letter-spacing: 0.04em;
}

.status-pill .status-dot {
  width: 8px;
  height: 8px;
  border-radius: 999px;
  background: currentColor;
}

.status-pill.active {
  background: rgba(34, 197, 94, 0.15);
  color: #34d399;
}

.status-pill.paused {
  background: rgba(156, 163, 175, 0.25);
  color: #d1d5db;
}

.ghost-btn {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  width: 36px;
  height: 36px;
  border: 1px solid var(--border-color);
  border-radius: 10px;
  background: transparent;
  color: var(--text-secondary);
  cursor: pointer;
  transition: all 0.2s ease;
}

.ghost-btn:hover {
  background: var(--bg-hover, #2a2a2a);
  color: var(--text-primary);
}

.ghost-icon {
  width: 18px;
  height: 18px;
}

.menu-wrapper {
  position: relative;
}

.action-menu {
  position: absolute;
  right: 0;
  top: 44px;
  display: flex;
  flex-direction: column;
  background: var(--bg-card);
  border: 1px solid var(--border-color);
  border-radius: 10px;
  box-shadow: 0 12px 32px rgba(0, 0, 0, 0.35);
  min-width: 140px;
  padding: 6px;
  z-index: 10;
}

.menu-item {
  border: none;
  background: transparent;
  text-align: left;
  padding: 8px 10px;
  border-radius: 8px;
  color: var(--text-primary);
  font-size: 14px;
  cursor: pointer;
  transition: background 0.2s ease, color 0.2s ease;
}

.menu-item:hover {
  background: var(--bg-hover, #2a2a2a);
}

.menu-item.danger {
  color: #f87171;
}

.menu-item.danger:hover {
  color: #fecaca;
}

.card-body {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
  gap: 20px;
}

.meta-row {
  display: flex;
  gap: 12px;
}

.meta-icon {
  width: 22px;
  height: 22px;
  color: var(--text-secondary);
  flex-shrink: 0;
}

.meta-label {
  font-size: 12px;
  text-transform: uppercase;
  letter-spacing: 0.06em;
  color: var(--text-secondary);
  margin-bottom: 4px;
}

.meta-value {
  font-size: 14px;
  color: var(--text-primary);
}

.meta-value.link {
  color: var(--button-primary-bg, #2563eb);
  cursor: pointer;
}

.day-pills {
  display: flex;
  flex-wrap: wrap;
  gap: 6px;
  margin-top: 6px;
}

.day-pill {
  padding: 4px 10px;
  border-radius: 999px;
  font-size: 12px;
  border: 1px solid var(--border-color);
  color: var(--text-secondary);
}

.day-pill.active {
  background: rgba(59, 130, 246, 0.15);
  border-color: rgba(59, 130, 246, 0.4);
  color: #3b82f6;
}

@media (max-width: 768px) {
  .page-header {
    flex-direction: column;
  }

  .card-body {
    grid-template-columns: 1fr;
  }

  .card-controls {
    width: 100%;
    justify-content: flex-start;
  }
}
</style>


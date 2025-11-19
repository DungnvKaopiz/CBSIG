<template>
  <nav class="navbar navbar-expand-lg navbar-light bg-light mb-4">
    <div class="container">
      <a class="navbar-brand" href="#">CMS Digital Signage</a>
      
      <div class="navbar-nav ms-auto d-flex align-items-center">
        <!-- User info -->
        <div v-if="user" class="me-3">
          <span class="text-muted me-2">{{ user.name }}</span>
          <span v-if="user.roles && user.roles.length > 0" class="badge bg-primary">
            {{ user.roles[0].name }}
          </span>
        </div>
        
        <!-- Theme toggle button -->
        <button 
          class="theme-toggle me-3" 
          @click="toggleTheme" 
          :aria-label="currentTheme === 'dark' ? 'Switch to light mode' : 'Switch to dark mode'"
        >
          <Sun v-if="currentTheme === 'dark'" :size="18" />
          <Moon v-else :size="18" />
        </button>
        
        <!-- Logout button -->
        <button 
          class="btn btn-outline-danger btn-sm" 
          @click="handleLogout"
          :disabled="loading"
        >
          <span v-if="loading" class="spinner-border spinner-border-sm me-2"></span>
          {{ loading ? 'Logging out...' : 'Logout' }}
        </button>
      </div>
    </div>
  </nav>
</template>

<script>
import { Sun, Moon } from 'lucide-vue-next';
import { useAuth } from '@/composables/useAuth';
import { useTheme } from '@/composables/useTheme';

export default {
  name: 'Header',
  components: {
    Sun,
    Moon,
  },
  setup() {
    const { user, loading, logout } = useAuth();
    const { toggleTheme, currentTheme } = useTheme();

    const handleLogout = async () => {
      if (confirm('Are you sure you want to logout?')) {
        await logout();
        // AppWrapper will automatically show login after logout
        // No need to redirect, Vue reactivity will handle it
      }
    };

    return {
      user,
      loading,
      handleLogout,
      toggleTheme,
      currentTheme,
    };
  },
};
</script>

<style scoped>
.navbar {
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
  background-color: var(--bg-primary) !important;
  border-bottom: 1px solid var(--border-color);
  transition: background-color 0.3s ease, border-color 0.3s ease;
}

.navbar-brand {
  color: var(--text-primary) !important;
}

.text-muted {
  color: var(--text-secondary) !important;
}

.theme-toggle {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  padding: 8px;
  background: transparent;
  border: 1px solid var(--border-color);
  color: var(--text-secondary);
  cursor: pointer;
  border-radius: 6px;
  transition: all 0.2s ease;
  flex-shrink: 0;
  width: 36px;
  height: 36px;
}

.theme-toggle:hover {
  color: var(--text-primary);
  border-color: var(--border-hover);
  background-color: var(--bg-secondary);
}
</style>


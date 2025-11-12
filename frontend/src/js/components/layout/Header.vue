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
import { useAuth } from '@/composables/useAuth';

export default {
  name: 'Header',
  setup() {
    const { user, loading, logout } = useAuth();

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
    };
  },
};
</script>

<style scoped>
.navbar {
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}
</style>


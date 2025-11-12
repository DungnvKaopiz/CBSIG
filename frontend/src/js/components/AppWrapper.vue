<template>
  <div>
    <!-- Show loading while checking auth -->
    <div v-if="authLoading" class="d-flex justify-content-center align-items-center" style="min-height: 100vh;">
      <div class="spinner-border text-primary" role="status">
        <span class="visually-hidden">Loading...</span>
      </div>
    </div>

    <!-- Show login/register if not authenticated -->
    <AuthContainer v-else-if="!isAuthenticated" />

    <!-- Show main app if authenticated -->
    <App v-else />
  </div>
</template>

<script>
import { onMounted, watch } from 'vue';
import { useAuth } from '@/composables/useAuth';
import App from './App.vue';
import AuthContainer from './auth/AuthContainer.vue';

export default {
  name: 'AppWrapper',
  components: {
    App,
    AuthContainer,
  },
  setup() {
    const { isAuthenticated, authLoading, initializeAuth } = useAuth();

    // Initialize auth on mount
    onMounted(async () => {
      await initializeAuth();
    });

    // Watch for authentication changes
    watch(isAuthenticated, (authenticated) => {
      if (!authenticated) {
        // User not authenticated, AuthContainer will be shown
        console.log('User not authenticated, showing login');
      }
    });

    return {
      isAuthenticated,
      authLoading,
    };
  },
};
</script>

<style scoped>
</style>


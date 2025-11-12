<template>
  <div class="container mt-5">
    <div class="row justify-content-center">
      <div class="col-md-6">
        <div class="card">
          <div class="card-header">
            <h3 class="text-center">Login</h3>
          </div>
          <div class="card-body">
            <div v-if="error" class="alert alert-danger">{{ error }}</div>
            
            <form @submit.prevent="handleLogin">
              <div class="mb-3">
                <label for="email" class="form-label">Email</label>
                <input
                  type="email"
                  id="email"
                  v-model="form.email"
                  class="form-control"
                  :class="{ 'is-invalid': errors.email }"
                  placeholder="Enter your email"
                  required
                />
                <div v-if="errors.email" class="invalid-feedback">
                  {{ errors.email[0] }}
                </div>
              </div>

              <div class="mb-3">
                <label for="password" class="form-label">Password</label>
                <input
                  type="password"
                  id="password"
                  v-model="form.password"
                  class="form-control"
                  :class="{ 'is-invalid': errors.password }"
                  placeholder="Enter your password"
                  required
                />
                <div v-if="errors.password" class="invalid-feedback">
                  {{ errors.password[0] }}
                </div>
              </div>

              <button type="submit" class="btn btn-primary w-100" :disabled="loading">
                <span v-if="loading" class="spinner-border spinner-border-sm me-2"></span>
                {{ loading ? 'Logging in...' : 'Login' }}
              </button>
            </form>

            <div class="mt-3 text-center">
              <p>Don't have an account? <a href="#" @click.prevent="$emit('switch-to-register')">Register</a></p>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { ref } from 'vue';
import { useAuth } from '@/composables/useAuth';

export default {
  name: 'Login',
  emits: ['switch-to-register'],
  setup() {
    const { login, loading } = useAuth();

    const form = ref({
      email: '',
      password: '',
    });

    const error = ref(null);
    const errors = ref({});

    const handleLogin = async () => {
      error.value = null;
      errors.value = {};

      const result = await login(form.value);

      if (result.success) {
        // Login successful, AppWrapper will automatically show App component
        // The isAuthenticated computed property will update automatically
        // No need to reload, Vue reactivity will handle it
      } else {
        error.value = result.error;
        errors.value = result.errors || {};
      }
    };

    return {
      form,
      error,
      errors,
      loading,
      handleLogin,
    };
  },
};
</script>

<style scoped>
.card {
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}
</style>


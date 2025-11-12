/**
 * Auth Store (Singleton)
 * Quản lý authentication state toàn cục
 */

import { ref, computed } from 'vue';
import client from '@/api/client';

// Singleton state
const user = ref(null);
const loading = ref(false);
const token = ref(localStorage.getItem('token'));

// Set token trong axios header nếu có
if (token.value) {
  client.defaults.headers.common['Authorization'] = `Bearer ${token.value}`;
}

// Computed
const isAuthenticated = computed(() => !!token.value && !!user.value);

// Methods
const hasRole = (role) => {
  if (!user.value) return false;
  return user.value.roles?.some(r => r.name === role) || false;
};

const hasPermission = (permission) => {
  if (!user.value) return false;
  return user.value.permissions?.some(p => p.name === permission) || false;
};

const hasAnyRole = (roles) => {
  if (!user.value) return false;
  return roles.some(role => hasRole(role));
};

const hasAnyPermission = (permissions) => {
  if (!user.value) return false;
  return permissions.some(permission => hasPermission(permission));
};

const login = async (credentials) => {
  loading.value = true;
  try {
    const response = await client.post('/login', credentials);
    token.value = response.data.token;
    user.value = response.data.user;
    localStorage.setItem('token', token.value);
    client.defaults.headers.common['Authorization'] = `Bearer ${token.value}`;
    return { success: true, data: response.data };
  } catch (error) {
    return {
      success: false,
      error: error.response?.data?.message || 'Login failed',
      errors: error.response?.data?.errors,
    };
  } finally {
    loading.value = false;
  }
};

const register = async (userData) => {
  loading.value = true;
  try {
    const response = await client.post('/register', userData);
    token.value = response.data.token;
    user.value = response.data.user;
    localStorage.setItem('token', token.value);
    client.defaults.headers.common['Authorization'] = `Bearer ${token.value}`;
    return { success: true, data: response.data };
  } catch (error) {
    return {
      success: false,
      error: error.response?.data?.message || 'Registration failed',
      errors: error.response?.data?.errors,
    };
  } finally {
    loading.value = false;
  }
};

const logout = async () => {
  loading.value = true;
  try {
    await client.post('/logout');
  } catch (error) {
    console.error('Logout error:', error);
  } finally {
    token.value = null;
    user.value = null;
    localStorage.removeItem('token');
    delete client.defaults.headers.common['Authorization'];
    loading.value = false;
  }
};

const fetchUser = async () => {
  if (!token.value) return;

  loading.value = true;
  try {
    const response = await client.get('/user');
    user.value = response.data.user;
    return { success: true, user: user.value };
  } catch (error) {
    // Token invalid, logout
    if (error.response?.status === 401) {
      await logout();
    }
    return { success: false, error };
  } finally {
    loading.value = false;
  }
};

const init = async () => {
  if (token.value) {
    await fetchUser();
  }
};

// Loading state for auth initialization
const authLoading = ref(true);

const initializeAuth = async () => {
  authLoading.value = true;
  try {
    await init();
  } finally {
    authLoading.value = false;
  }
};

// Export singleton store
export const authStore = {
  // State
  user,
  token,
  loading,
  authLoading,
  isAuthenticated,
  // Methods
  login,
  register,
  logout,
  fetchUser,
  init,
  initializeAuth,
  // Permission checks
  hasRole,
  hasPermission,
  hasAnyRole,
  hasAnyPermission,
};


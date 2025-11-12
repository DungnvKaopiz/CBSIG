/**
 * useAuth Composable
 * Wrapper cho authStore để sử dụng trong components
 */

import { authStore } from '@/stores/authStore';

export function useAuth() {
  // Return singleton store
  return authStore;
}

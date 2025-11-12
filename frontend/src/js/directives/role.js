/**
 * Role Directive
 * Ẩn/hiện element dựa trên role của user
 * 
 * Usage: v-role="'admin'"
 *        v-role:any="['admin', 'editor']"
 *        v-role:all="['admin', 'super-admin']"
 */

import { useAuth } from '@/composables/useAuth';

export default {
  mounted(el, binding) {
    const { hasRole, hasAnyRole } = useAuth();
    const { value, modifiers } = binding;

    let hasAccess = false;

    if (modifiers.any) {
      // Check if user has any of the roles
      hasAccess = hasAnyRole(Array.isArray(value) ? value : [value]);
    } else if (modifiers.all) {
      // Check if user has all roles
      hasAccess = Array.isArray(value)
        ? value.every(role => hasRole(role))
        : hasRole(value);
    } else {
      // Check single role
      hasAccess = hasRole(value);
    }

    if (!hasAccess) {
      el.style.display = 'none';
    }
  },
  updated(el, binding) {
    const { hasRole, hasAnyRole } = useAuth();
    const { value, modifiers } = binding;

    let hasAccess = false;

    if (modifiers.any) {
      hasAccess = hasAnyRole(Array.isArray(value) ? value : [value]);
    } else if (modifiers.all) {
      hasAccess = Array.isArray(value)
        ? value.every(role => hasRole(role))
        : hasRole(value);
    } else {
      hasAccess = hasRole(value);
    }

    el.style.display = hasAccess ? '' : 'none';
  },
};


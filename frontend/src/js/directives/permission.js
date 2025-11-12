/**
 * Permission Directive
 * Ẩn/hiện element dựa trên permission của user
 * 
 * Usage: v-permission="'edit posts'"
 *        v-permission:any="['edit posts', 'delete posts']"
 *        v-permission:all="['edit posts', 'delete posts']"
 */

import { useAuth } from '@/composables/useAuth';

export default {
  mounted(el, binding) {
    const { hasPermission, hasAnyPermission } = useAuth();
    const { value, modifiers } = binding;

    let hasAccess = false;

    if (modifiers.any) {
      // Check if user has any of the permissions
      hasAccess = hasAnyPermission(Array.isArray(value) ? value : [value]);
    } else if (modifiers.all) {
      // Check if user has all permissions
      hasAccess = Array.isArray(value)
        ? value.every(perm => hasPermission(perm))
        : hasPermission(value);
    } else {
      // Check single permission
      hasAccess = hasPermission(value);
    }

    if (!hasAccess) {
      el.style.display = 'none';
    }
  },
  updated(el, binding) {
    const { hasPermission, hasAnyPermission } = useAuth();
    const { value, modifiers } = binding;

    let hasAccess = false;

    if (modifiers.any) {
      hasAccess = hasAnyPermission(Array.isArray(value) ? value : [value]);
    } else if (modifiers.all) {
      hasAccess = Array.isArray(value)
        ? value.every(perm => hasPermission(perm))
        : hasPermission(value);
    } else {
      hasAccess = hasPermission(value);
    }

    el.style.display = hasAccess ? '' : 'none';
  },
};


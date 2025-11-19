import { ref, computed } from 'vue';

const THEME_KEY = 'app-theme';
const DARK_THEME = 'dark';
const LIGHT_THEME = 'light';

// Initialize theme from localStorage or default to dark
const getInitialTheme = () => {
  if (typeof window === 'undefined') return DARK_THEME;
  
  const savedTheme = localStorage.getItem(THEME_KEY);
  if (savedTheme === DARK_THEME || savedTheme === LIGHT_THEME) {
    return savedTheme;
  }
  
  // Check system preference
  const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
  return prefersDark ? DARK_THEME : LIGHT_THEME;
};

const currentTheme = ref(getInitialTheme());

// Apply theme to document
const applyTheme = (theme) => {
  if (typeof document === 'undefined') return;
  
  const root = document.documentElement;
  if (theme === DARK_THEME) {
    root.classList.add('dark');
    root.classList.remove('light');
  } else {
    root.classList.add('light');
    root.classList.remove('dark');
  }
};

// Initialize theme on load - run immediately when module loads
if (typeof window !== 'undefined' && typeof document !== 'undefined') {
  // Apply theme immediately when module is loaded
  const initTheme = () => {
    const savedTheme = localStorage.getItem(THEME_KEY);
    if (savedTheme === DARK_THEME || savedTheme === LIGHT_THEME) {
      currentTheme.value = savedTheme;
    } else {
      // Check system preference
      const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
      currentTheme.value = prefersDark ? DARK_THEME : LIGHT_THEME;
    }
    applyTheme(currentTheme.value);
  };
  
  // Apply immediately
  initTheme();
  
  // Also apply on DOM ready if not already applied
  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => {
      applyTheme(currentTheme.value);
    });
  } else {
    applyTheme(currentTheme.value);
  }
  
  // Watch for system theme changes (only if no saved preference)
  const mediaQuery = window.matchMedia('(prefers-color-scheme: dark)');
  const handleSystemThemeChange = (e) => {
    if (!localStorage.getItem(THEME_KEY)) {
      currentTheme.value = e.matches ? DARK_THEME : LIGHT_THEME;
      applyTheme(currentTheme.value);
    }
  };
  mediaQuery.addEventListener('change', handleSystemThemeChange);
}

export function useTheme() {
  // Toggle between dark and light
  const toggleTheme = () => {
    const newTheme = currentTheme.value === DARK_THEME ? LIGHT_THEME : DARK_THEME;
    currentTheme.value = newTheme;
    localStorage.setItem(THEME_KEY, newTheme);
    applyTheme(newTheme);
    console.log('Theme toggled to:', newTheme);
    console.log('HTML classes:', document.documentElement.classList.toString());
    console.log('CSS variables:', {
      bgPrimary: getComputedStyle(document.documentElement).getPropertyValue('--bg-primary'),
      textPrimary: getComputedStyle(document.documentElement).getPropertyValue('--text-primary'),
    });
  };

  // Set specific theme
  const setTheme = (theme) => {
    if (theme === DARK_THEME || theme === LIGHT_THEME) {
      currentTheme.value = theme;
      localStorage.setItem(THEME_KEY, theme);
      applyTheme(theme);
    }
  };

  return {
    currentTheme: computed(() => currentTheme.value),
    toggleTheme,
    setTheme,
    isDark: computed(() => currentTheme.value === DARK_THEME),
    isLight: computed(() => currentTheme.value === LIGHT_THEME),
  };
}


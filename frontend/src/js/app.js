/**
 * First we will load all of this project's JavaScript dependencies which
 * includes Vue and other libraries. It is a great starting point when
 * building robust, powerful web applications using Vue and Laravel.
 */

import './bootstrap';
import { createApp } from 'vue';
import AppWrapper from './components/AppWrapper.vue';
import 'bootstrap/dist/css/bootstrap.min.css';
import '../css/app.css'; // Import app.css for theme variables
import VueApexCharts from 'vue3-apexcharts';
// Import theme initialization early
import { useTheme } from './composables/useTheme';

/**
 * Initialize theme before mounting app
 */
const { setTheme } = useTheme();
const savedTheme = localStorage.getItem('app-theme') || 'dark';
setTheme(savedTheme);

/**
 * Create Vue application instance with AppWrapper
 * AppWrapper will handle authentication check and redirect
 */
const app = createApp(AppWrapper);
app.use(VueApexCharts);
app.mount('#app');

//import ExampleComponent from './components/ExampleComponent.vue';
//app.component('example-component', ExampleComponent);

/**
 * The following block of code may be used to automatically register your
 * Vue components. It will recursively scan this directory for the Vue
 * components and automatically register them with their "basename".
 *
 * Eg. ./components/ExampleComponent.vue -> <example-component></example-component>
 */

// Object.entries(import.meta.glob('./**/*.vue', { eager: true })).forEach(([path, definition]) => {
//     app.component(path.split('/').pop().replace(/\.\w+$/, ''), definition.default);
// });

/**
 * Finally, we will attach the application instance to a HTML element with
 * an "id" attribute of "app". This element is included with the "auth"
 * scaffolding. Otherwise, you will need to add an element yourself.
 */

//app.mount('#app');

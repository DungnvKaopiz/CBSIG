/**
 * About page entry point
 */

import './bootstrap';
import { createApp } from 'vue';
import ExampleComponent from './components/ExampleComponent.vue';
import 'bootstrap/dist/css/bootstrap.min.css';

// Mount ExampleComponent to #example-component
createApp(ExampleComponent).mount('#example-component');


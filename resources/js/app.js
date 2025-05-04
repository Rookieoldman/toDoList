// Asegúrate de que tu app.js se parezca a esto:
import './bootstrap';
import { createApp } from 'vue'; // Importa Vue
import TaskList from './components/TaskList.vue'; // Importa tu componente

alert('¡app.js cargado!');
const app = createApp({});
app.component('task-list', TaskList);
app.mount('#app');
<template>
    <div class="task-list-container">
        <h1>Mi Lista de Tareas (Vue)</h1>

        <!-- Formulario para añadir nueva tarea -->
        <!-- @submit.prevent evita que la página se recargue al enviar el formulario -->
        <form @submit.prevent="addTask" class="add-task-form">
            <input type="text" v-model="newTaskTitle" placeholder="Añadir nueva tarea..." required />
            <button type="submit">Añadir</button>
        </form>

        <!-- Mensaje de carga/estado -->
        <p>{{ message }}</p>

        <!-- Lista de tareas -->
        <ul v-if="tasks.length > 0" class="task-list">
            <li v-for="task in tasks" :key="task.id">
                <span>{{ task.title }}</span>
            </li>
        </ul>

    </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'; // <-- Añade onMounted
import axios from 'axios';          // <-- Importa axios

console.log("Componente TaskList cargado!");

// Estado reactivo para almacenar las tareas
const tasks = ref([]); // Inicializamos como un array vacío

// Estado para el mensaje (podemos quitarlo o dejarlo por ahora)
const message = ref('Cargando tareas...');
const newTaskTitle = ref('');

// Función para obtener las tareas de la API
const fetchTasks = async () => {
    console.log("Fetching tasks from API...");
    try {
        // Hacemos una petición GET a nuestro endpoint de Laravel
        const response = await axios.get('/api/tasks');
        // Actualizamos nuestro estado reactivo 'tasks' con los datos recibidos
        tasks.value = response.data;
        console.log("Tasks fetched:", tasks.value);
        message.value = 'Lista de tareas:'; // Cambiamos el mensaje
        if (tasks.value.length === 0) {
            message.value = 'No hay tareas pendientes.';
        }
    } catch (error) {
        console.error("Error fetching tasks:", error);
        message.value = 'Error al cargar las tareas.';
        // Aquí podríamos mostrar un mensaje de error más elaborado al usuario
    }
};
// Función para añadir una nueva tarea
const addTask = async () => {
    // Validación simple: no añadir si el título está vacío (quitando espacios)
    if (newTaskTitle.value.trim() === '') {
        alert('Por favor, introduce un título para la tarea.');
        return; // Detiene la ejecución de la función
    }

    console.log('Adding task:', newTaskTitle.value);
    message.value = 'Añadiendo tarea...'; // Mensaje de feedback

    try {
        // Hacemos la petición POST a la API de Laravel
        // Enviamos un objeto con la propiedad 'title'
        const response = await axios.post('/api/tasks', {
            title: newTaskTitle.value
        });

        // Si la API responde correctamente (código 201 y la tarea creada):
        // Añadimos la nueva tarea (devuelta por la API) al INICIO del array local 'tasks'.
        // Usar unshift() hace que las nuevas tareas aparezcan arriba.
        tasks.value.unshift(response.data);

        // Limpiamos el input (gracias a v-model, esto borrará el texto del input)
        newTaskTitle.value = '';
        message.value = ''; // Limpiamos el mensaje de estado

    } catch (error) {
        console.error("Error adding task:", error);
        message.value = 'Error al añadir la tarea.';
        // Podríamos inspeccionar 'error.response.data' si Laravel envía errores de validación específicos
        if (error.response && error.response.data && error.response.data.message) {
            alert(`Error: ${error.response.data.message}`);
        } else {
            alert('Ocurrió un error inesperado al añadir la tarea.');
        }
    }
};

// Hook del ciclo de vida: se ejecuta cuando el componente se monta en el DOM
onMounted(() => {
    fetchTasks(); // Llamamos a la función para cargar las tareas
});

</script>


<style scoped>
/* Los estilos aquí solo se aplicarán a ESTE componente */
.task-list-container {
    max-width: 600px;
    margin: 2rem auto;
    padding: 1.5rem;
    background-color: #f9f9f9;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

h1 {
    color: #333;
    text-align: center;
    margin-bottom: 1.5rem;
}

.add-task-form {
    display: flex;
    margin-bottom: 1.5rem;
    /* Espacio antes del mensaje/lista */
}

.add-task-form input[type="text"] {
    flex-grow: 1;
    /* El input ocupa el espacio disponible */
    padding: 0.6rem;
    border: 1px solid #ccc;
    border-radius: 4px 0 0 4px;
    /* Bordes redondeados a la izquierda */
}

.add-task-form button {
    padding: 0.6rem 1rem;
    background-color: #4CAF50;
    /* Verde */
    color: white;
    border: none;
    border-radius: 0 4px 4px 0;
    /* Bordes redondeados a la derecha */
    cursor: pointer;
    transition: background-color 0.2s;
}

.add-task-form button:hover {
    background-color: #45a049;
}
</style>
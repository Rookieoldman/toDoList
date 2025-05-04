<template>
    <div class="task-list-container">
        <h1>Mi Lista de Tareas (Vue)</h1>

        <form @submit.prevent="addTask" class="add-task-form">
            <input type="text" v-model="newTaskTitle" placeholder="Añadir nueva tarea..." required />
            <button type="submit">Añadir</button>
        </form>

        <p v-if="loading">Cargando tareas...</p>
        <p v-else-if="error" class="error-message">{{ error }}</p>
        <p v-else-if="tasks.length === 0">No hay tareas pendientes.</p>

        <!-- Usamos el nuevo componente en el v-for -->
        <div v-else class="tasks-container">
            <TaskItem
                v-for="task in tasks"
                :key="task.id"
                :task="task"
                @toggle-complete="toggleComplete"
                @delete-task="deleteTask"
                @update-task-title="handleUpdateTitle"  
            />
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import axios from 'axios';
import TaskItem from './TaskItem.vue';

const tasks = ref([]);
const newTaskTitle = ref('');
const loading = ref(true);
const error = ref(null);

// --- FUNCIONES EXISTENTES (sin cambios en su lógica interna por ahora) ---
const fetchTasks = async () => {
    loading.value = true;
    error.value = null;
    console.log("Fetching tasks from API...");
    try {
        const response = await axios.get('/api/tasks');
        tasks.value = response.data;
        console.log("Tasks fetched:", tasks.value);
    } catch (err) {
        console.error("Error fetching tasks:", err);
        error.value = 'Error al cargar las tareas.';
    } finally {
        loading.value = false;
    }
};

const addTask = async () => {
    if (newTaskTitle.value.trim() === '') {
        alert('Por favor, introduce un título para la tarea.');
        return;
    }
    error.value = null;
    console.log('Adding task:', newTaskTitle.value);
    try {
        const response = await axios.post('/api/tasks', {
            title: newTaskTitle.value
        });
        tasks.value.unshift(response.data);
        newTaskTitle.value = '';
    } catch (err) {
        console.error("Error adding task:", err);
        error.value = 'Error al añadir la tarea.';
        if (err.response && err.response.data && err.response.data.message) {
            alert(`Error: ${err.response.data.message}`);
        } else {
            alert('Ocurrió un error inesperado al añadir la tarea.');
        }
    }
};

const toggleComplete = async (taskToToggle) => {
    console.log('Handling toggle-complete event for task:', taskToToggle.id);
    error.value = null;
    const taskIndex = tasks.value.findIndex(t => t.id === taskToToggle.id); // Usamos findIndex
    if (taskIndex === -1) return;

    const originalTask = tasks.value[taskIndex]; // Obtenemos la tarea del array
    const originalStatus = originalTask.is_complete;
    originalTask.is_complete = !originalTask.is_complete; // Actualización optimista

    try {
        // Asegúrate de enviar el ID correcto
        await axios.put(`/api/tasks/${originalTask.id}`, {
            is_complete: originalTask.is_complete
        });
        console.log('Task updated successfully on server');
        // Opcional: refrescar la tarea específica con la respuesta si la API devuelve algo útil
        // const response = await axios.put...
        // tasks.value[taskIndex] = response.data;
    } catch (err) {
        console.error("Error updating task:", err);
        error.value = 'Error al actualizar la tarea.';
        originalTask.is_complete = originalStatus; // Revertir
        alert('No se pudo actualizar la tarea en el servidor.');
    }
};

const deleteTask = async (taskToDelete) => {
    console.log('Handling delete-task event for task:', taskToDelete.id);
    error.value = null;
    // Añadimos confirmación aquí por si acaso
     if (!confirm(`¿Seguro que quieres borrar la tarea "${taskToDelete.title}"?`)) {
         return;
     }
    try {
        await axios.delete(`/api/tasks/${taskToDelete.id}`);
        tasks.value = tasks.value.filter(t => t.id !== taskToDelete.id);
        console.log('Task deleted successfully');
    } catch (err) {
        console.error("Error deleting task:", err);
        error.value = 'Error al borrar la tarea.';
        alert('No se pudo borrar la tarea en el servidor.');
    }
};

// --- FUNCIÓN QUE FALTABA ---
const handleUpdateTitle = async (taskId, newTitle) => {
    console.log(`TaskList: Handling update-task-title. ID: ${taskId}, Title: "${newTitle}"`);
    error.value = null;
    const taskIndex = tasks.value.findIndex(t => t.id === taskId);
    if (taskIndex === -1) {
        console.error("Task not found for update!");
        return;
    }

    // Guardamos el título original por si falla la API
    const originalTitle = tasks.value[taskIndex].title;

    try {
        // Llamamos a la API para actualizar SOLO el título
        const response = await axios.put(`/api/tasks/${taskId}`, {
            title: newTitle
        });

        // Actualizamos TODO el objeto tarea en nuestro array local con la respuesta de la API
        // Esto asegura que tenemos los datos más recientes (incluyendo updated_at si cambió)
        tasks.value[taskIndex] = response.data;

        console.log('TaskList: Local task updated with API response:', tasks.value[taskIndex]);

    } catch (err) {
        console.error("Error updating task title:", err);
        error.value = 'Error al actualizar el título.';
        // Opcional: podríamos revertir si quisiéramos, pero como no hicimos update optimista
        // para el título, no es estrictamente necesario, aunque podríamos mostrar el error.
        alert('No se pudo actualizar el título de la tarea en el servidor.');
        // Si quisiéramos revertir visualmente (aunque no es lo ideal sin update optimista):
        // tasks.value[taskIndex].title = originalTitle;
    }
};
// --------------------------

onMounted(() => {
    fetchTasks();
});

</script>

<style scoped>
/* Quitamos los estilos de 'ul' y 'li' que ahora están en TaskItem.vue */
.task-list-container {
    max-width: 700px;
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
}

.add-task-form input[type="text"] {
    flex-grow: 1;
    padding: 0.6rem;
    border: 1px solid #ccc;
    border-radius: 4px 0 0 4px;
}

.add-task-form button {
    padding: 0.6rem 1rem;
    background-color: #4CAF50;
    color: white;
    border: none;
    border-radius: 0 4px 4px 0;
    cursor: pointer;
    transition: background-color 0.2s;
}

.add-task-form button:hover {
    background-color: #45a049;
}

.error-message {
    color: #d32f2f; /* Rojo */
    text-align: center;
    margin: 1rem 0;
}

.tasks-container {
    margin-top: 1.5rem;
}
</style>
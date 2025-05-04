<template>
    <div class="task-list-container">
        <h1>La Meva Llista de Tasques (Vue)</h1>

        <!-- Formulari per afegir una nova tasca -->
        <form @submit.prevent="addTask" class="add-task-form">
            <input type="text" v-model="newTaskTitle" placeholder="Afegir nova tasca..." required />
            <button type="submit">Afegir</button>
        </form>

        <!-- Missatges d'estat (càrrega, error, llista buida) -->
        <p v-if="loading">Carregant tasques...</p>
        <p v-else-if="error" class="error-message">{{ error }}</p>
        <p v-else-if="tasks.length === 0">No hi ha tasques pendents.</p>

        <!-- Contenidor per a les tasques, utilitzant el component TaskItem -->
        <div v-else class="tasks-container">
            <TaskItem v-for="task in tasks" :key="task.id" :task="task" @toggle-complete="toggleComplete"
                @delete-task="deleteTask" @update-task-title="handleUpdateTitle" />
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'; // Importem funcions necessàries de Vue
import axios from 'axios';             // Importem Axios per a les crides HTTP
import TaskItem from './TaskItem.vue'; // Importem el component fill per a cada tasca

// --- ESTAT REACTIU DEL COMPONENT ---

// Array reactiu per emmagatzemar la llista de tasques obtingudes de l'API
const tasks = ref([]);
// Variable reactiva per enllaçar (v-model) amb l'input del formulari d'afegir tasca
const newTaskTitle = ref('');
// Variable reactiva per indicar si s'estan carregant les dades inicials
const loading = ref(true);
// Variable reactiva per emmagatzemar possibles missatges d'error de l'API
const error = ref(null);

// --- FUNCIONS PER INTERACTUAR AMB L'API ---

/**
 * Funció asíncrona per obtenir totes les tasques de l'API (/api/tasks).
 * S'executa quan el component es munta.
 */
const fetchTasks = async () => {
    loading.value = true; // Indiquem que comença la càrrega
    error.value = null;   // Resetejem errors previs
    console.log("Obtenint tasques de l'API...");
    try {
        // Fem la petició GET a l'endpoint de Laravel
        const response = await axios.get('/api/tasks');
        // Actualitzem l'array reactiu 'tasks' amb les dades rebudes
        tasks.value = response.data;
        console.log("Tasques obtingudes:", tasks.value);
    } catch (err) {
        // Si hi ha un error durant la petició
        console.error("Error obtenint tasques:", err);
        error.value = 'Error al carregar les tasques.'; // Guardem el missatge d'error
    } finally {
        // Aquesta part s'executa sempre, tant si hi ha èxit com error
        loading.value = false; // Indiquem que la càrrega ha finalitzat
    }
};

/**
 * Funció asíncrona per afegir una nova tasca.
 * Es crida en fer 'submit' al formulari.
 */
const addTask = async () => {
    // Validació simple per no afegir tasques buides
    if (newTaskTitle.value.trim() === '') {
        alert('Si us plau, introdueix un títol per a la tasca.');
        return; // Aturem l'execució
    }
    error.value = null; // Resetejem errors previs
    console.log('Afegint tasca:', newTaskTitle.value);
    try {
        // Fem la petició POST a l'API enviant el títol
        const response = await axios.post('/api/tasks', {
            title: newTaskTitle.value
        });
        // Afegim la nova tasca (retornada per l'API) al principi de l'array local
        tasks.value.unshift(response.data);
        // Buidem l'input del formulari (gràcies a v-model)
        newTaskTitle.value = '';
    } catch (err) {
        // Si hi ha un error durant la petició POST
        console.error("Error afegint tasca:", err);
        error.value = 'Error al afegir la tasca.'; // Guardem missatge d'error
        // Mostrem una alerta més específica si és possible
        if (err.response && err.response.data && err.response.data.message) {
            alert(`Error: ${err.response.data.message}`);
        } else {
            alert('Ha ocorregut un error inesperat al afegir la tasca.');
        }
    }
};

/**
 * Funció asíncrona per canviar l'estat completat/incomplet d'una tasca.
 * Es crida quan el component TaskItem emet l'event 'toggle-complete'.
 * @param {Object} taskToToggle - L'objecte de la tasca que s'ha de modificar.
 */
const toggleComplete = async (taskToToggle) => {
    console.log('Gestionant event toggle-complete per a la tasca:', taskToToggle.id);
    error.value = null; // Resetejem errors previs
    // Trobem l'índex de la tasca a l'array local
    const taskIndex = tasks.value.findIndex(t => t.id === taskToToggle.id);
    if (taskIndex === -1) return; // Mesura de seguretat

    const originalTask = tasks.value[taskIndex]; // Obtenim la referència a la tasca a l'array
    const originalStatus = originalTask.is_complete; // Guardem l'estat original

    // --- Actualització Optimista ---
    // Canviem l'estat localment *abans* de la crida a l'API per una millor UX
    originalTask.is_complete = !originalTask.is_complete;

    try {
        // Fem la petició PUT a l'API amb el nou estat
        await axios.put(`/api/tasks/${originalTask.id}`, {
            is_complete: originalTask.is_complete // Enviem el nou estat (ja canviat localment)
        });
        console.log('Tasca actualitzada correctament al servidor');
        // No cal actualitzar l'estat local de nou, ja ho hem fet amb l'actualització optimista
    } catch (err) {
        // Si la crida a l'API falla
        console.error("Error actualitzant tasca:", err);
        error.value = 'Error al actualitzar la tasca.';
        // Revertim el canvi local a l'estat original
        originalTask.is_complete = originalStatus;
        alert('No s\'ha pogut actualitzar la tasca al servidor.');
    }
};

/**
 * Funció asíncrona per esborrar una tasca.
 * Es crida quan el component TaskItem emet l'event 'delete-task'.
 * @param {Object} taskToDelete - L'objecte de la tasca que s'ha d'esborrar.
 */
const deleteTask = async (taskToDelete) => {
    console.log('Gestionant event delete-task per a la tasca:', taskToDelete.id);
    error.value = null; // Resetejem errors previs

    // Demanem confirmació a l'usuari
    if (!confirm(`Segur que vols esborrar la tasca "${taskToDelete.title}"?`)) {
        return; // L'usuari ha cancel·lat
    }
    try {
        // Fem la petició DELETE a l'API
        await axios.delete(`/api/tasks/${taskToDelete.id}`);
        // Filtrem l'array local per eliminar la tasca esborrada
        tasks.value = tasks.value.filter(t => t.id !== taskToDelete.id);
        console.log('Tasca esborrada correctament');
    } catch (err) {
        // Si hi ha un error durant la petició DELETE
        console.error("Error esborrant tasca:", err);
        error.value = 'Error al esborrar la tasca.';
        // Recomanat canviar a cometes dobles per llegibilitat:
        alert("No s'ha pogut esborrar la tasca al servidor.");

    }
};

/**
 * Funció asíncrona per actualitzar el títol d'una tasca.
 * Es crida quan el component TaskItem emet l'event 'update-task-title'.
 * @param {number} taskId - L'ID de la tasca a actualitzar.
 * @param {string} newTitle - El nou títol per a la tasca.
 */
const handleUpdateTitle = async (taskId, newTitle) => {
    console.log(`TaskList: Gestionant update-task-title. ID: ${taskId}, Títol: "${newTitle}"`);
    error.value = null; // Resetejem errors previs
    // Trobem l'índex de la tasca a l'array local
    const taskIndex = tasks.value.findIndex(t => t.id === taskId);
    if (taskIndex === -1) {
        console.error("Tasca no trobada per actualitzar!");
        return; // Mesura de seguretat
    }

    // Podríem guardar el títol original aquí per revertir si cal
    // const originalTitle = tasks.value[taskIndex].title;

    try {
        // Fem la petició PUT a l'API enviant només el nou títol
        const response = await axios.put("/api/tasks/${taskId}", {
            title: newTitle
        });

        // Actualitzem *tot* l'objecte tasca a l'array local amb la resposta de l'API.
        // Això és important per mantenir la coherència (p.ex., updated_at).
        tasks.value[taskIndex] = response.data;

        console.log("TaskList: Estat local de la tasca actualitzat amb la resposta de l'API: ", tasks.value[taskIndex]);

    } catch (err) {
        // Si la crida a l'API falla
        console.error("Error actualitzant el títol de la tasca:", err);

        error.value = "Error al actualitzar el títol.";

        alert("No s'ha pogut actualitzar el títol de la tasca al servidor.");
        // Aquí podríem revertir el canvi si haguéssim fet actualització optimista
    }
};

// --- HOOK DEL CICLE DE VIDA ---

/**
 * Hook onMounted: S'executa un cop el component s'ha muntat al DOM.
 * És el lloc ideal per fer crides inicials a l'API per obtenir dades.
 */
onMounted(() => {
    fetchTasks(); // Cridem la funció per obtenir les tasques inicials
});

</script>

<style scoped>
/* Estils del component TaskList (contenidor, formulari, missatges) */
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
    color: #d32f2f;
    /* Vermell */
    text-align: center;
    margin: 1rem 0;
}

.tasks-container {
    margin-top: 1.5rem;
}
</style>
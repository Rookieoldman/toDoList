<template>
    <div class="task-card" :class="{ completed: task.is_complete }">
        <!-- Contingut: Mostra el títol O l'input d'edició -->
        <div class="task-content">
            <span v-if="!isEditing" class="task-title">{{ task.title }}</span>
            <input v-else ref="editInput" type="text" v-model="editingTitle" class="edit-input" @keyup.enter="saveEdit"
                @keyup.esc="cancelEdit" @blur="saveEdit" />
        </div>
        <!-- Accions: Mostra botons diferents segons el mode -->
        <div class="task-actions">
            <template v-if="!isEditing">
                <button @click="handleToggle" class="btn-toggle">
                    {{ toggleButtonText }}
                </button>
                <button @click="startEditing" class="btn-edit">Edit</button>
                <button @click="handleDelete" class="btn-delete">×</button>
            </template>
            <template v-else>
                <button @click="saveEdit" class="btn-save">Save</button>
                <button @click="cancelEdit" class="btn-cancel">Cancel</button>
            </template>
        </div>
    </div>
</template>

<script setup>
// Importem les funcions necessàries de Vue: ref per a l'estat reactiu,
// computed per a propietats calculades, i nextTick per esperar actualitzacions del DOM.
import { ref, computed, nextTick } from 'vue';

// --- PROPS REBUDES DEL COMPONENT PARE ---
// Definim les 'props' que aquest component espera rebre.
// En aquest cas, un objecte 'task' que és obligatori.
const props = defineProps({
    task: { type: Object, required: true }
});

// --- EVENTS QUE EL COMPONENT POT EMETRE ---
// Definim els noms dels events personalitzats que aquest component pot emetre
// cap al component pare (TaskList.vue).
const emit = defineEmits(['toggle-complete', 'delete-task', 'update-task-title']);

// --- ESTAT REACTIU LOCAL PER A L'EDICIÓ ---
// Variable reactiva per controlar si la tasca està en mode d'edició (true/false).
const isEditing = ref(false);
// Variable reactiva per emmagatzemar el títol mentre s'està editant.
const editingTitle = ref('');
// Variable reactiva per obtenir una referència directa a l'element <input> d'edició.
const editInput = ref(null);

// --- FUNCIONS MANEJADORES D'EVENTS (EMISSIONS AL PARE) ---

/**
 * Funció que s'executa en fer clic al botó 'Done'/'Undo'.
 * Emet l'event 'toggle-complete' cap al pare, passant l'objecte 'task' actual.
 */
const handleToggle = () => { emit('toggle-complete', props.task); };

/**
 * Funció que s'executa en fer clic al botó d'esborrar ('×').
 * Emet l'event 'delete-task' cap al pare, passant l'objecte 'task' actual.
 * (La confirmació podria estar aquí o al component pare).
 */
const handleDelete = () => { emit('delete-task', props.task); };

// --- FUNCIONS PER GESTIONAR L'EDICIÓ INLINE ---

/**
 * Funció que s'executa en fer clic al botó 'Edit'.
 * Activa el mode d'edició, copia el títol actual a 'editingTitle',
 * i posa el focus a l'input d'edició (utilitzant nextTick).
 */
const startEditing = () => {
    isEditing.value = true; // Activem el mode edició
    editingTitle.value = props.task.title; // Copiem el títol actual a la variable d'edició
    // nextTick s'assegura que el DOM s'ha actualitzat (l'input ara és visible)
    // abans d'intentar posar-hi el focus.
    nextTick(() => {
        // Usem optional chaining (?.) per seguretat si editInput.value fos null.
        editInput.value?.focus();
    });
};

/**
 * Funció que s'executa en fer clic a 'Cancel' o prémer 'Escape' durant l'edició.
 * Desactiva el mode d'edició sense guardar canvis.
 */
const cancelEdit = () => {
    isEditing.value = false; // Desactivem el mode edició
};

/**
 * Funció que s'executa en fer clic a 'Save', prémer 'Enter' o perdre el focus de l'input.
 * Valida el nou títol, i si és vàlid i diferent de l'original,
 * emet l'event 'update-task-title' cap al pare amb l'ID i el nou títol.
 * Finalment, desactiva el mode d'edició.
 */
const saveEdit = () => {
    // Obtenim el nou títol sense espais al principi/final.
    const newTitle = editingTitle.value.trim();
    // Validació: no permetem títols buits.
    if (newTitle === '') {
        alert('El títol no pot estar buit.');
        // Tornem a posar el focus a l'input per facilitar la correcció.
        nextTick(() => { editInput.value?.focus(); });
        return; // Aturem l'execució
    }
    // Si el títol no ha canviat, simplement cancelem l'edició.
    if (newTitle === props.task.title) {
        cancelEdit();
        return; // Aturem l'execució
    }

    // Si tot és correcte, emetem l'event cap al component pare (TaskList).
    console.log('TaskItem: Emetent update-task-title amb ID:', props.task.id, 'i Títol:', newTitle);
    emit('update-task-title', props.task.id, newTitle); // Enviem ID i nou títol
    isEditing.value = false; // Desactivem el mode edició
};

// --- PROPIETAT COMPUTADA ---

/**
 * Propietat computada que determina el text del botó 'toggle'.
 * Retorna 'Undo' si la tasca està completada, i 'Done' si no ho està.
 * Es recalcula automàticament quan props.task.is_complete canvia.
 */
const toggleButtonText = computed(() => props.task.is_complete ? 'Undo' : 'Done');
</script>

<style scoped>
/* Estils específics del component TaskItem (card, títol, botons, input d'edició) */
.task-card {
    background-color: #ffffff;
    border: 1px solid #e0e0e0;
    border-radius: 6px;
    padding: 1rem;
    margin-bottom: 1rem;
    display: flex;
    justify-content: space-between;
    align-items: center;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
    transition: background-color 0.3s, border-color 0.3s;
}

.task-card.completed {
    background-color: #f1f8e9; /* Verd pàl·lid */
    border-color: #dcedc8;
}

.task-content {
    flex-grow: 1; /* Ocupa l'espai disponible */
    margin-right: 1rem; /* Marge abans dels botons */
    min-width: 0; /* Assegura que l'input no desbordi */
}

.task-title {
    font-size: 1.1em;
    color: #333;
    transition: color 0.3s;
}

.task-card.completed .task-title {
    text-decoration: line-through; /* Ratllat per a tasques completades */
    color: #888; /* Color gris per a tasques completades */
}

.task-actions {
    display: flex; /* Botons en línia */
    align-items: center; /* Alineació vertical */
    gap: 0.5rem; /* Espai entre botons */
}

.task-actions button {
    background: none; /* Sense fons per defecte */
    border: 1px solid #ccc; /* Vora subtil */
    cursor: pointer;
    padding: 0.3rem 0.7rem;
    border-radius: 4px;
    font-size: 0.9em;
    transition: background-color 0.2s, border-color 0.2s; /* Transicions suaus */
}

.task-actions button:hover {
    background-color: #f5f5f5; /* Lleuger canvi de fons al passar per sobre */
}

.btn-toggle {
     color: #4CAF50; /* Verd */
     border-color: #4CAF50;
}

.btn-edit {
    color: #1976d2; /* Blau */
    border-color: #1976d2;
}

.btn-edit:hover {
    background-color: #e3f2fd; /* Blau clar al passar per sobre */
}

.btn-delete {
    color: #d32f2f; /* Vermell */
    border-color: transparent; /* Sense vora per defecte */
    font-weight: bold;
    font-size: 1.4em; /* Més gran per al símbol '×' */
    padding: 0 0.5rem; /* Ajust de padding */
    line-height: 1; /* Ajust vertical */
}

.btn-delete:hover {
    background-color: #ffebee; /* Vermell clar al passar per sobre */
    border-color: transparent;
}

.edit-input {
    width: 100%; /* Ocupa tot l'ample disponible dins del seu contenidor */
    padding: 0.4rem 0.6rem;
    font-size: 1.05em; /* Mida similar al títol */
    border: 1px solid #1976d2; /* Vora blava per indicar edició */
    border-radius: 4px;
    box-sizing: border-box; /* El padding no afecta l'ample total */
}

.btn-save {
    color: #388e3c; /* Verd fosc */
    border-color: #388e3c;
}

.btn-save:hover {
    background-color: #e8f5e9; /* Verd molt clar */
}

.btn-cancel {
    color: #555; /* Gris fosc */
    border-color: #ccc;
}

.btn-cancel:hover {
    background-color: #f5f5f5; /* Gris clar */
}
</style>
<template>
    <div class="task-card" :class="{ completed: task.is_complete }">
        <!-- Contenido: Muestra el título O el input de edición -->
        <div class="task-content">
            <span v-if="!isEditing" class="task-title">{{ task.title }}</span>
            <input v-else ref="editInput" type="text" v-model="editingTitle" class="edit-input" @keyup.enter="saveEdit"
                @keyup.esc="cancelEdit" @blur="saveEdit" />
        </div>
        <!-- Acciones: Muestra botones diferentes según el modo -->
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
import { ref, computed, nextTick } from 'vue';

const props = defineProps({
    task: { type: Object, required: true }
});
const emit = defineEmits(['toggle-complete', 'delete-task', 'update-task-title']);

const isEditing = ref(false);
const editingTitle = ref('');
const editInput = ref(null);

const handleToggle = () => { emit('toggle-complete', props.task); };
const handleDelete = () => { emit('delete-task', props.task); };

const startEditing = () => {
    isEditing.value = true;
    editingTitle.value = props.task.title;
    nextTick(() => {
        editInput.value?.focus();
    });
};

const cancelEdit = () => {
    isEditing.value = false;
};

const saveEdit = () => {
    const newTitle = editingTitle.value.trim();
    if (newTitle === '') {
        alert('El título no puede estar vacío.');
        nextTick(() => { editInput.value?.focus(); });
        return;
    }
    if (newTitle === props.task.title) {
        cancelEdit();
        return;
    }
    // Log añadido para depuración
    console.log('TaskItem: Emitting update-task-title with ID:', props.task.id, 'and Title:', newTitle);
    emit('update-task-title', props.task.id, newTitle);
    isEditing.value = false;
};

const toggleButtonText = computed(() => props.task.is_complete ? 'Undo' : 'Done');
</script>

<style scoped>
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
    background-color: #f1f8e9;
    border-color: #dcedc8;
}

.task-content {
    flex-grow: 1;
    margin-right: 1rem;
    min-width: 0; /* Asegura que el input no desborde */
}

.task-title {
    font-size: 1.1em;
    color: #333;
    transition: color 0.3s;
}

.task-card.completed .task-title {
    text-decoration: line-through;
    color: #888;
}

.task-actions {
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.task-actions button {
    background: none;
    border: 1px solid #ccc;
    cursor: pointer;
    padding: 0.3rem 0.7rem;
    border-radius: 4px;
    font-size: 0.9em;
    transition: background-color 0.2s, border-color 0.2s;
}

.task-actions button:hover {
    background-color: #f5f5f5;
}

.btn-toggle {
    /* Estilos específicos si necesitas */
     color: #4CAF50; /* Verde */
     border-color: #4CAF50;
}

.btn-edit {
    color: #1976d2; /* Azul */
    border-color: #1976d2;
}

.btn-edit:hover {
    background-color: #e3f2fd;
}

.btn-delete {
    color: #d32f2f; /* Rojo */
    border-color: transparent;
    font-weight: bold;
    font-size: 1.4em;
    padding: 0 0.5rem;
    line-height: 1;
}

.btn-delete:hover {
    background-color: #ffebee;
    border-color: transparent;
}

.edit-input {
    width: 100%;
    padding: 0.4rem 0.6rem;
    font-size: 1.05em;
    border: 1px solid #1976d2;
    border-radius: 4px;
    box-sizing: border-box;
}

.btn-save {
    color: #388e3c; /* Verde */
    border-color: #388e3c;
}

.btn-save:hover {
    background-color: #e8f5e9;
}

.btn-cancel {
    color: #555;
    border-color: #ccc;
}

.btn-cancel:hover {
    background-color: #f5f5f5;
}
</style>
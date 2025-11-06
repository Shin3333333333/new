<template>
  <div class="task-management-container">
    <div class="task-input-section">
      <input type="text" v-model="newTask" placeholder="Add a new task..." @keyup.enter="addTask">
      <input type="datetime-local" v-model="reminderTime" class="reminder-input">
      <button @click="addTask">Add Task</button>
    </div>
    <ul class="task-list">
      <li v-for="task in tasks" :key="task.id">
        <span>{{ task.text }}</span>
        <button @click="removeTask(task)">Remove</button>
      </li>
    </ul>
  </div>
</template>

<script>
import { useToast } from '../../../composables/useToast';
import axios from '../../../axios';

export default {
  name: 'TaskManagement',
  setup() {
    const { success } = useToast();
    return { success };
  },
  data() {
    return {
      newTask: '',
      reminderTime: '',
      tasks: [],
    };
  },
  mounted() {
    this.fetchTasks();
  },
  methods: {
    async fetchTasks() {
      try {
        const response = await axios.get('/tasks');
        this.tasks = response.data;
      } catch (error) {
        console.error('Error fetching tasks:', error);
      }
    },
    async addTask() {
      if (this.newTask.trim() !== '') {
        try {
          const response = await axios.post('/tasks', {
            text: this.newTask,
            reminder_time: this.reminderTime,
          });
          this.tasks.push(response.data);
          this.newTask = '';
          this.reminderTime = '';
        } catch (error) {
          console.error('Error adding task:', error);
        }
      }
    },
    async removeTask(task) {
      try {
        await axios.delete(`/tasks/${task.id}`);
        this.tasks = this.tasks.filter(t => t.id !== task.id);
      } catch (error) {
        console.error('Error removing task:', error);
      }
    },
  },
};
</script>

<style scoped>
.task-management-container {
  padding: 2rem;
  background-color: #fff;
  border-radius: 0.75rem;
  box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
}

.task-input-section {
  display: flex;
  margin-bottom: 1.5rem;
}

.task-input-section input {
  flex-grow: 1;
  padding: 0.75rem 1rem;
  border: 1px solid #e5e7eb;
  border-radius: 0.5rem 0 0 0.5rem;
  font-size: 1rem;
}

.reminder-input {
  padding: 0.75rem 1rem;
  border: 1px solid #e5e7eb;
  border-left: none;
  font-size: 1rem;
}

.task-input-section button {
  padding: 0.75rem 1.5rem;
  background-color: #3b82f6;
  color: #fff;
  border: none;
  border-radius: 0 0.5rem 0.5rem 0;
  cursor: pointer;
  font-size: 1rem;
  font-weight: 600;
}

.task-list {
  list-style: none;
  padding: 0;
}

.task-list li {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 1rem;
  border-bottom: 1px solid #e5e7eb;
}

.task-list li:last-child {
  border-bottom: none;
}

.task-list button {
  background-color: #ef4444;
  color: #fff;
  border: none;
  border-radius: 0.5rem;
  padding: 0.5rem 1rem;
  cursor: pointer;
  font-weight: 600;
}
</style>
<template>
  <div class="schedule-history-container">
    <h2 class="main-title">Schedule History</h2>
    <div class="controls">
      <input type="text" v-model="searchQuery" placeholder="Search schedules..." class="search-input">
      <div class="total-schedules">Total Schedules: {{ totalSchedules }}</div>
    </div>
    <div v-if="loading" class="loading-message">Loading...</div>
    <div v-else-if="error" class="error-message">{{ error }}</div>
    <div v-else>
      <div v-for="(schedules, academicYear) in groupedSchedules" :key="academicYear" class="academic-year-group">
        <h3 @click="toggleYear(academicYear)" class="collapsible-header">
          <span>{{ academicYear }}</span>
          <div class="header-details">
            <span class="yearly-total">Total Schedules: {{ getYearlyTotal(schedules) }}</span>
            <span class="yearly-units">Total Units: {{ getYearlyUnits(schedules) }}</span>
            <span class="arrow">{{ yearVisibility[academicYear] ? '&#9660;' : '&#9658;' }}</span>
          </div>
        </h3>
        <div v-if="yearVisibility[academicYear]">
          <div v-for="(semesterSchedules, semester) in schedules" :key="semester" class="semester-group">
            <h4 class="semester-header">
              <span>{{ semester }}</span>
              <div class="semester-details">
                <span class="semester-total">Schedules: {{ semesterSchedules.length }}</span>
                <span class="semester-units">Units: {{ getSemesterUnits(semesterSchedules) }}</span>
              </div>
            </h4>
            <table class="schedule-table">
              <thead>
                <tr>
                  <th>Subject</th>
                  <th>Time</th>
                  <th>Classroom</th>
                  <th>Course Code</th>
                  <th>Course Section</th>
                  <th>Units</th>
                  <th>Date Last Used</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="schedule in semesterSchedules" :key="schedule.id">
                  <td>{{ schedule.subject }}</td>
                  <td>{{ schedule.time }}</td>
                  <td>{{ schedule.classroom }}</td>
                  <td>{{ schedule.course_code }}</td>
                  <td>{{ schedule.course_section }}</td>
                  <td>{{ schedule.units }}</td>
                  <td>{{ formatDate(schedule.date_last_used) }}</td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import axios from '../../../axios';

export default {
  name: 'ScheduleHistory',
  data() {
    return {
      schedules: [],
      loading: true,
      error: null,
      searchQuery: '',
      yearVisibility: {},
    };
  },
  computed: {
    totalSchedules() {
      return this.schedules.length;
    },
    filteredSchedules() {
      if (!this.searchQuery) {
        return this.schedules;
      }
      const lowerCaseQuery = this.searchQuery.toLowerCase();
      return this.schedules.filter(schedule => {
        return Object.values(schedule).some(value =>
          String(value).toLowerCase().includes(lowerCaseQuery)
        );
      });
    },
    groupedSchedules() {
      return this.filteredSchedules.reduce((acc, schedule) => {
        const academicYear = schedule.academic_year || 'N/A';
        const semester = schedule.semester || 'N/A';
        if (!acc[academicYear]) {
          acc[academicYear] = {};
        }
        if (!acc[academicYear][semester]) {
          acc[academicYear][semester] = [];
        }
        acc[academicYear][semester].push(schedule);
        return acc;
      }, {});
    },
  },
  methods: {
    async fetchHistory() {
      this.loading = true;
      this.error = null;
      try {
        const userResponse = await axios.get('/user');
        const userId = userResponse.data.id;

        if (!userId) {
          this.error = 'User ID not found.';
          console.error(this.error);
          this.loading = false;
          return;
        }

        const response = await axios.get(`/faculty/${userId}/schedule-history`);
        this.schedules = response.data;
        this.initializeVisibility();
      } catch (error) {
        this.error = 'Error fetching schedule history.';
        console.error(error);
      } finally {
        this.loading = false;
      }
    },
    initializeVisibility() {
      const visibility = {};
      for (const year in this.groupedSchedules) {
        visibility[year] = true; // Initially all expanded
      }
      this.yearVisibility = visibility;
    },
    toggleYear(year) {
      this.yearVisibility[year] = !this.yearVisibility[year];
    },
    getYearlyTotal(schedules) {
      let count = 0;
      for (const semester in schedules) {
        count += schedules[semester].length;
      }
      return count;
    },
    getYearlyUnits(schedules) {
      let units = 0;
      for (const semester in schedules) {
        units += this.getSemesterUnits(schedules[semester]);
      }
      return units;
    },
    getSemesterUnits(semesterSchedules) {
      return semesterSchedules.reduce((total, schedule) => total + (schedule.units || 0), 0);
    },
    formatDate(dateString) {
      if (!dateString) return 'N/A';
      const options = { year: 'numeric', month: 'long', day: 'numeric' };
      return new Date(dateString).toLocaleDateString(undefined, options);
    }
  },
  watch: {
    '$route': 'fetchHistory',
  },
  created() {
    this.fetchHistory();
  },
};
</script>

<style scoped>
.schedule-history-container {
  padding: 2rem;
  background-color: #f9f9f9;
  font-family: sans-serif;
}

.main-title {
  text-align: center;
  margin-bottom: 2rem;
  color: #333;
}

.controls {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 2rem;
}

.search-input {
  padding: 0.5rem;
  border: 1px solid #ccc;
  border-radius: 4px;
  width: 300px;
}

.total-schedules {
  font-weight: bold;
  color: #555;
}

.loading-message, .error-message {
  text-align: center;
  font-size: 1.2rem;
  color: #888;
}

.academic-year-group {
  margin-bottom: 2rem;
  border: 1px solid #ddd;
  border-radius: 8px;
  overflow: hidden;
}

.collapsible-header {
  cursor: pointer;
  display: flex;
  justify-content: space-between;
  align-items: center;
  background-color: #e9e9e9;
  padding: 1rem;
  font-size: 1.2rem;
  color: #333;
}

.header-details {
  display: flex;
  align-items: center;
}

.yearly-total, .yearly-units {
  margin-right: 1rem;
  font-size: 0.9rem;
  color: #555;
}

.arrow {
  font-size: 1rem;
}

.semester-group {
  padding: 1rem;
}

.semester-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 1rem;
  padding-bottom: 0.5rem;
  border-bottom: 1px solid #eee;
}

.semester-details {
  display: flex;
  align-items: center;
  font-size: 0.9rem;
  color: #555;
}

.semester-total, .semester-units {
  margin-left: 1rem;
}

.schedule-table {
  width: 100%;
  border-collapse: collapse;
}

.schedule-table th, .schedule-table td {
  border: 1px solid #ddd;
  padding: 0.8rem;
  text-align: left;
}

.schedule-table th {
  background-color: #f2f2f2;
}
</style>
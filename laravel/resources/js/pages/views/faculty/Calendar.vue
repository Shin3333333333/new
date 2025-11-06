<template>
  <div class="calendar-container">
    <FullCalendar :options="calendarOptions" />
  </div>
</template>

<script>
import FullCalendar from '@fullcalendar/vue3';
import dayGridPlugin from '@fullcalendar/daygrid';
import timeGridPlugin from '@fullcalendar/timegrid';
import interactionPlugin from '@fullcalendar/interaction';
import api from '../../../axios';

export default {
  components: {
    FullCalendar,
  },
  data() {
    return {
      subjectColors: {},
      colorPalette: ['#264653', '#2a9d8f', '#e9c46a', '#f4a261', '#e76f51', '#d62828', '#023e8a', '#0077b6'],
      nextColorIndex: 0,
      calendarOptions: {
        plugins: [dayGridPlugin, timeGridPlugin, interactionPlugin],
        initialView: 'timeGridWeek',
        headerToolbar: {
          left: 'prev,next today',
          center: 'title',
          right: 'dayGridMonth,timeGridWeek,timeGridDay',
        },
        events: [],
        height: '80vh',
        slotMinTime: '06:00:00',
        eventContent: this.handleEventContent,
        slotLabelFormat: {
          hour: 'numeric',
          minute: '2-digit',
          omitZeroMinute: false,
          meridiem: 'short'
        },
        eventMinHeight: 20,
        dayHeaderFormat: {
          weekday: 'short'
        },
        dayHeaderContent: this.handleDayHeaderContent,
        eventDisplay: 'block',
        eventTextColor: '#FFFFFF'
      },
    };
  },
  created() {
    this.fetchSchedule();
  },
  methods: {
    handleDayHeaderContent(arg) {
      const today = new Date();
      const date = arg.date;
      let text = arg.text;

      if (date.getDate() === today.getDate() && date.getMonth() === today.getMonth() && date.getFullYear() === today.getFullYear()) {
        return { html: `${text} <br><span class="today-text">(Today)</span>` };
      }
      return text;
    },
    async fetchSchedule() {
      try {
        const response = await api.get('/faculty/schedule');
        console.log('Fetched schedule data:', response.data);
        this.calendarOptions.events = this.transformScheduleToEvents(response.data.schedule);
      } catch (error) {
        console.error('Error fetching schedule:', error);
      }
    },
    getSubjectColor(subject) {
      if (!this.subjectColors[subject]) {
        this.subjectColors[subject] = this.colorPalette[this.nextColorIndex];
        this.nextColorIndex = (this.nextColorIndex + 1) % this.colorPalette.length;
      }
      return this.subjectColors[subject];
    },
    transformScheduleToEvents(schedule) {
      const dayMapping = {
        'Mon': 1,
        'Tue': 2,
        'Wed': 3,
        'Thu': 4,
        'Fri': 5,
        'Sat': 6,
        'Sun': 0,
      };
      return schedule.map(item => {
        const startTime = `${String(item.time_start).padStart(2, '0')}:00:00`;
        const endTime = `${String(item.time_end).padStart(2, '0')}:00:00`;

        return {
          title: item.subject,
          daysOfWeek: [dayMapping[item.day]],
          startTime: startTime,
          endTime: endTime,
          backgroundColor: this.getSubjectColor(item.subject),
          borderColor: this.getSubjectColor(item.subject),
          extendedProps: {
            course: item.course_section,
            classroom: item.classroom,
          },
        };
      });
    },
    handleEventContent(arg) {
      const { event } = arg;
      const { course, classroom } = event.extendedProps;
      const title = event.title;

      // Create a custom HTML structure for the event content
      const eventEl = document.createElement('div');
      eventEl.innerHTML = `
        <div class="fc-event-main-frame">
          <div class="fc-event-title-container">
            <div class="fc-event-title fc-sticky">${title}</div>
          </div>
          <div class="fc-event-body">
            <div>${course}</div>
            <div>${classroom}</div>
          </div>
        </div>
      `;
      return { domNodes: [eventEl] };
    },
  },
};
</script>

<style>
.calendar-container {
  height: 100%;
  width: 100%;
}

.fc-event {
  border: 1px solid #000; /* Add a border to the events */
  font-size: 0.8em; /* Make the font smaller */
  border-radius: 5px;
  box-shadow: 0 2px 5px rgba(0, 0, 0, 0.2);
  transition: all 0.3s ease;
  background: linear-gradient(to bottom, rgba(255, 255, 255, 0.1), rgba(0, 0, 0, 0.1));
}

.fc-event:hover {
  transform: scale(1.05);
  box-shadow: 0 5px 15px rgba(0, 0, 0, 0.3);
}

.today-text {
  font-size: 0.8em;
  font-weight: bold;
  color: #d63384;
}
</style>
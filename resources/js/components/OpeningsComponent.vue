<template>
    <div>
        <div class="card shadow mb-4">
            <div class="card-header">
                <h6 class="m-0 font-weight-bold text-primary">Öffnungszeiten</h6>
            </div>
            <div class="card-body">
                <full-calendar :event-sources="eventSources" @event-created="addEvent"
                               @event-selected="eventSelected"
                               @event-drop="updateEvent" :config="config" ref="calendar"></full-calendar>
                <div class="mt-2">
                    <input type="checkbox" id="repeatEvent" v-model="repeatEvent">
                    <label for="repeatEvent"> Eingeträge wiederholen</label>
                </div>
            </div>
        </div>

        <div class="card shadow mb-4" v-if="exceptions">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Ausnahmen</h6>
            </div>
            <div class="card-body">
                <div v-for="exception in exceptions">
                    {{ exception.exception_date }}
                    ({{ exception.start }} - {{ exception.end }})
                    <button @click="deleteException(exception)" class="btn btn-danger">X</button>
                </div>
            </div>
        </div>

        <!-- Wiederholungsevent Popup -->
        <div v-if="selectedEvent != null" id="weekly-event-modal" class="modal fade" tabindex="-1" role="dialog" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Wiederholungsevent</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <p><strong>{{ selectedEvent.title }}</strong></p>
                        <div>
                            <button v-if="" @click="setEndDate" class="btn btn-primary mt-2" data-dismiss="modal">Letzter Termin</button>
                        </div>
                        <div>
                            <button v-if=""
                                    @click="addException" class="btn btn-danger mt-2" data-dismiss="modal">Ausnahme hinzufügen</button>
                        </div>
                        <div>
                            <button v-if="" @click="deleteEvent" class="btn btn-danger mt-2" data-dismiss="modal">!!!Wiederholungsbuchung löschen!!!</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Einzelevent Popup -->
        <div v-if="selectedEvent != null" id="single-event-modal" class="modal fade" tabindex="-1" role="dialog" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Einzelevent</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <p><strong>{{ selectedEvent.title }}</strong></p>
                        <div>
                            <button v-if="" @click="deleteEvent" class="btn btn-danger mt-2" data-dismiss="modal">Einzeltermin löschen</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
    import {FullCalendar} from 'vue-full-calendar';
    import bootstrapPlugin from '@fullcalendar/bootstrap';

    import 'fullcalendar/dist/locale/de-at';
    import 'fullcalendar/dist/fullcalendar.css';
    import Datepicker from 'vuejs-datepicker';
    import {de} from 'vuejs-datepicker/dist/locale';

    import * as moment from 'moment'

    export default {
        components: {
            FullCalendar
        },
        computed: {
            selectedEventDate: function () {
                return this.selectedEvent == null ? "" : new Date(this.selectedEvent.start);
            }
        },
        name: "BlockingsComponent",
        methods: {
            refreshEvents() {
                this.$refs.calendar.$emit('refetch-events');
            },
            addEvent: function(event) {
                console.log('addEvent()', event);
                this.$refs.calendar.fireMethod('unselect');
                axios.post('/openings', {
                    start: event.start,
                    end: event.end,
                    weekly_repeat: this.repeatEvent
                }).then(response => {
                    this.refreshEvents();
                }).catch(error => {
                    console.log(error);
                    //todo: error handling
                });
            },
            eventSelected: function(event) {
                console.log('eventSelected()', event);
                this.selectedEvent = event;

                // Weekly event
                if(this.selectedEvent !== null && this.selectedEvent.weekly_repeat == 1) {
                    $("#weekly-event-modal").modal();
                }

                if(this.selectedEvent !== null && this.selectedEvent.weekly_repeat == 0) {
                    $("#single-event-modal").modal();
                }
            },
            updateEvent: function(event) {console.log('updateEvent()', event)},
            deleteEvent: function(event) {
                console.log('deleteEvent()', event);
                axios.delete('openings/delete/' + this.selectedEvent.id).then(response => {
                    this.refreshEvents();
                    this.selectedEvent = null;
                });
            },
            setEndDate: function(event) {
                console.log('setEndDate()', event);
                axios.put('openings/update/' + this.selectedEvent.id,
                    { repeat_until: moment(this.selectedEventDate).format("YYYY-MM-DD")}).then(response => {
                    this.selectedEvent = null;
                });
            },
            addException: function(e) {
                axios.put('/openings/exceptDate/' + this.selectedEvent.id, {
                    exception_date: moment(this.selectedEventDate).format("YYYY-MM-DD")
                }).then(response => {
                    this.selectedEvent = null;
                    this.refreshEvents();
                    this.getExceptions();
                })
            },
            deleteException: function(exception) {
                axios.delete('/openings/exceptions/' + exception.id).then(response => {
                    this.exceptions.splice(this.exceptions.indexOf(exception), 1);
                    this.refreshEvents();
                })
            },
            getExceptions: function() {
                axios.get('/openings/exceptions/').then(response => {
                    console.log(response);
                    this.exceptions = response.data;
                })
            },
        },
        mounted() {
            this.exceptions = this.getExceptions();
        },
        data() {
            return {
                eventSources: [
                    {
                        url: '../api/openings',
                        editable: false
                    }
                ],
                de: de,
                disabledDates: {to: new Date()},
                config: {
                    locale: 'de-at',
                    allDaySlot: false,
                    selectable: permissions['select bookings'],
                    header: {
                        left: 'prev,next today',
                        center: 'title',
                        right: 'agendaWeek,agendaDay'
                    },
                    editable: false,
                    snapDuration: '00:15:00',
                    plugins: [ bootstrapPlugin ],
                    themeSystem: 'bootstrap4',
                    eventRender: function (event, element) {
                        console.log('eventRender:', event, element);
                    }
                },
                repeatEvent: true,
                selectedEvent: null,
                exceptions: [],
            }
        },
        permissions: permissions,
        user: user,
    }
</script>

<style scoped>

</style>

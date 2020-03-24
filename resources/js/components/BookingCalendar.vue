<template>
    <div>
        <div class="card shadow mb-4">
            <div class="card-body">
                <full-calendar :event-sources="eventSources" @event-created="addEvent"
                            @event-selected="eventSelected"
                            @event-drop="updateEvent" :config="config" ref="calendar"></full-calendar>
            </div>
        </div>

        <div class="mt-3" v-if="permissions['create bookings']">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary" >
                        <span v-if="permissions['list types']">Raum buchen</span>
                        <span v-if="!permissions['list types']">Überziehung notieren</span>
                    </h6>
                </div>
                <div class="card-body">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Raum</th>
                                <th>Anzeigen</th>
                                <th>Buchen</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="room in rooms">
                                <td>{{ room.name}}</td>
                                <td><input type="checkbox" v-on:change="toggleWatchedRooms(room)" :checked="watchedRooms.includes(room)"> </td>
                                <td><input type="radio" name="roomSelector" :checked="room == createRoom" v-on:change="setCreateRoom(room)"> </td>
                            </tr>
                        </tbody>
                    </table>
                    <!--<button v-for="room in rooms" class="badge badge-info" @dblclick="toggleWatchedRooms(room)"
                            @click="setCreateRoom(room)" v-bind:class="{active:watchedRooms.includes(room) }">{{ room.name}}
                    </button>-->

                    <hr>

                    <div class="row">
                        <div class="col-12 col-md-6">
                            <label for="doctor-select">Arzt auswählen</label>
                            <select id="doctor-select" class="custom-select" :class="{'is-invalid': !doctor}" v-model="doctor">
                                <option v-for="doc in selectableDoctors" :value="doc">{{ doc.name }}</option>
                            </select>
                            <div class="invalid-feedback">
                                Bitte Arzt auswählen.
                            </div>
                        </div>
                        <div class="col-12 col-md-6">
                            <label for="pay-type-select"  v-if="permissions['list types']">Termintyp auswählen</label>
                            <select id="pay-type-select" class="custom-select" :class="{'is-invalid': !type}" v-model="type" v-if="permissions['list types']">
                                <option v-for="t in appointmentTypes" :value="t">{{ t.name }} ({{ t.pay_type }}/{{ t.duration}}
                                    Minuten)
                                </option>
                            </select>
                            <div class="invalid-feedback">
                                Bitte Termintyp auswählen.
                            </div>
                            <small v-if="type && type.invisible == true" id="pay-type-select-help" class="form-text text-muted">Achtung: Der ausgewählte Termintyp ist für Patienten nicht sichtbar.</small>
                        </div>
                    </div>

                    <div class="mt-2" v-if="permissions['create repeating bookings']">
                        <input type="checkbox" id="repeatEvent" v-model="repeatEvent"/>
                        <label for="repeatEvent"> Wiederholen</label>
                    </div>
                </div>
            </div>
        </div>

        <div id="eventOptions">

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
                                <button v-if="checkPermissions('update bookings', 'update own bookings', 'target')" @click="setEndDate" class="btn btn-primary mt-2" data-dismiss="modal">Letzter Termin</button>
                            </div>
                            <div>
                                <button v-if="checkPermissions('add booking exceptions', 'add own booking exceptions', 'target')"
                                    @click="addException" class="btn btn-danger mt-2" data-dismiss="modal">Ausnahme hinzufügen</button>
                            </div>
                            <div>
                                <button v-if="checkPermissions('delete bookings', 'delete own bookings', 'target')" @click="deleteEvent" class="btn btn-danger mt-2" data-dismiss="modal">!!!Wiederholungsbuchung löschen!!!</button>
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
                                <button v-if="checkPermissions('delete bookings', 'delete own bookings', 'target|owner')" @click="deleteEvent" class="btn btn-danger mt-2" data-dismiss="modal">Einzeltermin löschen</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="mt-3" v-if="permissions['create bookings']">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Ausnahmen</h6>
                </div>
                <div class="card-body">
                    <div v-for="exception in exceptions">
                        <strong v-if="exception.doctor_name !== null">{{ exception.doctor_name}}</strong>
                        {{ exception.exception_date }}
                        ({{ exception.start }} - {{ exception.end }})
                        <button @click="deleteException(exception)" class="btn btn-danger">X</button>
                    </div>
                </div>
            </div>
        </div>

        <div id="add-event-error-modal" class="modal fade text-left" tabindex="-1" role="dialog">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Fehler</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <p>{{ errorText }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
    import {FullCalendar} from 'vue-full-calendar';
    import bootstrapPlugin from '@fullcalendar/bootstrap';
    import 'fullcalendar/dist/locale/de-at'
    import 'fullcalendar/dist/fullcalendar.css'
    import Datepicker from 'vuejs-datepicker';
    import {de} from 'vuejs-datepicker/dist/locale'
    import * as moment from 'moment'
    moment.locale("de-at");
    function dumpNotEqual(message, exp1, exp2) {
        var res = exp1 !== exp2;
        console.log(message + res);
        console.log("Exp1: " + exp1);
        console.log("Exp2: " + exp2);
    }

    function dumpEqual(message, exp1, exp2) {
        var res = exp1 === exp2;
        console.log(message + res);
        console.log("Exp1: " + exp1);
        console.log("Exp2: " + exp2);
    }

    export default {
        components: {
            FullCalendar,
            Datepicker
        },
        computed: {
            selectedEventDate: function () {
                return this.selectedEvent == null ? "" : new Date(this.selectedEvent.start);
            }
        },
        methods: {
            addException: function () {
                console.log("Adding " + this.selectedEvent.booking_id + " as an exception on " + this.selectedEventDate);
                axios.put('/bookings/exceptDate/' + this.selectedEvent.booking_id,
                    {exception_date: moment(this.selectedEventDate).format("YYYY-MM-DD")}).then(response => {
                    this.selectedEvent = null;
                    this.refreshEvents();
                    this.getExceptions();
                });
            },
            checkPermissions: function(permission, ownPermission, userToCheck) {
                /* First check if user has permission, otherwise check if it was booked by the current user and he
                 holds ownPermission.
                 If userToCheck == 'target' then it is compared, if the user matched the target (doctor_id).
                 If userToCheck == 'owner' then the comparison checks who made this booking (booked_by).
                 Multiple values can be passed when separating with pipe, i.e "target|owner"
                 */
                let targets = userToCheck.split("|");

                return this.permissions[permission]
                    || (this.permissions[ownPermission] && targets.includes("owner")
                        && this.selectedEvent.booked_by == this.user)
                    || (this.permissions[ownPermission] && targets.includes("target")
                        && this.selectedEvent.doctor_id == this.user && this.selectedEvent.type != null)
            },
            setEndDate: function () {
                axios.put('bookings/update/' + this.selectedEvent.booking_id,
                    {repeat_until: moment(this.selectedEventDate).format("YYYY-MM-DD")}).then(response => {
                    this.selectedEvent = null;
                });
            },
            deleteEvent: function () {
                axios.delete('bookings/delete/' + this.selectedEvent.booking_id).then(response => {
                    this.refreshEvents();
                    this.selectedEvent = null;
                });
            },
            deleteException: function (exception) {
                axios.delete('bookings/exceptions/' + exception.exception_id).then(response => {
                    this.exceptions.splice(this.exceptions.indexOf(exception), 1);
                    this.refreshEvents();
                });
            },
            refreshEvents() {
                this.$refs.calendar.$emit('refetch-events');
            },
            getRooms: function () {
                axios.get('rooms').then(response => {
                    this.rooms = response.data;
                    this.setCreateRoom(this.rooms[0]);
                    for(var i in this.rooms) {
                        // Show all rooms on default.
                        this.toggleWatchedRooms(this.rooms[i]);
                    }
                });
            },
            getExceptions: function () {
                if (selectableDoctors.length <= 1) {
                    axios.get('bookings/exceptions/' + this.doctor.id).then(response => {
                        this.exceptions = response.data;
                    });
                } else {
                    axios.get('bookings/exceptions').then(response => {
                        this.exceptions = response.data;
                    });
                }
            },
            toggleWatchedRooms: function (room) {
                console.log("Called");
                if (this.watchedRooms.includes(room)) {
                    this.watchedRooms.splice(this.watchedRooms.indexOf(room), 1);
                    this.$refs.calendar.fireMethod('removeEventSource', '../api/bookings/' + room.id);
                } else {
                    this.watchedRooms.push(room);
                    this.$refs.calendar.fireMethod('addEventSource', '../api/bookings/' + room.id);
                }
            },
            closeEditWindow: function () {
                this.selectedEvent = null;
            },
            setCreateRoom: function (room) {
                this.createRoom = room;
            },
            isBreakNeededBefore: function (event) {
                var existingEvents = this.$refs.calendar.fireMethod('clientEvents');

                return existingEvents.filter(this.conflictingEventsBefore(event)).length > 0;
            },
            isBreakNeededAfter: function (event) {
                var existingEvents = this.$refs.calendar.fireMethod('clientEvents');

                return existingEvents.filter(this.conflictingEventsAfter(event)).length > 0;
            },
            conflictingEventsBefore: function (newEvent) {
                return function (otherEvent) {
                    dumpNotEqual("Different days: ", newEvent.start.day(), otherEvent.start.day());
                    dumpEqual("Same doctor: ", newEvent.doctor_id, otherEvent.doctor_id);
                    dumpEqual("Same room: ", newEvent.room_id, otherEvent.room_id);
                    if (newEvent.start.day() !== otherEvent.start.day()
                        || newEvent.doctor_id == otherEvent.doctor_id
                        || newEvent.room_id != otherEvent.room_id) { // note: types don't match!
                        return false;
                    }
                    otherEvent.end.add(15, 'minutes');
                    return otherEvent.start.isBefore(newEvent.end) && otherEvent.end.isAfter(newEvent.start);
                }
            },
            conflictingEventsAfter: function (newEvent) {
                return function (otherEvent) {                    
                    if (newEvent.start.day() !== otherEvent.start.day()
                        || newEvent.doctor_id == otherEvent.doctor_id
                        || newEvent.room_id != otherEvent.room_id) { // note: types don't match!
                        return false;
                    }
                    otherEvent.start.subtract(15, 'minutes');
                    return otherEvent.end.isAfter(newEvent.start);
                }
            },
            addEvent: function (event) {
                // Check if the event is outside the opening hours.
                axios.post('/openings/isEventInOpenings', {
                    start: event.start,
                    end: event.end
                }).then(() => {
                    this.$refs.calendar.fireMethod('unselect');
                    if (this.createRoom === null) {
                        this.inputError("Wählen Sie bitte zuerst einen Raum aus.");
                        this.errorText = "Wählen Sie bitte zuerst einen Raum aus.";
                        $("#add-event-error-modal").modal();
                        return;
                    }

                    if(this.doctor === null) {
                        this.errorText = "Wählen Sie bitte zuerst einen Arzt aus.";
                        $("#add-event-error-modal").modal();
                        return;
                    }

                    // TODO: Überprüfen ob Termintyp vorhanden ist, außer es wird von der Rezeption gebucht
                    event.doctor_id = this.doctor.id;
                    event.room_id = this.createRoom.id;
                    if (this.isBreakNeededBefore(event)) {
                        event.start.add(15, 'minutes');
                    }
                    if (this.isBreakNeededAfter(event)) {
                        event.end.subtract(15, 'minutes');
                    }
                    axios.post('/bookings', {
                        start: event.start, end: event.end, room_id: this.createRoom.id, doctor_id: this.doctor.id,
                        repeat: this.repeatEvent, type_id: this.type !== null ? this.type.id : null
                    }).then(response => {
                        this.refreshEvents();
                    }).catch(error => {
                        this.errorText = error.response.data.message;
                        $("#add-event-error-modal").modal();
                    });
                }).catch(error => {
                    console.log(error);
                    this.errorText = error.response.data.message;
                    $("#add-event-error-modal").modal();
                });
            },
            updateEvent: function (event) {
                axios.put('/bookings/update/' + event.booking_id, {
                    start: event.start,
                    end: event.end
                }).then(response => {
                    this.refreshEvents();
                });
            },

            inputError: function (message) {
                alert(message);
            },
            eventSelected: function (event) {
                this.selectedEvent = event;

                // Weekly event
                if(this.selectedEvent !== null && this.selectedEvent.weekly_repeat == 1) {
                    $("#weekly-event-modal").modal();
                }

                if(this.selectedEvent !== null && this.selectedEvent.weekly_repeat == 0) {
                    $("#single-event-modal").modal();
                }
            }
        },
        beforeMount() {
            if (this.selectableDoctors.length === 0) {
                this.doctor = {id: this.user};
            }
            else if (this.selectableDoctors.length === 1) {
                this.doctor = this.selectableDoctors[0];
            }
        },
        mounted() {
            this.rooms = this.getRooms();
            this.exceptions = this.getExceptions();
        },
        data() {
            return {
                eventSources: [
                    {
                        url: '../api/openings',
                        rendering: 'background',
                        color: '#999'
                    }
                ],
                de: de,
                disabledDates: {to: new Date()},
                config: {
                    locale: 'de-at',
                    allDaySlot: false,
                    selectable: permissions['select bookings'],
                    editable: false,

                    snapDuration: '00:15:00',
                    header: {
                        left: 'prev,next today',
                        center: 'title',
                        right: 'agendaWeek,agendaDay'
                    },

                    plugins: [ bootstrapPlugin ],
                    themeSystem: 'bootstrap4',

                    eventRender: function (event, element) {
                        if(event.type == undefined && event.is_opening == undefined) {
                            element.css({
                                'color': '#fff',
                                'background-color': '#d52a1a',
                                'border-color': '#ca2819'
                            });
                        }
                        // Farben für die Räume: Gelb Orange Rot Grün Blau
                        var roomStyles = [
                            // Room #1
                            {
                                'color': '#fff',
                                'background-color': '#f1c40f',
                                'border-color': '#d9b00c'
                            },
                            // Room #2
                            {
                                'color': '#fff',
                                'background-color': '#f39c12',
                                'border-color': '#df8d0b'
                            },
                            // Room #3
                            {
                                'color': '#fff',
                                'background-color': '#e74c3c',
                                'border-color': '#e33422'
                            },
                            // Room #4
                            {
                                'color': '#fff',
                                'background-color': '#2ecc71',
                                'border-color': '#29b765'
                            },
                            // Room #5
                            {
                                'color': '#fff',
                                'background-color': '#3498db',
                                'border-color': '#248acf'
                            }
                        ];

                        if(roomStyles[event.room_id-1]) {
                            element.css(roomStyles[event.room_id-1]);
                        }
                        
                        if (!('ranges' in event)) {
                            return true;
                        }
                        return (event.ranges.filter(function (range) { // test event against all the ranges
                            let e_start = moment(event.start.format("YYYY-MM-DD"));
                            let e_end = moment(event.end.format("YYYY-MM-DD"));
                            let r_start = moment(range.start);
                            let r_end = moment(range.end);

                            return (e_start.isSameOrBefore(r_end) &&
                                e_end.isSameOrAfter(r_start));

                        }).length) > 0; //if it isn't in one of the ranges, don't render it (by returning false)
                    },
                    viewRender: function (view, element) {
                        this.selectedEvent = null;
                    }
                },
                rooms: [],
                watchedRooms: [],
                appointmentTypes: appointmentTypes,
                createRoom: null,
                repeatEvent: !!permissions['create repeating bookings'],
                selectedEvent: null,
                selectedEventEndDate: null,
                type: null,
                doctor: null,
                selectableDoctors: selectableDoctors,
                permissions: permissions,
                exceptions: [],
                user: user,
                errorText: ''
            }
        }
    }
</script>

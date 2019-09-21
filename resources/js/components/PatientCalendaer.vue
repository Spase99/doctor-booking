<template>
    <div>
        <transition name="fade">
            <div v-show="axiousActive" class="fullscreen-backdrop-overlay"></div>
        </transition>

        <div class="section1 mb-5" :class="{ 'show-day-slots': daySlots.length }" v-show="appointment == null && bookingSuccess == false" >
            <div class="clearfix">
                <div class="datepicker-wrapper mb-0">

                    <div class="hfl-card hfl-card-green payment-info mb-4" v-if="!doctor.can_turn_in">
                        <p class="small mb-0">{{ noTurninMessage }}</p>
                    </div>

                    <div class="pay-types">
                        <p v-if="payTypes && payTypes.length > 1">Bitte wählen Sie:</p>
                        <button v-for="payType in payTypes" class="hfl-badge" @click="selectPayType(payType)"
                            :class="{ active: payType == selectedPayType }">
                            <span v-if="payType == 'Krankenkasse'">Krankenkasse: KFA</span>
                            <span v-else>{{ payType }}</span>
                        </button>
                    </div>

                        <div class="appointment-types" v-show="selectedPayType != null">
                            <button v-for="appointmentType in appointmentTypes" class="hfl-badge" @click="selectType(appointmentType)"
                                :class="{ active: appointmentType == selectedType }">{{ appointmentType.name }} ({{ appointmentType.duration }} Minuten)</button>
                        </div>
                </div>
            </div>
            <div class="datepicker-wrapper">
                <!-- <transition name="fade"> -->
                    <div v-show="selectedPayType != null && selectedType != null">
                        <datepicker  :key="key" v-model="selectedDate" :inline="true" :language="de" v-on:input="dateSelected()"
                                :disabledDates="disabledDates" v-on:changedMonth="changeMonth"></datepicker>
                    </div>
                <!-- </transition> -->
            </div>
            <div class="day-slots-wrapper">
                <button v-for="slot in daySlots" :class="{ active: slot == selectedSlot }" class="hfl-badge"
                    @click="selectSlot(slot)">{{ slot }}</button>
            </div>
        </div>


        <div class="section2 mb-5 mt-5" v-if="appointment != null && bookingSuccess == false" >
            <div class="back-text">
                <button class="btn btn-link" @click="deselectSlot()">&lsaquo; Termin ändern</button>
            </div>
            <div class="hfl-card">
                <h2 class="h5 mb-1">{{ appointment }}</h2>
                <h2 class="h6 mb-4">{{ selectedType.name }}: {{ selectedType.duration}} Minuten</h2>

                <form id="appointment-form" class="mb-0">
                    <div class="form-group">
                        <label for="first_name">Vorname:</label>
                        <input id="first_name" class="form-control" type="text" name="first_name" v-model="first_name" required>
                    </div>
                    <div class="form-group">
                        <label for="last_name">Nachname:</label>
                        <input id="last_name" class="form-control" type="text" name="last_name" v-model="last_name" required>
                    </div>
                    <div class="form-group">
                        <label for="tel">Telefon:</label>
                        <input id="tel" class="form-control" type="tel" name="phone" v-model="phone" required>
                    </div>
                    <div class="form-group mb-4">
                        <label for="email">E-Mail:</label>
                        <input id="email" class="form-control" type="email" name="email" v-model="email" required>
                    </div>
                    <button class="btn hfl-success-btn" @click="sendForm()" type="button">Termin buchen</button>
                </form>
            </div>
        </div>

        <div class="success-section mb-5 mt-5" v-if="bookingSuccess">
            <h4>Wir freuen uns auf Ihren Besuch!</h4>
            <p>Der Termin ist für Sie reserviert. Wir haben Ihnen eine Terminbestätigung per Mail geschickt.<br>
            Bitte überprüfen Sie gegebenfalls Ihren SPAM-Ordner.</p>
            <a class="btn hfl-success-btn rounded-0" href="https://www.healthforlife.at/">Zurück zur Startseite</a>
        </div>

    </div>

</template>

<script>
    import Datepicker from 'vuejs-datepicker';
    import {de} from 'vuejs-datepicker/dist/locale'
    import * as moment from 'moment'

    import Swal from 'sweetalert2';
    const bootstrapSwal = Swal.mixin({
        customClass: {
        confirmButton: 'btn hfl-success-btn',
        cancelButton: 'btn btn-danger'
        },
        buttonsStyling: false,
    });

    moment.locale("de-at");


    export default {
        props: {
            doctor: Object,
            noTurninMessage: String,
            showInvisible: {
                type: Boolean,
                default: false
            },
            payTypes: Array
        },
        data() {
            return {
                selectedDate: null,
                appointment: null,
                de: de,
                timeslots: [],
                appointmentTypes: [],
                disabledDates: {
                    to: new Date(),
                },
                first_name: "",
                last_name: "",
                phone: "",
                email: "",
                start: null,
                end: null,
                daySlots: [],
                key: "a",
                selectedPayType: null,
                selectedType: null,
                selectedSlot: null,
                //payTypes: payTypes,
                currentMonth: moment(new Date()).format('YYYY-MM'),
                axiousActive: false,
                bookingSuccess: false
            }
        },
        mounted() {
            if (this.payTypes.length == 1) {
                this.selectPayType(this.payTypes[0]);
            }
            console.log(this.noTurninMessage);
        },
        methods: {
            changeMonth: function (month) {
                this.currentMonth = moment(month).format('YYYY-MM');
                this.timeslots = [];
                this.getSlots();
            },
            selectSlot: function (slot) {
                this.appointment = this.formattedDate + " " + slot;
                this.selectedSlot = slot;
                var timeslot = slot.split("-");
                this.start = timeslot[0];
                this.end = timeslot[1];
            },
            deselectSlot: function() {
                this.appointment = null;
                this.selectedSlot = null;
                this.start = null;
                this.end = null;
            },
            sendForm: function () {
                var url = '/doc/' + this.doctor.url_slug;
                this.axiousActive = true;
                axios.post(url, {
                    first_name: this.first_name,
                    last_name: this.last_name,
                    phone: this.phone,
                    email: this.email,
                    type: this.selectedType.id,
                    start: this.isoDate + " " + this.start,
                    end: this.isoDate + " " + this.end
                })
                    .then(response => {
                        this.first_name = this.last_name = this.phone = this.email = "";
                        bootstrapSwal.fire({
                            type: 'success',
                            title: 'Termin eingetragen',
                            text: 'Ihr Termin wurde erfolgreich eingetragen.',
                        });
                        this.bookingSuccess = true;
                    })
                    .catch(response => {
                        bootstrapSwal.fire({
                            type: 'error',
                            title: 'Fehler',
                            text: 'Beim Eintragen des Termins ist ein Fehler aufgetreten. Versuchen Sie es später erneut.',
                        });
                    })
                    .finally(() => {
                        this.axiousActive = false;
                        this.deselectSlot();
                    });
            },
            selectPayType: function (payType) {
                if (this.selectedPayType === payType) {
                    return;
                }
                this.selectedPayType = payType;
                this.selectedType = null;
                this.selectedDate = null;
                this.appointment = null;
                this.timeslots = [];
                this.daySlots = [];
                this.getAppointmentTypes();
            },
            selectType: function (type) {
                if (this.selectedType === type) {
                    return;
                }
                this.selectedType = type;
                this.selectedDate = null;
                this.appointment = null;
                this.timeslots = [];
                this.daySlots = [];
                this.getSlots();
            },
            getAppointmentTypes: function() {
                var url = 'types/' + this.doctor.id + '/'+this.selectedPayType+'/appointmentTypes';
                axios.get(url).then(response => {
                    this.appointmentTypes = response.data.filter(type => {
                        if(!this.showInvisible) {
                            return !type.invisible;
                        } else {
                            return true;
                        }
                    });
                    if (this.appointmentTypes.length === 1) {
                        this.selectType(this.appointmentTypes[0]);
                    }
                })
            },
            getSlots: function () {
                var url = '/doc/' + this.doctor.url_slug + '/freeappointments/' + this.currentMonth;

                if (this.selectedType !== "") {
                    url += "?type=" + this.selectedType.id;
                }

                axios.get(url).then(response => {
                    this.timeslots = response.data;
                    if (this.key === "a") {
                        this.key = Math.random().toString(36).substring(2) + (new Date()).getTime().toString(36);
                    }
                    var _this = this;
                    this.disabledDates.customPredictor = function (date) {
                        date = moment(date);
                        for (var key in _this.timeslots) {
                            if (_this.timeslots.hasOwnProperty(key)) {
                                if (key === date.format('YYYY-MM-DD')) {
                                    return false;
                                }
                            }
                        }
                        return true;
                    };

                });
            },
            dateSelected: function () {
                if (this.selectedPayType === null) {
                    $("#error-choose-pay-type-modal").modal();
                    bootstrapSwal.fire({
                        type: 'error',
                        title: 'Fehler',
                        text: 'Bitte wählen Sie zuerst aus ob sie Kassen- oder Privatpatient sind.',
                    });

                    this.selectedDate = null;
                    return;
                }
                this.appointment = null;
                this.daySlots = this.timeslots[this.isoDate];
                /*var url = '/doc/' + this.url_slug + '/' + this.type.id + '/' + this.isoDate;

                axios.get(url).then(response => {
                    this.timeslots = response.data;
                });*/
            }
        },
        components: {
            Datepicker
        },
        computed: {
            formattedDate: function () {
                if (this.selectedDate != null) {
                    return moment(this.selectedDate).format('dddd, LL');
                } else {
                    return "";
                }
            },
            isoDate: function () {
                if (this.selectedDate != null) {
                    return moment(this.selectedDate).format('YYYY-MM-DD');
                } else {
                    return "";
                }
            }
        },
    }
</script>

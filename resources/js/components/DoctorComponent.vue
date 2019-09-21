<template>
    <div id="doctors">
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <div class="row align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-primary col">Ärzte</h6>
                    <a href="#_new-doctor" class="btn btn-success col-auto">Arzt hinzufügen</a>
                </div>
            </div>
            <div class="card-body">
                <table class="table table-striped table-responsive-lg doctor-table">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>E-Mail</th>
                            <th>Telefon</th>
                            <th>URL</th>
                            <th>Einreichbar</th>
                            <th>Kann Buchen</th>
                            <th>Google-Account</th>
                            <td class="button-wrapper--two-buttons">&nbsp;</td>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="doctor in doctors" :key="doctor.id" @dblclick="editDoctor(doctor)" :class="{ editing: doctor == editedDoctor }"
                            @keyup.enter="doneEdit(doctor)" @keyup.esc="cancelEdit(doctor)">
                            <td>
                                <label v-if="editedDoctor != doctor">{{ doctor.name}}</label>
                                <input class="form-control edit" type="text"
                                    v-model="doctor.name">
                            </td>
                            <td>
                                <label v-if="editedDoctor != doctor">{{ doctor.email}}</label>
                                <input class="form-control edit" type="text"
                                    v-model="doctor.email" placeholder="m.mustermann@healthforlife.at">
                            </td>
                            <td>
                                <label v-if="editedDoctor != doctor">{{ doctor.phone}}</label>
                                <input class="form-control edit" type="text"
                                    v-model="doctor.phone">
                            </td>
                            <td>
                                <label v-if="editedDoctor != doctor">{{ doctor.url_slug}}</label>
                                <input class="form-control edit" type="text"
                                    v-model="doctor.url_slug">
                            </td>
                            <td>
                                <input type="checkbox" v-model="doctor.can_turn_in" :disabled="editedDoctor != doctor">
                            </td>
                            <td>
                                <input type="checkbox" v-model="doctor.can_book" :disabled="editedDoctor != doctor">
                            </td>
                            <td>
                                {{ doctor.google_account}}
                            </td>
                            <td class="text-right button-wrapper--two-buttons">
                                <button class="btn btn-sm btn-danger btn-circle btn-sm" v-show="doctor != editedDoctor" data-toggle="modal" v-bind:data-target="'#delete-doctor-' + doctor.id + '-modal'"><i class="fas fa-trash"></i></button>
                                <button class="btn btn-sm btn-primary btn-circle ml-2" v-show="doctor != editedDoctor" @click="editDoctor(doctor)"><i class="fas fa-edit"></i></button>
                                <button class="btn btn-sm btn-secondary btn-circle" v-show="doctor == editedDoctor" @click="cancelEdit(doctor)"><i class="fas fa-times"></i></button>
                                <button class="btn btn-sm btn-success btn-circle ml-2" v-show="doctor == editedDoctor" @click="doneEdit(doctor)"><i class="fas fa-check"></i></button>
                                
                                <div v-bind:id="'delete-doctor-' + doctor.id + '-modal'" class="modal fade text-left" tabindex="-1" role="dialog">
                                    <div class="modal-dialog" role="document">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title">Arzt löschen</h5>
                                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                    <span aria-hidden="true">&times;</span>
                                                </button>
                                            </div>
                                            <div class="modal-body">
                                                <p>Soll der Arzt <strong>{{doctor.name}}</strong> wirklich gelöscht werden?<br>Zum Löschen geben Sie bitte "Ja" in das Feld ein.</p>
                                                <input v-model="deleteDoctorConfirmationText" type="text" class="form-control">
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary" data-dismiss="modal" @click="cancelEdit(doctor)">Abbrechen</button>
                                                <button type="button" class="btn btn-danger" data-dismiss="modal" :disabled="deleteDoctorConfirmationText !== 'Ja'" @click="deleteDoctor(doctor)" >Löschen</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="card shadow mb-4" id="_new-doctor">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Arzt hinzufügen</h6>
            </div>
            <div class="card-body">
                <form novalidate class="needs-validation">
                    <div class="form-group">
                        <label for="newDocName">Name</label>
                        <input id="newDocName" class="form-control" type="text" v-model="new_doc.name" required>
                        <div class="invalid-feedback">
                            Dieses Feld ist ein Pflichtfeld.
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="newDocPhone">Telefon</label>
                        <input id="newDocPhone" class="form-control" type="text" v-model="new_doc.phone" required>
                        <div class="invalid-feedback">
                            Dieses Feld ist ein Pflichtfeld.
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="newDocEmail">E-Mail</label>
                        <input id="newDocEmail" class="form-control" type="text" v-model="new_doc.email" required>
                        <div class="invalid-feedback">
                            Dieses Feld ist ein Pflichtfeld.
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="newDocURL">URL:</label>
                        <input id="newDocURL" class="form-control" type="text" v-model="new_doc.url_slug" required>
                        <div class="invalid-feedback">
                            Dieses Feld ist ein Pflichtfeld.
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="newDocCanTurnIn">Einreichbar:</label>
                        <input id="newDocCanTurnIn" type="checkbox" v-model="new_doc.can_turn_in">
                        <div class="invalid-feedback">
                            Dieses Feld ist ein Pflichtfeld.
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="newDocGoogle">Google-Account:</label>
                        <input id="newDocGoogle" class="form-control" aria-describedby="newDocGoogleHelp" type="text" v-model="new_doc.google_account" required>
                        <div class="invalid-feedback">
                            Dieses Feld ist ein Pflichtfeld.
                        </div>
                        <small id="newDocGoogleHelp" class="form-text text-muted">
                            Google Account: Es wird ein Google-Kalender erstellt und dann mit dem angegebenen Konto geteilt. Eine Änderung kann aus Sicherheitsgründen nur manuell erfolgen.
                        </small>
                    </div>
                    <button class="btn btn-primary" type="submit" id="add-doctor" @click="addDoctor" @keyup.enter="addDoctor">Anlegen</button>
                </form>
            </div>
        </div>
    </div>
</template>

<script>
    export default {
        mounted() {
            this.doctors = this.getDoctors();
            window.initFormValidation();
        },
        methods: {
            getDoctors: function() {
                axios.get('/doctors').then(response => {
                    this.doctors = response.data;
                });
            },
            addDoctor: function () {
                axios.post('/doctors', {name: this.new_doc.name, email: this.new_doc.email,
                    phone: this.new_doc.phone, url_slug:this.new_doc.url_slug, google_account: this.new_doc.google_account,
                can_turn_in: this.new_doc.can_turn_in})
                    .then(response => {
                    this.doctors.push(response.data);
                    this.new_doc = {};
                });
            },
            editDoctor: function(doctor) {
                this.editedDoctor = doctor;
                this.beforeEditCache = JSON.parse(JSON.stringify(doctor));
            },
            sameUrlFilter: function(doctor) {
                return function (otherDoctor) {
                    return otherDoctor.url_slug != null && doctor.url_slug == otherDoctor.url_slug && otherDoctor != doctor;
                }
            },
            errorMessage: function (message) {
                alert(message);
            },
            doneEdit: function (doctor) {
                if (!this.editedDoctor) {
                    return;
                }
                this.editedDoctor = null;
                if (doctor.url_slug === "") {
                    doctor.url_slug = null;
                }
                if (this.doctors.filter(this.sameUrlFilter(doctor)).length > 0) {
                    this.errorMessage("Diese URL ist bereits vergeben.");
                    this.cancelEdit(doctor);
                    return;
                }
                axios.put('/doctors/' + doctor.id, {
                    doctor: doctor
                }).then(response => {
                    doctor = (response.data);
                    this.beforeEditCache = null;
                });
            },
            cancelEdit: function (doctor) {
                this.editedDoctor = null;
                for (var key in this.beforeEditCache) {
                    if (this.beforeEditCache.hasOwnProperty(key)) {
                        doctor[key] = this.beforeEditCache[key];
                    }
                }
                this.deleteDoctorConfirmationText = '';
            },
            deleteDoctor: function(doctor) {
                if(this.deleteDoctorConfirmationText === 'Ja' && doctor) {
                    axios.delete('/doctors/'+doctor.id);
                    this.doctors.splice(this.doctors.indexOf(doctor), 1);
                }
                this.deleteDoctorConfirmationText = '';
            }
        },
        data: function () {
            return {
                doctors: [],
                new_doc: {},
                editedDoctor: null,
                beforeEditCache: null,
                deleteDoctorConfirmationText: ''
            }
        }
    }
</script>

<style scoped>
.button-wrapper--two-buttons {
    width: 105px !important;
}

@media (min-width: 992px) {
    .doctor-table {
        width: 100%;
        table-layout: fixed;
    }

    .doctor-table td > label,
    .doctor-table td > input {
        max-width: 100%;
    }
}

</style>
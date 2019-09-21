<template>
    <div class="reception-patient-calendar">
        <div class="card shadow mb-4">
            <div class="card-body">
                <div class="row">
                    <div class="col-12 col-md-6 col-lg-4">
                        <label for="doctor-select">Arzt auswählen</label>
                        <select id="doctor-select" class="custom-select" v-model="currentDoctor" v-on:change="getPayTypes()">
                            <option v-for="doc in selectableDoctors" :value="doc">{{ doc.name }}</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>
        <div class="card shadow mb-4" v-if="currentDoctor && currentPayTypes">
            <div class="card-body">
                <div class="row">
                    <div class="col-12">
                        <patient-calendar
                            :doctor="currentDoctor"
                            :pay-types="currentPayTypes"
                            :show-invisible="true">
                        </patient-calendar>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
    export default {
        methods: {
            getSelectableDoctors: function() {
                axios.get('/doctors/').then(response => {
                    this.selectableDoctors = response.data;
                });
            },
            getPayTypes: function() {
                if(this.currentDoctor) {
                    this.currentPayTypes = null;
                    axios.get('/types/' + this.currentDoctor.id + '/payTypes').then(response => {
                        this.currentPayTypes = response.data;
                    });
                }
            }
        },
        mounted() { 
            this.selectableDoctors = this.getSelectableDoctors();
        },
        data() {
            return {
                currentDoctor: null,
                currentPayTypes: null,
                selectableDoctors: []
            }
        }
    }
</script>
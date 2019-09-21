<template>
    <div id="types">
        <div id="doctors">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Termintypen</h6>
                </div>
                <div class="card-body">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Verrechnung</th>
                                <th>Dauer</th>
                                <th data-toggle="tooltip" data-placement="top" title="Termine bei denen diese Option aktiviert ist, sind für den Patienten unsichtbar und nicht buchbar.">Verbergen vor Patienten</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="type in types" :key="type.id" @dblclick="edittype(type)" :class="{ editing: type == editedtype }"
                                @keyup.enter="doneEdit(type)" @keyup.esc="cancelEdit(type)">
                                <td>
                                    <label v-if="editedtype != type">{{ type.name}}</label>
                                    <input class="form-control edit" type="text"
                                        v-model="type.name">
                                </td>
                                <td>
                                    <label v-if="editedtype != type">{{ type.pay_type}}</label>
                                    <select class="form-control edit" type="text" v-model="type.pay_type">
                                        <option v-for="payType in payTypes" :value="payType" :selected="edittype.pay_type == payType">{{ payType}}</option>
                                    </select>
                                </td>
                                <td>
                                    <label v-if="editedtype != type">{{ type.duration}}</label>
                                    <input class="form-control edit" type="text"
                                        v-model="type.duration">
                                </td>
                                <td>
                                    <input type="checkbox" :disabled="editedtype != type" v-model="type.invisible">
                                </td>
                                <td class="text-right button-wrapper--two-buttons">
                                    <button class="btn btn-danger btn-sm btn-circle" v-show="type != editedtype" data-toggle="modal" v-bind:data-target="'#delete-type-' + type.id + '-modal'"><i class="fas fa-trash"></i></button>
                                    <button class="btn btn-primary btn-sm btn-circle ml-2" v-show="type != editedtype" @click="edittype(type)"><i class="fas fa-edit"></i></button>
                                    <button class="btn btn-sm btn-secondary btn-circle" v-show="type == editedtype" @click="cancelEdit(type)"><i class="fas fa-times"></i></button>
                                    <button class="btn btn-sm btn-success btn-circle ml-2" v-show="type == editedtype" @click="doneEdit(type)"><i class="fas fa-check"></i></button>

                                    <div v-bind:id="'delete-type-' + type.id + '-modal'" class="modal fade text-left" tabindex="-1" role="dialog">
                                        <div class="modal-dialog" role="document">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title">Termintyp löschen</h5>
                                                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                        <span aria-hidden="true">&times;</span>
                                                    </button>
                                                </div>
                                                <div class="modal-body">
                                                    <p>Soll der Termintyp <strong>{{type.name}}</strong> wirklich gelöscht werden?<br>Zum Löschen geben Sie bitte "Ja" in das Feld ein.</p>
                                                    <input v-model="deleteTypeConfirmationText" type="text" class="form-control">
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary" data-dismiss="modal" @click="cancelEdit(type)">Abbrechen</button>
                                                    <button type="button" class="btn btn-danger" data-dismiss="modal" :disabled="deleteTypeConfirmationText !== 'Ja'" @click="deletetype(type)" >Löschen</button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    <input class="form-control" type="text" v-model="new_type['name']">
                                </td>
                                <td>
                                    <select class="custom-select" type="text" v-model="new_type['pay_type']">
                                        <option v-for="payType in payTypes" :value="payType">{{ payType}}</option>
                                    </select>
                                </td>
                                <td>
                                    <input class="form-control" type="text" v-model="new_type['duration']">
                                </td>
                                <td>
                                    <input type="checkbox" v-model="new_type['invisible']">
                                </td>
                                <td class="text-right">
                                    <button class="btn btn-primary btn-sm" type="button" id="add-type" @click="addtype" @keyup.enter="addtype">Anlegen</button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
    export default {
        mounted() {
            this.types = this.gettypes();
            window.initFormValidation();
            $(function () {
                $('[data-toggle="tooltip"]').tooltip()
            });
        },
        methods: {
            gettypes: function() {
                axios.get('/types').then(response => {
                    this.types = response.data;
                });
            },
            addtype: function () {
                axios.post('/types', {
                        name: this.new_type.name,
                        duration: this.new_type.duration,
                        pay_type: this.new_type.pay_type,
                        invisible: this.new_type.invisible
                    }).then(response => {
                        this.types.push(response.data);
                        this.new_type = {};
                    });
            },
            edittype: function(type) {
                this.editedtype = type;
                this.beforeEditCache = JSON.parse(JSON.stringify(type));
            },
            sameUrlFilter: function(type) {
                return function (othertype) {
                    return othertype.url_slug != null && type.url_slug == othertype.url_slug && othertype != type;
                }
            },
            errorMessage: function (message) {
                alert(message);
            },
            doneEdit: function (type) {
                if (!this.editedtype) {
                    return;
                }
                this.editedtype = null;
                if (type.url_slug === "") {
                    type.url_slug = null;
                }
                if (this.types.filter(this.sameUrlFilter(type)).length > 0) {
                    this.errorMessage("Diese URL ist bereits vergeben.");
                    this.cancelEdit(type);
                    return;
                }
                axios.put('/types/' + type.id, {
                    type: type
                }).then(response => {
                    type = (response.data);
                    this.beforeEditCache = null;
                });
            },
            cancelEdit: function (type) {
                this.editedtype = null;
                for (var key in this.beforeEditCache) {
                    if (this.beforeEditCache.hasOwnProperty(key)) {
                        type[key] = this.beforeEditCache[key];
                    }
                }
                this.deleteTypeConfirmationText = '';
            },
            deletetype: function(type) {
                if(this.deleteTypeConfirmationText === 'Ja' && type) {
                    axios.delete('/types/'+type.id);
                    this.types.splice(this.types.indexOf(type), 1)
                }
                this.deleteTypeConfirmationText = '';
            }
        },
        data: function () {
            return {
                types: [],
                new_type: {},
                editedtype: null,
                beforeEditCache: null,
                payTypes: ["Krankenkasse", "Privat"],
                deleteTypeConfirmationText: ''
            }
        }
    }
</script>

<style scoped>
.button-wrapper--two-buttons {
    min-width: 85px !important;
}
</style>
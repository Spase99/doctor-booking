<template>
    <div id="rooms">
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Räume</h6>
            </div>
            <div class="card-body">
                <table class="table table-striped table-responsive-sm">
                    <thead>
                        <tr>
                            <th>Name</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="room in rooms" :key="room.id" :class="{ editing: room == editedRoom }">
                            <td>
                                <div class="d-flex justify-content-between align-items-center">
                                    <span @dblclick="editRoom(room)" readonly class="form-control-plaintext" v-if="editedRoom !== room">{{ room.name }}</span>
                                    <input class="edit form-control" type="text"
                                        v-model="room.name"
                                        @keyup.enter="doneEdit(room)"
                                        @keyup.esc="cancelEdit(room)" v-room-focus="editedRoom === room">
                                    <div class="ml-3 text-right button-wrapper--two-buttons" v-show="room != editedRoom">
                                        <button class="btn btn-sm btn-danger btn-circle" data-toggle="modal"  v-bind:data-target="'#delete-room-' + room.id + '-modal'"><i class="fas fa-trash"></i></button>
                                        <button class="btn btn-sm btn-primary btn-circle ml-2" @click="editRoom(room)"><i class="fas fa-edit"></i></button>
                                    </div>
                                    <div class="ml-3 text-right button-wrapper--two-buttons" v-show="room === editedRoom">
                                        <button class="btn btn-sm btn-secondary btn-circle" @click="cancelEdit(room)"><i class="fas fa-times"></i></button>
                                        <button class="btn btn-sm btn-success btn-circle ml-2" @click="doneEdit(room)"><i class="fas fa-check"></i></button>
                                    </div>
                                    
                                    <div v-bind:id="'delete-room-' + room.id + '-modal'" class="modal fade" tabindex="-1" role="dialog">
                                        <div class="modal-dialog" role="document">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title">Raum löschen</h5>
                                                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                        <span aria-hidden="true">&times;</span>
                                                    </button>
                                                </div>
                                                <div class="modal-body">
                                                    <p>Soll der Raum <strong>{{room.name}}</strong> wirklich gelöscht werden?<br>Zum Löschen geben Sie bitte "Ja" in das Feld ein.</p>
                                                    <input v-model="deleteRoomConfirmationText" type="text" class="form-control">
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary" data-dismiss="modal" @click="cancelEdit(room)">Abbrechen</button>
                                                    <button type="button" class="btn btn-danger" data-dismiss="modal" :disabled="deleteRoomConfirmationText !== 'Ja'" @click="deleteRoom(room)" >Löschen</button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
                <div :class="{ 'was-validated': newRoomFormWasValidated }">
                    <div class="form-group">
                        <label for="newRoom">Neuer Raum:</label>
                        <div class="input-group mb-2">
                            <input @keyup.enter="addRoom" class="form-control" id="newRoom" type="text" v-model="newRoom" required>
                            <div class="input-group-append">
                                <button class="btn btn-primary rounded-right" type="submit" id="add-room" @click="addRoom" @keyup.enter="addRoom">Anlegen</button>
                            </div>
                            <div class="invalid-feedback">
                                Bitte geben Sie einen Namen für den neuen Raum an.
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
    export default {
        mounted() {
            this.rooms = this.getRooms();
            window.initFormValidation();
        },
        methods: {
            getRooms: function () {
                axios.get('/rooms').then(response => {
                    this.rooms = response.data;
                });
            },
            addRoom: function () {
                if(!this.newRoom.trim()) {
                    this.newRoomFormWasValidated = true;
                    return;
                }
                this.newRoomFormWasValidated = false;
                axios.post('/rooms', {
                    name: this.newRoom
                }).then(response => {
                    this.rooms.push(response.data);
                });
                this.newRoom = '';
            },
            editRoom: function (room) {
                this.beforeEditCache = room.name;
                this.editedRoom = room;
            },
            doneEdit: function (room) {
                console.log("DONE EDIT", room);
                if (!this.editedRoom) {
                    return;
                }
                this.editedRoom = null;
                console.log(room.name.trim());
                axios.put('/rooms/' + room.id, {
                    name: room.name.trim()
                }).then(response => {
                    room.name = (response.data['name']);
                });
            },
            cancelEdit: function (room) {
                this.editedRoom = null;
                room.name = this.beforeEditCache;
                this.deleteRoomConfirmationText = '';
            },
            deleteRoom: function (room) {
                console.log(this.deleteRoomConfirmationText);
                if(this.deleteRoomConfirmationText == 'Ja' && room) {
                    axios.delete('/rooms/' + room.id);
                    this.rooms.splice(this.rooms.indexOf(room), 1)
                }
                this.deleteRoomConfirmationText = '';
            }
        },
        data: function () {
            return {
                rooms: [],
                newRoom: '',
                editedRoom: null,
                newRoomFormWasValidated: false,
                deleteRoomConfirmationText: ''
            }
        },
        directives: {
            'room-focus': function (el, binding) {
                if (binding.value) {
                    el.focus()
                }
            }
        }
    }
</script>

<style scoped>

.button-wrapper--two-buttons {
    min-width: 85px !important;
}

</style>
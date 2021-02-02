import Error from '../../util/Error'

const CLIENT_LOCATION_STATE = {
    token: null,
    id: null,
    location_id: null,
    location_type_id: null,
    error: false
};

export default {
    data() {
        return {
            client_location: {...CLIENT_LOCATION_STATE},
            client_locations: [],
            dropdowns: {
                locations: [],
                location_types: [],
            }
        }
    },
    methods: {
        handleAddUpdateClientLocationClick() {
            this.client_location.error = false;
            this.formErrors = new Error({})
            let errors = {};

            if (!this.client_location.location_id) {
                this.client_location.error = true;
                errors.additional_location_id = [this.$t('validation.required')];
            }

            if (!this.client_location.location_type_id) {
                this.client_location.error = true;
                errors.location_type_id = [this.$t('validation.required')];
            }

            _.map(this.client_locations, (location) => {
                if(location.token !== this.client_location.token) {
                    if((location.location_id === this.client_location.location_id) && (location.location_type_id === this.client_location.location_type_id)) {
                        errors.additional_location_id = [this.$t('validation.duplicate')];
                        this.client_location.error = true;
                        this.formErrors = new Error(errors);
                        return false;
                    }
                }
            });

            this.formErrors = new Error(errors)
            if(this.client_location.error) return false;

            const entity = this.client_location;
            let index = -1;
            if (entity.token)
                index = _.findIndex(this.client_locations, {token: entity.token});

            if (this.client_locations[index] !== undefined) {
                this.client_locations[index] = entity;
            } else {
                entity.token = Math.random().toString(36).substring(2, 15) + Math.random().toString(36).substring(2, 15)
                this.client_locations.push(entity)
            }

            this.client_location = {...CLIENT_LOCATION_STATE, token: Math.random().toString(36).substring(2, 15) + Math.random().toString(36).substring(2, 15)}
        },
        handleDeleteClientLocationClick(token) {
            const index = _.findIndex(this.client_locations, {token: token})
            if (this.client_locations[index] !== undefined) {
                this.client_locations.splice(index, 1);
            }
        },
        handleEditClientLocationClick(token) {
            const index = _.findIndex(this.client_locations, {token: token})
            if (this.client_locations[index] !== undefined) {
                this.client_location = {...this.client_locations[index]};
            }
        },
        resetClientLocation() {
            this.client_location = {...CLIENT_LOCATION_STATE}
        }
    }
}

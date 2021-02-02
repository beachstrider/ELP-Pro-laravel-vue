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
            supplier_location: {...CLIENT_LOCATION_STATE},
            supplier_locations: [],
            dropdowns: {
                locations: [],
                location_types: [],
            }
        }
    },
    methods: {
        handleAddUpdateSupplierLocationClick() {
            this.supplier_location.error = false;
            this.formErrors = new Error({})
            let errors = {};

            if (!this.supplier_location.location_id) {
                this.supplier_location.error = true;
                errors.additional_location_id = [this.$t('validation.required')];
            }

            if (!this.supplier_location.location_type_id) {
                this.supplier_location.error = true;
                errors.location_type_id = [this.$t('validation.required')];
            }

            _.map(this.supplier_locations, (location) => {
                if(location.token !== this.supplier_location.token) {
                    if((location.location_id === this.supplier_location.location_id) && (location.location_type_id === this.supplier_location.location_type_id)) {
                        errors.additional_location_id = [this.$t('validation.duplicate')];
                        this.supplier_location.error = true;
                        this.formErrors = new Error(errors);
                        return false;
                    }
                }
            });

            this.formErrors = new Error(errors)
            if(this.supplier_location.error) return false;

            const entity = this.supplier_location;
            let index = -1;
            if (entity.token)
                index = _.findIndex(this.supplier_locations, {token: entity.token});

            if (this.supplier_locations[index] !== undefined) {
                this.supplier_locations[index] = entity;
            } else {
                entity.token = Math.random().toString(36).substring(2, 15) + Math.random().toString(36).substring(2, 15)
                this.supplier_locations.push(entity)
            }

            this.supplier_location = {...CLIENT_LOCATION_STATE, token: Math.random().toString(36).substring(2, 15) + Math.random().toString(36).substring(2, 15)}
        },
        handleDeleteSupplierLocationClick(token) {
            const index = _.findIndex(this.supplier_locations, {token: token})
            if (this.supplier_locations[index] !== undefined) {
                this.supplier_locations.splice(index, 1);
            }
        },
        handleEditSupplierLocationClick(token) {
            const index = _.findIndex(this.supplier_locations, {token: token})
            if (this.supplier_locations[index] !== undefined) {
                this.supplier_location = {...this.supplier_locations[index]};
            }
        },
        resetSupplierLocation() {
            this.supplier_location = {...CLIENT_LOCATION_STATE}
        },
    }
}

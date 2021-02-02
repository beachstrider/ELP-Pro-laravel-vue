import Error from '../../util/Error'

const DEALER_ADDITIONAL_LOCATIONS_STATE = {
    token: null,
    id: null,
    location_id: null,
    location_type_id: null,
    error: false
};

export default {
    data() {
        return {
            dealer_additional_location: {...DEALER_ADDITIONAL_LOCATIONS_STATE},
            dealer_additional_locations: [],
            dropdowns: {
                locations: [],
                location_types: []
            }
        }
    },
    methods: {
        handleAddUpdateDealerAdditionalLocationClick() {
            this.dealer_additional_location.error = false;
            this.formErrors = new Error({})
            let errors = {};

            if (!this.dealer_additional_location.location_id) {
                this.dealer_additional_location.error = true;
                errors.additional_location_id = [this.$t('validation.required')];
            }

            if (!this.dealer_additional_location.location_type_id) {
                this.dealer_additional_location.error = true;
                errors.location_type_id = [this.$t('validation.required')];
            }

            _.map(this.dealer_additional_locations, (location) => {
                if(location.token !== this.dealer_additional_location.token) {
                    if((location.location_id === this.dealer_additional_location.location_id) && (location.location_type_id === this.dealer_additional_location.location_type_id)) {
                        errors.additional_location_id = [this.$t('validation.duplicate')];
                        this.dealer_additional_location.error = true;
                        this.formErrors = new Error(errors);
                        return false;
                    }
                }
            });

            this.formErrors = new Error(errors)
            if(this.dealer_additional_location.error) return false;

            const entity = this.dealer_additional_location;
            let index = -1;

            if (entity.token)
                index = _.findIndex(this.dealer_additional_locations, {token: entity.token});


            if (this.dealer_additional_locations[index] !== undefined) {
                this.dealer_additional_locations[index] = entity;
            } else {
                entity.token = Math.random().toString(36).substring(2, 15) + Math.random().toString(36).substring(2, 15)
                this.dealer_additional_locations.push(entity)
            }

            this.dealer_additional_location = {...DEALER_ADDITIONAL_LOCATIONS_STATE, token: Math.random().toString(36).substring(2, 15) + Math.random().toString(36).substring(2, 15)}
        },
        handleDeleteDealerAdditionalLocationClick(token) {
            const index = _.findIndex(this.dealer_additional_locations, {token: token})
            if (this.dealer_additional_locations[index] !== undefined) {
                this.dealer_additional_locations.splice(index, 1);
            }
        },
        handleEditDealerAdditionalLocationClick(token) {
            const index = _.findIndex(this.dealer_additional_locations, {token: token})
            if (this.dealer_additional_locations[index] !== undefined) {
                this.dealer_additional_location = {...this.dealer_additional_locations[index]};
            }
        },
        resetDealerAdditionalLocation() {
            this.dealer_additional_location.locations = []
            this.dealer_additional_location = {...DEALER_ADDITIONAL_LOCATIONS_STATE}
            // this.dealer_additional_locations.length = 0
        }
    }
}

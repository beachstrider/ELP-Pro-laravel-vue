import Error from '../../util/Error'

const MANUFACTURER_LOCATION_STATE = {
    token: null,
    id: null,
    location_id: null,
    location_type_id: null,
    supplier_id: null,
    suppliers: [],
    brands: [],
    models: [],
    error: false
};

export default {
    data() {
        return {
            manufacturer_location: {...MANUFACTURER_LOCATION_STATE},
            manufacturer_locations: [],
            dropdowns: {
                suppliers: [],
                brands: [],
                models: [],
                location_types: []
            }
        }
    },
    methods: {
        validateManufacturerLocation() {
            this.formErrors = new Error({})
            let errors = {};

            if (!this.manufacturer_location.location_id) {
                this.manufacturer_location.error = true;
                errors.location_id = [this.$t('validation.required')];
            }

            if (!this.manufacturer_location.supplier_id) {
                this.manufacturer_location.error = true;
                errors.supplier_id = [this.$t('validation.required')];
            }

            if (this.manufacturer_location.suppliers.length <= 0) {
                errors.suppliers = [this.$t('validation.required')];
                this.manufacturer_location.error = true;
            }

            if (!this.manufacturer_location.location_type_id) {
                errors.location_type_id = [this.$t('validation.required')];
                this.manufacturer_location.error = true;
            }

            if (this.manufacturer_location.brands.length <= 0) {
                errors.brands = [this.$t('validation.required')];
                this.manufacturer_location.error = true;
            }

            if (this.manufacturer_location.models.length <= 0) {
                errors.models = [this.$t('validation.required')];
                this.manufacturer_location.error = true;
            }

            this.formErrors = new Error(errors)
        },
        handleAddUpdateManufacturerLocationClick() {
            this.manufacturer_location.error = false;

            this.validateManufacturerLocation()

            if (this.manufacturer_location.error) return false;

            const entity = this.manufacturer_location;
            let index = -1;
            if (entity.token)
                index = _.findIndex(this.manufacturer_locations, {token: entity.token});

            if (this.manufacturer_locations[index] !== undefined) {
                this.manufacturer_locations[index] = entity;
            } else {
                entity.token = Math.random().toString(36).substring(2, 15) + Math.random().toString(36).substring(2, 15)
                this.manufacturer_locations.push(entity)
            }

            this.manufacturer_location = {
                ...MANUFACTURER_LOCATION_STATE,
                token: Math.random().toString(36).substring(2, 15) + Math.random().toString(36).substring(2, 15)
            }
        },
        handleDeleteManufacturerLocationClick(token) {
            const index = _.findIndex(this.manufacturer_locations, {token: token})
            if (this.manufacturer_locations[index] !== undefined) {
                this.manufacturer_locations.splice(index, 1);
                this.resetManufacturerLocation()
            }
        },
        handleEditManufacturerLocationClick(token) {
            const index = _.findIndex(this.manufacturer_locations, {token: token})
            if (this.manufacturer_locations[index] !== undefined) {
                this.manufacturer_location = {...this.manufacturer_locations[index]};
            }
        },
        resetManufacturerLocation() {
            this.manufacturer_location = {...MANUFACTURER_LOCATION_STATE}
        }
    }
}

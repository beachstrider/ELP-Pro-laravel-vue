import Error from '../../util/Error'

const MANUFACTURER_BRAND_STATE = {
    token: null,
    id: null,
    brand_id: null,
    models: [],
    error: false
};

export default {
    data() {
        return {
            manufacturer_brand: {...MANUFACTURER_BRAND_STATE},
            manufacturer_brands: [],
            dropdowns: {
                brands: [],
                models: [],
            }
        }
    },
    methods: {
        handleAddUpdateManufacturerBrandClick() {
            this.manufacturer_brand.error = false;
            this.formErrors = new Error({})
            let errors = {};

            if (!this.manufacturer_brand.brand_id) {
                this.manufacturer_brand.error = true;
                errors.brand_id = [this.$t('validation.required')];
            }

            if (this.manufacturer_brand.models.length <= 0) {
                errors.models = [this.$t('validation.required')];
                this.manufacturer_brand.error = true;
            }

            _.map(this.manufacturer_brands, (brand) => {
                if(brand.token !== this.manufacturer_brand.token) {
                    if((brand.brand_id === this.manufacturer_brand.brand_id) && (brand.models.some((val) => this.manufacturer_brand.models.indexOf(val) !== -1) )) {
                        errors.brand_id = [this.$t('validation.duplicate')];
                        this.manufacturer_brand.error = true;
                        this.formErrors = new Error(errors);
                        return false;
                    }
                }
            });

            this.formErrors = new Error(errors)
            if(this.manufacturer_brand.error) return false;

            const entity = this.manufacturer_brand;
            let index = -1;
            if (entity.token)
                index = _.findIndex(this.manufacturer_brands, {token: entity.token});

            if (this.manufacturer_brands[index] !== undefined) {
                this.manufacturer_brands[index] = entity;
            } else {
                entity.token = Math.random().toString(36).substring(2, 15) + Math.random().toString(36).substring(2, 15)
                this.manufacturer_brands.push(entity)
            }

            this.manufacturer_brand = {...MANUFACTURER_BRAND_STATE, token: Math.random().toString(36).substring(2, 15) + Math.random().toString(36).substring(2, 15)}
        },
        handleDeleteManufacturerBrandClick(token) {
            const index = _.findIndex(this.manufacturer_brands, {token: token})
            if (this.manufacturer_brands[index] !== undefined) {
                this.manufacturer_brands.splice(index, 1);
                this.resetManufacturerBrand();
            }
        },
        handleEditManufacturerBrandClick(token) {
            const index = _.findIndex(this.manufacturer_brands, {token: token})
            if (this.manufacturer_brands[index] !== undefined) {
                this.manufacturer_brand = {...this.manufacturer_brands[index]};
            }
        },
        getModelsLabel(contact) {
            return contact.models.map(item => {
                const model = _.find(this.dropdowns.models, {id: item});
                return {
                    id: item,
                    label: (model && model.label ? model.label : 'Unknown Model')
                }
            })
                .map((item, index) => `${index+1}. ${item.label}`)
                .join("\r\n")
        },
        getModelsType(contact) {
            return contact.models.map(item => {
                const model = _.find(this.dropdowns.models, {id: item});
                return {
                    id: item,
                    type: (model && model.type ? model.type : '')
                }
            })
                .map((item, index) => `${index+1}. ${item.type}`)
                .join("\r\n")
        },
        resetManufacturerBrand() {
            this.manufacturer_brand = {...MANUFACTURER_BRAND_STATE}
        },
        handleBrandSelect() {
            this.manufacturer_brand.models = []
        }
    }
}



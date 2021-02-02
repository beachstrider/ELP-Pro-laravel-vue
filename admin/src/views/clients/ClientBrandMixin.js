import Error from '../../util/Error'

const CLIENT_BRAND_STATE = {
    token: null,
    id: null,
    brand_id: null,
    models: [],
    error: false
};

export default {
    data() {
        return {
            client_brand: {...CLIENT_BRAND_STATE},
            client_brands: [],
            dropdowns: {
                brands: [],
                models: [],
            }
        }
    },
    methods: {
        handleAddUpdateClientBrandClick() {
            this.client_brand.error = false;
            this.formErrors = new Error({})
            let errors = {};

            if (!this.client_brand.brand_id) {
                this.client_brand.error = true;
                errors.brand_id = [this.$t('validation.required')];
            }

            if (this.client_brand.models.length <= 0) {
                errors.models = [this.$t('validation.required')];
                this.client_brand.error = true;
            }

            _.map(this.client_brands, (brand) => {
                if(brand.token !== this.client_brand.token) {
                    if((brand.brand_id === this.client_brand.brand_id) && (brand.models.some((val) => this.client_brand.models.indexOf(val) !== -1) )) {
                        errors.brand_id = [this.$t('validation.duplicate')];
                        this.client_brand.error = true;
                        this.formErrors = new Error(errors);
                        return false;
                    }
                }
            });

            this.formErrors = new Error(errors)
            if (this.client_brand.error) return false;

            const entity = this.client_brand;
            let index = -1;
            if (entity.token)
                index = _.findIndex(this.client_brands, {token: entity.token});

            if (this.client_brands[index] !== undefined) {
                this.client_brands[index] = entity;
            } else {
                entity.token = Math.random().toString(36).substring(2, 15) + Math.random().toString(36).substring(2, 15)
                this.client_brands.push(entity)
            }

            this.client_brand = {
                ...CLIENT_BRAND_STATE,
                token: Math.random().toString(36).substring(2, 15) + Math.random().toString(36).substring(2, 15)
            }
        },
        handleDeleteClientBrandClick(token) {
            const index = _.findIndex(this.client_brands, {token: token})
            if (this.client_brands[index] !== undefined) {
                this.client_brands.splice(index, 1);
            }
        },
        handleEditClientBrandClick(token) {
            const index = _.findIndex(this.client_brands, {token: token})
            if (this.client_brands[index] !== undefined) {
                this.client_brand = {...this.client_brands[index]};
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
                .map((item, index) => `${index + 1}. ${item.label}`)
                .join("\r\n")
        },
        resetClientBrand() {
            this.client_brand = {...CLIENT_BRAND_STATE}
        },
        handleBrandSelect() {
            this.client_brand.models = []
        },
    }
}

import Error from '../../util/Error'

const DEALER_BRAND_STATE = {
    token: null,
    id: null,
    brand_id: null,
    models: [],
    error: false
};

export default {
    data() {
        return {
            dealer_brand: {...DEALER_BRAND_STATE},
            dealer_brands: [],
            dropdowns: {
                brands: [],
                models: [],
            }
        }
    },
    methods: {
        handleAddUpdateDealerBrandClick() {
            this.dealer_brand.error = false;
            this.formErrors = new Error({})
            let errors = {};

            if (!this.dealer_brand.brand_id) {
                this.dealer_brand.error = true;
                errors.brand_id = [this.$t('validation.required')];
            }

            if (this.dealer_brand.models.length <= 0) {
                errors.models = [this.$t('validation.required')];
                this.dealer_brand.error = true;
            }

            _.map(this.dealer_brands, (brand) => {
                if(brand.token !== this.dealer_brand.token) {
                    if((brand.brand_id === this.dealer_brand.brand_id) && (brand.models.some((val) => this.dealer_brand.models.indexOf(val) !== -1) )) {
                        errors.brand_id = [this.$t('validation.duplicate')];
                        this.dealer_brand.error = true;
                        this.formErrors = new Error(errors);
                        return false;
                    }
                }
            });

            this.formErrors = new Error(errors)
            if (this.dealer_brand.error) return false;

            const entity = this.dealer_brand;
            let index = -1;
            if (entity.token)
                index = _.findIndex(this.dealer_brands, {token: entity.token});

            if (this.dealer_brands[index] !== undefined) {
                this.dealer_brands[index] = entity;
            } else {
                entity.token = Math.random().toString(36).substring(2, 15) + Math.random().toString(36).substring(2, 15)
                this.dealer_brands.push(entity)
            }

            this.dealer_brand = {
                ...DEALER_BRAND_STATE,
                token: Math.random().toString(36).substring(2, 15) + Math.random().toString(36).substring(2, 15)
            }
        },
        handleDeleteDealerBrandClick(token) {
            const index = _.findIndex(this.dealer_brands, {token: token})
            if (this.dealer_brands[index] !== undefined) {
                this.dealer_brands.splice(index, 1);
            }
        },
        handleEditDealerBrandClick(token) {
            const index = _.findIndex(this.dealer_brands, {token: token})
            if (this.dealer_brands[index] !== undefined) {
                this.dealer_brand = {...this.dealer_brands[index]};
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
        resetDealerBrand() {
            this.dealer_brand.models = []
            this.dealer_brand = {...DEALER_BRAND_STATE}
            // this.dealer_brands.length = 0
        },
        handleBrandSelect() {
            this.dealer_brand.models = []
        }
    }
}

import Error from '../../util/Error'

const MANUFACTURER_DOCUMENT_STATE = {
    token: null,
    id: null,
    document: null,
    title: null,
    error: false
};

export default {
    data() {
        return {
            manufacturer_document: {...MANUFACTURER_DOCUMENT_STATE},
            manufacturer_documents: [],
        }
    },
    methods: {
        handleAddUpdateManufacturerDocumentClick() {
            this.manufacturer_document.error = false;
            this.formErrors = new Error({})
            let errors = {};

            if (!this.manufacturer_document.document || this.manufacturer_document.document.length <= 0 || !this.manufacturer_document.document.id) {
                this.manufacturer_document.error = true;
                errors.document = [this.$t('validation.required')];
            }

            if (!this.manufacturer_document.title || _.trim(this.manufacturer_document.title.length) <= 2) {
                this.manufacturer_document.error = true;
                errors.title = [this.$t('validation.required')];
            }

            _.map(this.manufacturer_documents, (manufacturer) => {
                if(manufacturer.token !== this.manufacturer_document.token) {
                    if(manufacturer.title === this.manufacturer_document.title) {
                        errors.title = [this.$t('validation.duplicate')];
                        this.manufacturer_document.error = true;
                        this.formErrors = new Error(errors);
                        return false;
                    }
                }
            });

            this.formErrors = new Error(errors)
            if(this.manufacturer_document.error) return false;

            const entity = this.manufacturer_document;
            let index = -1;
            if (entity.token)
                index = _.findIndex(this.manufacturer_documents, {token: entity.token});

            if (this.manufacturer_documents[index] !== undefined) {
                this.manufacturer_documents[index] = entity;
            } else {
                entity.token = Math.random().toString(36).substring(2, 15) + Math.random().toString(36).substring(2, 15)
                this.manufacturer_documents.push(entity)
            }

            this.manufacturer_document = {...MANUFACTURER_DOCUMENT_STATE, token: Math.random().toString(36).substring(2, 15) + Math.random().toString(36).substring(2, 15)}
        },
        handleDeleteManufacturerDocumentClick(token) {
            const index = _.findIndex(this.manufacturer_documents, {token: token})
            if (this.manufacturer_documents[index] !== undefined) {
                this.manufacturer_documents.splice(index, 1);
            }
        },
        handleEditManufacturerDocumentClick(token) {
            const index = _.findIndex(this.manufacturer_documents, {token: token})
            if (this.manufacturer_documents[index] !== undefined) {
                this.manufacturer_document = {...this.manufacturer_documents[index]};
            }
        },
        resetManufacturerDocument() {
            this.manufacturer_document = {...MANUFACTURER_DOCUMENT_STATE}
            this.manufacturer_document.error = false;
        },
    }
}

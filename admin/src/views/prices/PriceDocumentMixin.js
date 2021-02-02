import Error from '../../util/Error'

const PRICE_DOCUMENT_STATE = {
    token: null,
    id: null,
    document: null,
    title: null,
    error: false
};

export default {
    data() {
        return {
            price_document: {...PRICE_DOCUMENT_STATE},
            price_documents: [],
        }
    },
    methods: {
        handleAddUpdatePriceDocumentClick() {
            this.price_document.error = false;
            this.formErrors = new Error({})
            let errors = {};

            if (!this.price_document.document || this.price_document.document.length <= 0 || !this.price_document.document.id) {
                this.price_document.error = true;
                errors.document = [this.$t('validation.required')];
            }

            if (!this.price_document.title || _.trim(this.price_document.title.length) <= 2) {
                this.price_document.error = true;
                errors.title = [this.$t('validation.required')];
            }

            _.map(this.price_documents, (document) => {
                if(document.token !== this.price_document.token) {
                    if(document.title === this.price_document.title) {
                        errors.title = [this.$t('validation.duplicate')];
                        this.price_document.error = true;
                        this.formErrors = new Error(errors);
                        return false;
                    }
                }
            });

            this.formErrors = new Error(errors)
            if(this.price_document.error) return false;

            const entity = this.price_document;
            let index = -1;
            if (entity.token)
                index = _.findIndex(this.price_documents, {token: entity.token});

            if (this.price_documents[index] !== undefined) {
                this.price_documents[index] = entity;
            } else {
                entity.token = Math.random().toString(36).substring(2, 15) + Math.random().toString(36).substring(2, 15)
                this.price_documents.push(entity)
            }

            this.price_document = {...PRICE_DOCUMENT_STATE, token: Math.random().toString(36).substring(2, 15) + Math.random().toString(36).substring(2, 15)}
        },
        handleDeletePriceDocumentClick(token) {
            const index = _.findIndex(this.price_documents, {token: token})
            if (this.price_documents[index] !== undefined) {
                this.price_documents.splice(index, 1);
            }
        },
        handleEditPriceDocumentClick(token) {
            const index = _.findIndex(this.price_documents, {token: token})
            if (this.price_documents[index] !== undefined) {
                this.price_document = {...this.price_documents[index]};
            }
        },
        resetPriceDocument() {
            this.price_document = {...PRICE_DOCUMENT_STATE}
            this.price_document.error = false;
        },
    }
}

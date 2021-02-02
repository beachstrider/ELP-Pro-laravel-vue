import Error from '../../util/Error'

const DRIVER_DOCUMENT_STATE = {
    token: null,
    id: null,
    document: null,
    title: null,
    error: false
};

export default {
    data() {
        return {
            driver_document: {...DRIVER_DOCUMENT_STATE},
            driver_documents: [],
        }
    },
    methods: {
        handleAddUpdateDriverDocumentClick() {
            this.driver_document.error = false;
            this.formErrors = new Error({})
            let errors = {};

            if (!this.driver_document.document || this.driver_document.document.length <= 0 || !this.driver_document.document.id) {
                this.driver_document.error = true;
                errors.document = [this.$t('validation.required')];
            }

            if (!this.driver_document.title || _.trim(this.driver_document.title.length) <= 2) {
                this.driver_document.error = true;
                errors.title = [this.$t('validation.required')];
            }

            _.map(this.driver_documents, (document) => {
                if(document.token !== this.driver_document.token) {
                    if(document.title === this.driver_document.title) {
                        errors.title = [this.$t('validation.duplicate')];
                        this.driver_document.error = true;
                        this.formErrors = new Error(errors);
                        return false;
                    }
                }
            });

            this.formErrors = new Error(errors)
            if(this.driver_document.error) return false;

            const entity = this.driver_document;
            let index = -1;
            if (entity.token)
                index = _.findIndex(this.driver_documents, {token: entity.token});

            if (this.driver_documents[index] !== undefined) {
                this.driver_documents[index] = entity;
            } else {
                entity.token = Math.random().toString(36).substring(2, 15) + Math.random().toString(36).substring(2, 15)
                this.driver_documents.push(entity)
            }

            this.driver_document = {...DRIVER_DOCUMENT_STATE, token: Math.random().toString(36).substring(2, 15) + Math.random().toString(36).substring(2, 15)}
        },
        handleDeleteDriverDocumentClick(token) {
            const index = _.findIndex(this.driver_documents, {token: token})
            if (this.driver_documents[index] !== undefined) {
                this.driver_documents.splice(index, 1);
            }
        },
        handleEditDriverDocumentClick(token) {
            const index = _.findIndex(this.driver_documents, {token: token})
            if (this.driver_documents[index] !== undefined) {
                this.driver_document = {...this.driver_documents[index]};
            }
        },
        resetDriverDocument() {
            this.driver_document = {...DRIVER_DOCUMENT_STATE}
            this.driver_document.error = false;
        },
    }
}

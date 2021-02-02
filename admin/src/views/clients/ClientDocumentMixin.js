import Error from '../../util/Error'

const CLIENT_DOCUMENT_STATE = {
    token: null,
    id: null,
    document: null,
    title: null,
    error: false
};

export default {
    data() {
        return {
            client_document: {...CLIENT_DOCUMENT_STATE},
            client_documents: [],
        }
    },
    methods: {
        handleAddUpdateClientDocumentClick() {
            this.client_document.error = false;
            this.formErrors = new Error({})
            let errors = {};

            if (!this.client_document.document || this.client_document.document.length <= 0 || !this.client_document.document.id) {
                this.client_document.error = true;
                errors.document = [this.$t('validation.required')];
            }

            if (!this.client_document.title || _.trim(this.client_document.title.length) <= 2) {
                this.client_document.error = true;
                errors.title = [this.$t('validation.required')];
            }

            _.map(this.client_documents, (document) => {
                if(document.token !== this.client_document.token) {
                    if(document.title === this.client_document.title) {
                        errors.title = [this.$t('validation.duplicate')];
                        this.client_document.error = true;
                        this.formErrors = new Error(errors);
                        return false;
                    }
                }
            });

            this.formErrors = new Error(errors)
            if(this.client_document.error) return false;

            const entity = this.client_document;
            let index = -1;
            if (entity.token)
                index = _.findIndex(this.client_documents, {token: entity.token});

            if (this.client_documents[index] !== undefined) {
                this.client_documents[index] = entity;
            } else {
                entity.token = Math.random().toString(36).substring(2, 15) + Math.random().toString(36).substring(2, 15);
                this.client_documents.push(entity)
            }

            this.client_document = {...CLIENT_DOCUMENT_STATE, token: Math.random().toString(36).substring(2, 15) + Math.random().toString(36).substring(2, 15)}
        },
        handleDeleteClientDocumentClick(token) {
            const index = _.findIndex(this.client_documents, {token: token})
            if (this.client_documents[index] !== undefined) {
                this.client_documents.splice(index, 1);
            }
        },
        handleEditClientDocumentClick(token) {
            const index = _.findIndex(this.client_documents, {token: token})
            if (this.client_documents[index] !== undefined) {
                this.client_document = {...this.client_documents[index]};
            }
        },
        resetClientDocument() {
            this.client_document = {...CLIENT_DOCUMENT_STATE}
            this.client_document.error = false;
        },
    }
}

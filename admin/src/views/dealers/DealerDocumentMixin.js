import Error from '../../util/Error'

const DEALER_DOCUMENT_STATE = {
    token: null,
    id: null,
    document: null,
    title: null,
    error: false
};

export default {
    data() {
        return {
            dealer_document: {...DEALER_DOCUMENT_STATE},
            dealer_documents: [],
        }
    },
    methods: {
        handleAddUpdateDealerDocumentClick() {
            this.dealer_document.error = false;
            this.formErrors = new Error({})
            let errors = {};

            if (!this.dealer_document.document || this.dealer_document.document.length <= 0 || !this.dealer_document.document.id) {
                this.dealer_document.error = true;
                errors.document = [this.$t('validation.required')];
            }

            if (!this.dealer_document.title || _.trim(this.dealer_document.title.length) <= 2) {
                this.dealer_document.error = true;
                errors.title = [this.$t('validation.required')];
            }

            _.map(this.dealer_documents, (document) => {
                if(document.token !== this.dealer_document.token) {
                    if(document.title === this.dealer_document.title) {
                        errors.title = [this.$t('validation.duplicate')];
                        this.dealer_document.error = true;
                        this.formErrors = new Error(errors);
                        return false;
                    }
                }
            });

            this.formErrors = new Error(errors)
            if(this.dealer_document.error) return false;

            const entity = this.dealer_document;
            let index = -1;
            if (entity.token)
                index = _.findIndex(this.dealer_documents, {token: entity.token});

            if (this.dealer_documents[index] !== undefined) {
                this.dealer_documents[index] = entity;
            } else {
                entity.token = Math.random().toString(36).substring(2, 15) + Math.random().toString(36).substring(2, 15);
                this.dealer_documents.push(entity)
            }

            this.dealer_document = {...DEALER_DOCUMENT_STATE, token: Math.random().toString(36).substring(2, 15) + Math.random().toString(36).substring(2, 15)}
        },
        handleDeleteDealerDocumentClick(token) {
            const index = _.findIndex(this.dealer_documents, {token: token})
            if (this.dealer_documents[index] !== undefined) {
                this.dealer_documents.splice(index, 1);
            }
        },
        handleEditDealerDocumentClick(token) {
            const index = _.findIndex(this.dealer_documents, {token: token})
            if (this.dealer_documents[index] !== undefined) {
                this.dealer_document = {...this.dealer_documents[index]};
            }
        },
        resetDealerDocument() {
            this.dealer_document = {...DEALER_DOCUMENT_STATE}
            this.dealer_document.error = false;
        },
    }
}

import Error from '../../util/Error'

const CONTRACT_DOCUMENT_STATE = {
    token: null,
    id: null,
    document: null,
    title: null,
    error: false
};

export default {
    data() {
        return {
            contract_document: {...CONTRACT_DOCUMENT_STATE},
            contract_documents: [],
        }
    },
    methods: {
        handleAddUpdateContractDocumentClick() {
            this.contract_document.error = false;
            this.formErrors = new Error({})
            let errors = {};

            if (!this.contract_document.document || this.contract_document.document.length <= 0 || !this.contract_document.document.id) {
                this.contract_document.error = true;
                errors.document = [this.$t('validation.required')];
            }

            if (!this.contract_document.title || _.trim(this.contract_document.title.length) <= 2) {
                this.contract_document.error = true;
                errors.title = [this.$t('validation.required')];
            }

            _.map(this.contract_documents, (document) => {
                if(document.token !== this.contract_document.token) {
                    if(document.title === this.contract_document.title) {
                        errors.title = [this.$t('validation.duplicate')];
                        this.contract_document.error = true;
                        this.formErrors = new Error(errors);
                        return false;
                    }
                }
            });

            this.formErrors = new Error(errors)
            if(this.contract_document.error) return false;

            const entity = this.contract_document;
            let index = -1;
            if (entity.token)
                index = _.findIndex(this.contract_documents, {token: entity.token});

            if (this.contract_documents[index] !== undefined) {
                this.contract_documents[index] = entity;
            } else {
                entity.token = Math.random().toString(36).substring(2, 15) + Math.random().toString(36).substring(2, 15)
                this.contract_documents.push(entity)
            }

            this.contract_document = {...CONTRACT_DOCUMENT_STATE, token: Math.random().toString(36).substring(2, 15) + Math.random().toString(36).substring(2, 15)}
        },
        handleDeleteContractDocumentClick(token) {
            const index = _.findIndex(this.contract_documents, {token: token})
            if (this.contract_documents[index] !== undefined) {
                this.contract_documents.splice(index, 1);
            }
        },
        handleEditContractDocumentClick(token) {
            const index = _.findIndex(this.contract_documents, {token: token})
            if (this.contract_documents[index] !== undefined) {
                this.contract_document = {...this.contract_documents[index]};
            }
        },
        resetContractDocument() {
            this.contract_document = {...CONTRACT_DOCUMENT_STATE}
            this.contract_document.error = false;
        },
    }
}

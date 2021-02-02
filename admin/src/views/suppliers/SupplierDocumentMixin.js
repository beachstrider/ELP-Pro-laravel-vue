import Error from '../../util/Error'

const SUPPLIER_DOCUMENT_STATE = {
    token: null,
    id: null,
    document: null,
    title: null,
    error: false
};

export default {
    data() {
        return {
            supplier_document: {...SUPPLIER_DOCUMENT_STATE},
            supplier_documents: [],
        }
    },
    methods: {
        handleAddUpdateSupplierDocumentClick() {
            this.supplier_document.error = false;
            this.formErrors = new Error({})
            let errors = {};

            if (!this.supplier_document.document || this.supplier_document.document.length <= 0 || !this.supplier_document.document.id) {
                this.supplier_document.error = true;
                errors.document = [this.$t('validation.required')];
            }

            if (!this.supplier_document.title || _.trim(this.supplier_document.title.length) <= 2) {
                this.supplier_document.error = true;
                errors.title = [this.$t('validation.required')];
            }

            _.map(this.supplier_documents, (location) => {
                if(location.token !== this.supplier_document.token) {
                    if(location.title === this.supplier_document.title) {
                        errors.title = [this.$t('validation.duplicate')];
                        this.supplier_document.error = true;
                        this.formErrors = new Error(errors);
                        return false;
                    }
                }
            });

            this.formErrors = new Error(errors)
            if(this.supplier_document.error) return false;

            const entity = this.supplier_document;
            let index = -1;
            if (entity.token)
                index = _.findIndex(this.supplier_documents, {token: entity.token});

            if (this.supplier_documents[index] !== undefined) {
                this.supplier_documents[index] = entity;
            } else {
                entity.token = Math.random().toString(36).substring(2, 15) + Math.random().toString(36).substring(2, 15)
                this.supplier_documents.push(entity)
            }

            this.supplier_document = {...SUPPLIER_DOCUMENT_STATE, token: Math.random().toString(36).substring(2, 15) + Math.random().toString(36).substring(2, 15)}
        },
        handleDeleteSupplierDocumentClick(token) {
            const index = _.findIndex(this.supplier_documents, {token: token})
            if (this.supplier_documents[index] !== undefined) {
                this.supplier_documents.splice(index, 1);
            }
        },
        handleEditSupplierDocumentClick(token) {
            const index = _.findIndex(this.supplier_documents, {token: token})
            if (this.supplier_documents[index] !== undefined) {
                this.supplier_document = {...this.supplier_documents[index]};
            }
        },
        resetSupplierDocument() {
            this.supplier_document = {...SUPPLIER_DOCUMENT_STATE}
            this.supplier_document.error = false;
        },
    }
}

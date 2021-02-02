import Error from '../../util/Error'

const SUPPLIER_CONTACTS_STATE = {
    token: null,
    id: null,
    contact_id: null,
    locations: [],
    error: false
};

export default {
    data() {
        return {
            supplier_contact: {...SUPPLIER_CONTACTS_STATE},
            supplier_contacts: [],
            dropdowns: {
                locations: []
            }
        }
    },
    methods: {
        handleAddUpdateSupplierContactClick() {
            this.supplier_contact.error = false;
            this.formErrors = new Error({})
            let errors = {};

            if (!this.supplier_contact.contact_id) {
                this.supplier_contact.error = true;
                errors.contact_id = [this.$t('validation.required')];
            }

            _.map(this.supplier_contacts, (contact) => {
                if(contact.token !== this.supplier_contact.token) {
                    if((contact.contact_id === this.supplier_contact.contact_id) && (contact.locations.some((val) => this.supplier_contact.locations.indexOf(val) !== -1) )) {
                        errors.contact_id = [this.$t('validation.duplicate')];
                        this.supplier_contact.error = true;
                        this.formErrors = new Error(errors);
                        return false;
                    }
                }
            });

            this.formErrors = new Error(errors)
            if(this.supplier_contact.error) return false;

            const entity = this.supplier_contact;
            let index = -1;
            if (entity.token)
                index = _.findIndex(this.supplier_contacts, {token: entity.token});

            if (this.supplier_contacts[index] !== undefined) {
                this.supplier_contacts[index] = entity;
            } else {
                entity.token = Math.random().toString(36).substring(2, 15) + Math.random().toString(36).substring(2, 15)
                this.supplier_contacts.push(entity)
            }

            this.supplier_contact = {...SUPPLIER_CONTACTS_STATE, token: Math.random().toString(36).substring(2, 15) + Math.random().toString(36).substring(2, 15)}
        },
        handleDeleteSupplierContactClick(token) {
            const index = _.findIndex(this.supplier_contacts, {token: token})
            if (this.supplier_contacts[index] !== undefined) {
                this.supplier_contacts.splice(index, 1);
            }
        },
        handleEditSupplierContactClick(token) {
            const index = _.findIndex(this.supplier_contacts, {token: token})
            if (this.supplier_contacts[index] !== undefined) {
                this.supplier_contact = {...this.supplier_contacts[index]};
            }
        },
        getLocationsLabel(contact, location_id) {
            if(contact.locations.length > 0) {
                return contact.locations.map(item => {
                    const location = _.find(this.dropdowns.locations, {id: item});
                    return {
                        id: item,
                        label: (location && location.label ? location.label : 'Unknown Location')
                    }
                })
                    .map((item, index) => `${index+1}. ${item.label}`)
                    .join("\r\n")
            } else {
                if(location_id) {
                    contact.locations.push(location_id);
                    this.supplier_contact.locations = [];
                    return  [_.find(this.dropdowns.locations, {id: location_id})].map((item) => item.label).join(', ')
                }
            }

        },
        resetSupplierContact() {
            this.supplier_contact = {...SUPPLIER_CONTACTS_STATE}
        }
    }
}

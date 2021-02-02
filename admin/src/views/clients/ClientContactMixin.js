import Error from '../../util/Error'

const CLIENT_CONTACTS_STATE = {
    token: null,
    id: null,
    contact_id: null,
    locations: [],
    error: false
};

export default {
    data() {
        return {
            client_contact: {...CLIENT_CONTACTS_STATE},
            client_contacts: [],
            dropdowns: {
                locations: []
            }
        }
    },
    methods: {
        handleAddUpdateClientContactClick() {
            this.client_contact.error = false;
            this.formErrors = new Error({})
            let errors = {};

            if (!this.client_contact.contact_id) {
                this.client_contact.error = true;
                errors.contact_id = [this.$t('validation.required')];
            }

            _.map(this.client_contacts, (contact) => {
                if(contact.token !== this.client_contact.token) {
                    if((contact.contact_id === this.client_contact.contact_id) && (contact.locations.some((val) => this.client_contact.locations.indexOf(val) !== -1) )) {
                        errors.contact_id = [this.$t('validation.duplicate')];
                        this.client_contact.error = true;
                        this.formErrors = new Error(errors);
                        return false;
                    }
                }
            });

            this.formErrors = new Error(errors);
            if(this.client_contact.error) return false;

            const entity = this.client_contact;
            let index = -1;
            if (entity.token)
                index = _.findIndex(this.client_contacts, {token: entity.token});

            if (this.client_contacts[index] !== undefined) {
                this.client_contacts[index] = entity;
            } else {
                entity.token = Math.random().toString(36).substring(2, 15) + Math.random().toString(36).substring(2, 15)
                this.client_contacts.push(entity)
            }

            this.client_contact = {...CLIENT_CONTACTS_STATE, token: Math.random().toString(36).substring(2, 15) + Math.random().toString(36).substring(2, 15)}
        },
        handleDeleteClientContactClick(token) {
            const index = _.findIndex(this.client_contacts, {token: token})
            if (this.client_contacts[index] !== undefined) {
                this.client_contacts.splice(index, 1);
            }
        },
        handleEditClientContactClick(token) {
            const index = _.findIndex(this.client_contacts, {token: token})
            if (this.client_contacts[index] !== undefined) {
                this.client_contact = {...this.client_contacts[index]};
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
                    this.client_contact.locations = [];
                    return  [_.find(this.dropdowns.locations, {id: location_id})].map((item) => item.label).join(', ')
                }
            }

        },
        resetClientContact() {
            this.client_contact = {...CLIENT_CONTACTS_STATE}
        }
    }
}

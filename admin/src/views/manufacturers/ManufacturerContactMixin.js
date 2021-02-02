import Error from '../../util/Error'

const MANUFACTURER_CONTACTS_STATE = {
    token: null,
    id: null,
    contact_id: null,
    locations: [],
    error: false
};

export default {
    data() {
        return {
            manufacturer_contact: {...MANUFACTURER_CONTACTS_STATE},
            manufacturer_contacts: [],
            dropdowns: {
                locations: []
            }
        }
    },
    methods: {
        handleAddUpdateManufacturerContactClick() {
            this.manufacturer_contact.error = false;
            this.formErrors = new Error({})
            let errors = {};

            if (!this.manufacturer_contact.contact_id) {
                this.manufacturer_contact.error = true;
                errors.contact_id = [this.$t('validation.required')];
            }

            _.map(this.manufacturer_contacts, (contact) => {
                if(contact.token !== this.manufacturer_contact.token) {
                    if((contact.contact_id === this.manufacturer_contact.contact_id) && (contact.locations.some((val) => this.manufacturer_contact.locations.indexOf(val) !== -1) )) {
                        errors.contact_id = [this.$t('validation.duplicate')];
                        this.manufacturer_contact.error = true;
                        this.formErrors = new Error(errors);
                        return false;
                    }
                }
            });

            this.formErrors = new Error(errors)
            if(this.manufacturer_contact.error) return false;

            const entity = this.manufacturer_contact;
            let index = -1;
            if (entity.token)
                index = _.findIndex(this.manufacturer_contacts, {token: entity.token});

            if (this.manufacturer_contacts[index] !== undefined) {
                this.manufacturer_contacts[index] = entity;
            } else {
                entity.token = Math.random().toString(36).substring(2, 15) + Math.random().toString(36).substring(2, 15)
                this.manufacturer_contacts.push(entity)
            }

            this.manufacturer_contact = {...MANUFACTURER_CONTACTS_STATE, token: Math.random().toString(36).substring(2, 15) + Math.random().toString(36).substring(2, 15)}
        },
        handleDeleteManufacturerContactClick(token) {
            const index = _.findIndex(this.manufacturer_contacts, {token: token})
            if (this.manufacturer_contacts[index] !== undefined) {
                this.manufacturer_contacts.splice(index, 1);
                this.resetManufacturerContact()
            }
        },
        handleEditManufacturerContactClick(token) {
            const index = _.findIndex(this.manufacturer_contacts, {token: token})
            if (this.manufacturer_contacts[index] !== undefined) {
                this.manufacturer_contact = {...this.manufacturer_contacts[index]};
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
        resetManufacturerContact() {
            this.manufacturer_contact = {...MANUFACTURER_CONTACTS_STATE}
        }
    }
}

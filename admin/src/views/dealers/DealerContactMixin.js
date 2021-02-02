import Error from '../../util/Error'

const DEALER_CONTACTS_STATE = {
    token: null,
    id: null,
    contact_id: null,
    locations: [],
    error: false
};

export default {
    data() {
        return {
            dealer_contact: {...DEALER_CONTACTS_STATE},
            dealer_contacts: [],
            dropdowns: {
                locations: []
            }
        }
    },
    methods: {
        handleAddUpdateDealerContactClick() {
            this.dealer_contact.error = false;
            this.formErrors = new Error({})
            let errors = {};

            if (!this.dealer_contact.contact_id) {
                this.dealer_contact.error = true;
                errors.contact_id = [this.$t('validation.required')];
            }

            // if (this.dealer_contact.locations.length <= 0) {
            //     errors.locations = [this.$t('validation.required')];
            //     this.dealer_contact.error = true;
            // }

            _.map(this.dealer_contacts, (contact) => {
                if(contact.token !== this.dealer_contact.token) {
                    if((contact.contact_id === this.dealer_contact.contact_id) && (contact.locations.some((val) => this.dealer_contact.locations.indexOf(val) !== -1) )) {
                        errors.contact_id = [this.$t('validation.duplicate')];
                        this.dealer_contact.error = true;
                        this.formErrors = new Error(errors);
                        return false;
                    }
                }
            });

            this.formErrors = new Error(errors)
            if(this.dealer_contact.error) return false;

            const entity = this.dealer_contact;
            let index = -1;
            if (entity.token)
                index = _.findIndex(this.dealer_contacts, {token: entity.token});

            if (this.dealer_contacts[index] !== undefined) {
                this.dealer_contacts[index] = entity;
            } else {
                entity.token = Math.random().toString(36).substring(2, 15) + Math.random().toString(36).substring(2, 15)
                this.dealer_contacts.push(entity)
            }

            this.dealer_contact = {...DEALER_CONTACTS_STATE, token: Math.random().toString(36).substring(2, 15) + Math.random().toString(36).substring(2, 15)}
        },
        handleDeleteDealerContactClick(token) {
            const index = _.findIndex(this.dealer_contacts, {token: token})
            if (this.dealer_contacts[index] !== undefined) {
                this.dealer_contacts.splice(index, 1);
            }
        },
        handleEditDealerContactClick(token) {
            const index = _.findIndex(this.dealer_contacts, {token: token})
            if (this.dealer_contacts[index] !== undefined) {
                this.dealer_contact = {...this.dealer_contacts[index]};
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
        resetDealerContact() {
            this.dealer_contact.locations = []
            this.dealer_contact = {...DEALER_CONTACTS_STATE}
            // this.dealer_contacts.length = 0
        }
    }
}

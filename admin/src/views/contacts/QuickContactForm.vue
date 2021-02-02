<template>
	<fragment>
		<a :title="$t('button.title.addItem')"
		   v-if="$global.hasPermission('contactsstore') && allowCreate > 0"
		   v-b-tooltip.hover
		   @click="handleAddContactClick"
			class="mr-1">
			<i class="fe fe-plus"></i>
		</a>
		<a :title="$t('button.title.editItem')"
		   v-if="$global.hasPermission('contactsupdate') && hasId && allowUpdate > 0"
		   @click="handleEditContactClick"
		   v-b-tooltip.hover>
			<i class="fe fe-edit"></i>
		</a>
		<b-modal v-model="visibility" size="lg" :title="operationTitle" hide-footer>
			<div class="contact-operation">
				<form @submit.prevent="handleSubmit" autocomplete="off">
					<b-row>
						<b-col cols="12">
							<div v-show="editLoading">
								<b-skeleton-table
									:rows="5"
									:columns="2"
									:table-props="{ bordered: true, striped: true }"
								></b-skeleton-table>
							</div>

							<div v-show="!editLoading">
								<b-row>
									<b-col sm="6">
										<b-form-group
												:label="$t('input.firstName')+' *'"
												label-for="first_name"
												:invalid-feedback="formErrors.first('first_name')"
										>
											<b-form-input
													id="first_name"
													v-model="formFields.first_name"
													type="text"
													:state="((formErrors.has('first_name') ? false : null))"
													@focus="$event.target.select()"
											></b-form-input>
										</b-form-group>
									</b-col><!--/b-col-->
									<b-col sm="6">
										<b-form-group
												:label="$t('input.lastName')"
												label-for="last_name"
												:invalid-feedback="formErrors.first('last_name')"
										>
											<b-form-input
													id="last_name"
													v-model="formFields.last_name"
													type="text"
													:state="((formErrors.has('last_name') ? false : null))"
													@focus="$event.target.select()"
											></b-form-input>
										</b-form-group>
									</b-col><!--/b-col-->
								</b-row><!--/b-row-->
								<b-row>
									<b-col sm="12">
										<b-form-group
												:label="$t('input.email')+' *'"
												label-for="email"
												:invalid-feedback="formErrors.first('email')"
										>
											<b-form-input
													id="email"
													v-model="formFields.email"
													type="text"
													:state="((formErrors.has('email') ? false : null))"
													@focus="$event.target.select()"
											></b-form-input>
										</b-form-group>
									</b-col><!--/b-col-->
								</b-row><!--/b-row-->
								<b-row>
									<b-col sm="6">
										<b-form-group
												:label="$t('input.phone')+' *'"
												label-for="phone"
												:invalid-feedback="formErrors.first('phone')"
										>
											<b-form-input
													pattern="^[0-9-+()]*"
													title="+(XXX) XXX"
													id="phone"
													v-model="formFields.phone"
													type="number"
													:state="((formErrors.has('phone') ? false : null))"
													@focus="$event.target.select()"
											></b-form-input>
										</b-form-group>
									</b-col><!--/b-col-->
									<b-col sm="6">
										<b-form-group
												:label="$t('input.mobile')+' *'"
												label-for="mobile"
												:invalid-feedback="formErrors.first('mobile')"
										>
											<b-form-input
													id="mobile"
													v-model="formFields.mobile"
													type="text"
													:state="((formErrors.has('mobile') ? false : null))"
													@focus="$event.target.select()"
											></b-form-input>
										</b-form-group>
									</b-col><!--/b-col-->
								</b-row><!--/b-row-->
								<b-row>
									<b-col sm="12">
										<b-form-group
												:label="$t('input.functions')+' '"
												label-for="functions"
												:invalid-feedback="formErrors.first('functions')"
										>
											<b-form-input
													id="functions"
													v-model="formFields.functions"
													type="text"
													:state="((formErrors.has('functions') ? false : null))"
													@focus="$event.target.select()"
											></b-form-input>
										</b-form-group>
									</b-col><!--/b-col-->
								</b-row><!--/b-row-->
							</div>

							<div class="quick-modal-footer pt-1">
								<b-button
									size="sm"
									type="submit"
									variant="primary"
									:disabled="global.pendingRequests > 0"
									v-b-tooltip.hover :title="$t('button.title.save')"
								>
									<clip-loader style="display: inline" :loading="true" color="#fff"
									             size="12px"
									             v-if="global.pendingRequests > 0"></clip-loader>
									<i class="fa fa-save mr-1"></i>
									{{$t('button.save')}}
								</b-button>
								<b-button variant="warning" class="ml-3"
								          size="sm" @click="handleOperationClose"
								          v-b-tooltip.hover :title="$t('button.title.cancel')">
									<i class="fa fa-arrow-left mr-1"></i> {{$t('button.cancel')}}
								</b-button>
							</div><!--/.quick-modal-footer-->
						</b-col><!--/b-col-->
					</b-row><!--/b-col-->
				</form>
			</div><!-- /.contact-operation -->
		</b-modal>
	</fragment>
</template>
<script>
    import { Fragment } from 'vue-fragment'
    import Error from "./../../util/Error";
    import {request} from "../../util/Request";
    import {handleServerError} from "../../util/Utils";
    import {mapState} from "vuex";
    import {itemEditFails} from "../../util/Notify";

    const FORM_STATE = {
        name: null,
        first_name: null,
        last_name: null,
        email: null,
        phone: null,
        mobile: null,
        functions: null,
        _method: 'post',
    };

    export default {
        props: ['allowCreate', 'allowUpdate', 'id', 'afterCreate', 'afterUpdate', 'afterCancel'],
        components: {
            Fragment
        },
	    data() {
	        return {
                formErrors: new Error({}),
                formFields: {...FORM_STATE},
		        visibility: false,
                editLoading: false,
                operationTitle: this.$t('title.addContact')
	        }
	    },
		methods: {
            async handleSubmit() {
                this.formErrors = new Error({})
                try {
                    const response = await request({
                        url: this.formFields.id ? 'contacts/update' : 'contacts/create',
                        method: 'post',
                        data: this.formFields,
                    })

                    if (this.formFields.id) {
	                    this.handleAfterContactUpdate(this.formFields, this.formFields.id);
                    } else {
                        this.handleAfterContactCreate(response.data);
                    }

                    this.handleOperationClose()
                } catch (error) {
                    if (error.request && error.request.status && error.request.status === 422) {
                        this.formErrors = new Error(JSON.parse(error.request.responseText).errors)
                        return false
                    }

                    handleServerError(error, this.$notification)
                }
            },
            async handleEditClick(id) {
                try {
                    this.editLoading = true
                    const response = await request({
                        method: 'get',
                        url: `/contacts/detail/${id}`,
                    })
                    this.operationTitle = this.$t('title.addContact')
                    const {data} = response
                    this.formFields = {...data}
                } catch (e) {
                    itemEditFails(this.$notification)
                    this.formFields = {...FORM_STATE}
                } finally {
                    this.editLoading = false
                }
            },
            handleAddContactClick() {
                this.operationTitle = this.$t('title.addContact');
                this.visibility = true
            },
            handleEditContactClick() {
                this.operationTitle = this.$t('title.editContact');
                this.visibility = true
	            this.handleEditClick(this.id)
            },
            handleOperationClose() {
                this.formFields = {...FORM_STATE}
                this.visibility = false
	            this.handleAfterOperationCancel()
            },
            handleAfterOperationCancel() {
                if (this.afterCancel) {
                    this.afterCancel()
                }
            },
            handleAfterContactCreate(inputs) {
                if (this.afterCreate) {
                    this.afterCreate(inputs)
                }
            },
            handleAfterContactUpdate(inputs) {
                if (this.afterUpdate) {
                    this.afterUpdate(inputs)
                }
            },
            handleNameBlur() {
                let names = this.formFields.name.split(' ');
                if(names.length > 0) {
                    if(!this.formFields.first_name) {
                        this.formFields.first_name = names[0];
                    }

                    delete names[0]
                    names = names.join(" ");
                    if(!this.formFields.last_name) {
                        this.formFields.last_name = names;
                    }
                }
            }
		},
	    computed: {
            ...mapState([
                'global',
                'settings'
            ]),
            hasId() {
                return (this.id)
            },
	    }
	}
</script>
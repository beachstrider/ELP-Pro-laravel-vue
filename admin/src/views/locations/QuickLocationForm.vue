<template>
	<fragment>
		<a :title="$t('button.title.addItem')"
		   v-if="$global.hasPermission('locationsstore') && allowCreate > 0"
		   v-b-tooltip.hover
		   @click="handleAddLocationClick"
			class="mr-1">
			<i class="fe fe-plus"></i>
		</a>
		<a :title="$t('button.title.editItem')"
		   v-if="$global.hasPermission('locationsupdate') && hasId && allowUpdate > 0"
		   @click="handleEditLocationClick"
		   v-b-tooltip.hover>
			<i class="fe fe-edit"></i>
		</a>
		<b-modal v-model="visibility" size="lg" :title="operationTitle" hide-footer>
			<div class="location-operation">
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
									<b-col cols="12">
										<b-row>
											<b-col sm="6">
												<b-form-group
														:label="$t('input.country')+' *'"
														label-for="country"
														:invalid-feedback="formErrors.first('country')">
													<treeselect
															:multiple="false"
															:options="dropdowns.countries"
															placeholder=""
															v-model="formFields.country"
															:class="[{'invalid is-invalid': (formErrors.has('country'))}]"
													/>
												</b-form-group>
											</b-col><!--/b-col-->
                                            <b-col sm="6">
                                                <b-form-group
                                                    :label="$t('input.code')+' *'"
                                                    label-for="code"
                                                    :invalid-feedback="formErrors.first('code')"
                                                >
                                                    <b-form-input
                                                        id="code"
                                                        v-model="formFields.code"
                                                        type="text"
                                                        :state="((formErrors.has('code') ? false : null))"
                                                        ref="code"
                                                        @focus="$event.target.select()"
                                                    ></b-form-input>
                                                </b-form-group>
                                            </b-col><!--/b-col-->
										</b-row><!--/b-row-->
										<b-row>
                                            <b-col sm="6">
                                                <b-form-group
                                                    :label="$t('input.street')+' *'"
                                                    label-for="street"
                                                    :invalid-feedback="formErrors.first('street')"
                                                >
                                                    <b-form-input
                                                        id="street"
                                                        v-model="formFields.street"
                                                        type="text"
                                                        :state="((formErrors.has('street') ? false : null))"
                                                        ref="street"
                                                        @focus="$event.target.select()"
                                                    ></b-form-input>
                                                </b-form-group>
                                            </b-col><!--/b-col-->
											<b-col sm="6">
												<b-form-group
														:label="$t('input.street_no')+' *'"
														label-for="street_no"
														:invalid-feedback="formErrors.first('street_no')"
												>
													<b-form-input
															id="street_no"
															v-model="formFields.street_no"
															type="text"
															:state="((formErrors.has('street_no') ? false : null))"
															@focus="$event.target.select()"
													></b-form-input>
												</b-form-group>
											</b-col><!--/b-col-->
										</b-row><!--/b-row-->
										<b-row>
                                            <b-col sm="6">
                                                <b-form-group
                                                    :label="$t('input.zip')+' *'"
                                                    label-for="zip"
                                                    :invalid-feedback="formErrors.first('zip')"
                                                >
                                                    <b-form-input
                                                        id="zip"
                                                        v-model="formFields.zip"
                                                        type="number"
                                                        :state="((formErrors.has('zip') ? false : null))"
                                                        @focus="$event.target.select()"
                                                    ></b-form-input>
                                                </b-form-group>
                                            </b-col><!--/b-col-->
											<b-col sm="6">
												<b-form-group
														:label="$t('input.city')+' *'"
														label-for="city"
														:invalid-feedback="formErrors.first('city')"
												>
													<b-form-input
															id="city"
															v-model="formFields.city"
															type="text"
															:state="((formErrors.has('city') ? false : null))"
															@focus="$event.target.select()"
													></b-form-input>
												</b-form-group>
											</b-col><!--/b-col-->
										</b-row><!--/b-row-->
										<b-row>
											<b-col sm="6">
												<b-form-group
														:label="$t('input.fromOpeningHours')+' *'"
														label-for="from_opening_hours"
														:invalid-feedback="formErrors.first('from_opening_hours')"
												>
													<b-form-input
															id="from_opening_hours"
															v-model="formFields.from_opening_hours"
															type="number"
															step=".05"
															:state="((formErrors.has('from_opening_hours') ? false : null))"
															@focus="$event.target.select()"
													></b-form-input>
												</b-form-group>
											</b-col><!--/b-col-->
											<b-col sm="6">
												<b-form-group
														:label="$t('input.toOpeningHours')+' *'"
														label-for="to_opening_hours"
														:invalid-feedback="formErrors.first('to_opening_hours')"
												>
													<b-form-input
															id="to_opening_hours"
															v-model="formFields.to_opening_hours"
															type="number"
															step=".05"
															:state="((formErrors.has('to_opening_hours') ? false : null))"
															@focus="$event.target.select()"
													></b-form-input>
												</b-form-group>
											</b-col><!--/b-col-->
										</b-row><!--/b-row-->
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
			</div><!-- /.location-operation -->
		</b-modal>
	</fragment>
</template>
<script>
    import { Fragment } from 'vue-fragment'
    import Error from "./../../util/Error"
    import {request} from "../../util/Request"
    import {handleServerError} from "../../util/Utils"
    import {mapState} from "vuex"
    import {itemEditFails} from "../../util/Notify"
    import Treeselect from '@riophae/vue-treeselect'

    const FORM_STATE = {
        location_type_id: null,
        street: null,
        street_no: null,
        zip: null,
        city: null,
        country: null,
		  from_opening_hours: null,
		  to_opening_hours: null,
        _method: 'post',
    };

    export default {
        props: ['allowCreate', 'allowUpdate', 'id', 'afterCreate', 'afterUpdate', 'afterCancel'],
        components: {
            Fragment,
            Treeselect
        },
	    data() {
	        return {
                formErrors: new Error({}),
                formFields: {...FORM_STATE},
		        visibility: false,
                editLoading: false,
                operationTitle: this.$t('title.addLocation'),
                dropdowns: {
                    countries: [],
                }
	        }
	    },
	    mounted() {
            this.getCountries();
        },
		methods: {
            async handleSubmit() {
                this.formErrors = new Error({})
                try {
                    const response = await request({
                        url: this.formFields.id ? 'locations/update' : 'locations/create',
                        method: 'post',
                        data: this.formFields,
                    })

                    if (this.formFields.id) {
	                    this.handleAfterLocationUpdate(this.formFields, this.formFields.id);
                    } else {
                        this.handleAfterLocationCreate(response.data);
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
                        url: `/locations/detail/${id}`,
                    })
                    this.operationTitle = this.$t('title.addLocation')
                    const {data} = response
                    this.formFields = {...data}
                } catch (e) {
                    itemEditFails(this.$notification)
                    this.formFields = {...FORM_STATE}
                } finally {
                    this.editLoading = false
                }
            },
            async getCountries() {
                try {
                    const response = await request({
                        url: '/dropdowns/countries',
                        method: "post"
                    })

                    const {data} = response
                    this.dropdowns.countries = data

                } catch (e) {
                    this.dropdowns.countries = []
                }
            },
            handleAddLocationClick() {
                this.operationTitle = this.$t('title.addLocation');
                this.visibility = true
            },
            handleEditLocationClick() {
                this.operationTitle = this.$t('title.editLocation');
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
            handleAfterLocationCreate(inputs) {
                if (this.afterCreate) {
                    this.afterCreate(inputs)
                }
            },
            handleAfterLocationUpdate(inputs) {
                if (this.afterUpdate) {
                    this.afterUpdate(inputs)
                }
            },
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

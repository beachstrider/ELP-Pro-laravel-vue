<template>
	<fragment>
		<a :title="$t('button.title.addItem')"
		   v-if="$global.hasPermission('modelsstore') && allowCreate > 0"
		   v-b-tooltip.hover
		   @click="handleAddModelClick"
			class="mr-1">
			<i class="fe fe-plus"></i>
		</a>
		<a :title="$t('button.title.editItem')"
		   v-if="$global.hasPermission('modelsupdate') && hasId && allowUpdate > 0"
		   @click="handleEditModelClick"
		   v-b-tooltip.hover>
			<i class="fe fe-edit"></i>
		</a>
		<b-modal v-model="visibility" size="lg" :title="operationTitle" hide-footer>
			<div class="model-operation">
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
												:label="$t('input.model')+' *'"
												label-for="title"
												:invalid-feedback="formErrors.first('title')"
										>
											<b-form-input
													id="title"
													v-model="formFields.title"
													type="text"
													:state="((formErrors.has('title') ? false : null))"
											></b-form-input>
										</b-form-group>
									</b-col><!--/b-col-->
									<b-col sm="6">
										<b-form-group
												:label="$t('input.type')+' *'"
												label-for="type"
												:invalid-feedback="formErrors.first('type')"
										>
											<b-form-input
													id="type"
													v-model="formFields.type"
													type="text"
													:state="((formErrors.has('type') ? false : null))"
											></b-form-input>
										</b-form-group>
									</b-col><!--/b-col-->
									<b-col sm="4">
										<b-form-group
												:label="$t('input.length')+' *'"
												label-for="length"
												:invalid-feedback="formErrors.first('length')"
										>
											<b-form-input
													id="length"
													step=".01"
													v-model="formFields.length"
													type="number"
													:state="((formErrors.has('length') ? false : null))"
													@focus="$event.target.select()"
											></b-form-input>
										</b-form-group>
									</b-col><!--/b-col-->
									<b-col sm="4">
										<b-form-group
												:label="$t('input.width')+' *'"
												label-for="width"
												:invalid-feedback="formErrors.first('width')"
										>
											<b-form-input
													id="width"
													step=".01"
													v-model="formFields.width"
													type="number"
													:state="((formErrors.has('width') ? false : null))"
													@focus="$event.target.select()"
											></b-form-input>
										</b-form-group>
									</b-col><!--/b-col-->
									<b-col sm="4">
										<b-form-group
												:label="$t('input.height')+' *'"
												label-for="height"
												:invalid-feedback="formErrors.first('height')"
										>
											<b-form-input
													id="height"
													type="number"
													step=".01"
													v-model="formFields.height"
													:state="((formErrors.has('height') ? false : null))"
													@focus="$event.target.select()"
											></b-form-input>
										</b-form-group>
									</b-col><!--/b-col-->
									<b-col sm="6">
										<b-form-group
												:label="$t('input.deliveryFactors')+' *'"
												label-for="delivery_factors"
												:invalid-feedback="formErrors.first('delivery_factors')"
										>
											<b-form-input
													id="delivery_factors"
													v-model="formFields.delivery_factors"
													type="number"
													:state="((formErrors.has('delivery_factors') ? false : null))"
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
			</div><!-- /.model-operation -->
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
        title: null,
        brand_id: null,
        type: null,
        length: null,
        width: null,
        height: null,
        delivery_factors: null,
        is_active: true,
        _method: 'post',
    };

    export default {
        props: ['allowCreate', 'allowUpdate', 'id', 'afterCreate', 'afterUpdate', 'afterCancel', 'dependBrandId'],
        components: {
            Fragment
        },
	    data() {
	        return {
                formErrors: new Error({}),
                formFields: {...FORM_STATE},
		        visibility: false,
                editLoading: false,
                operationTitle: this.$t('title.addModel')
	        }
	    },
		methods: {
            async handleSubmit() {
                this.formErrors = new Error({})
                try {
                    const response = await request({
                        url: this.formFields.id ? 'models/update' : 'models/create',
                        method: 'post',
                        data: this.formFields,
                    })

                    if (this.formFields.id) {
	                    this.handleAfterModelUpdate(this.formFields, this.formFields.id);
                    } else {
                        this.handleAfterModelCreate(response.data);
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
                        url: `/models/detail/${id}`,
                    })
                    this.operationTitle = this.$t('title.addModel')
                    const {data} = response
                    const {brand} = data
                    delete data.brand
                    this.formFields = {...data, is_active: (data.is_active > 0), brand_id: (brand ? brand.id : null)}
                } catch (e) {
                    itemEditFails(this.$notification)
                    this.formFields = {...FORM_STATE}
                } finally {
                    this.editLoading = false
                }
            },
            handleAddModelClick() {
                this.operationTitle = this.$t('title.addModel');
                this.visibility = true
            },
            handleEditModelClick() {
                this.operationTitle = this.$t('title.editModel');
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
            handleAfterModelCreate(inputs) {
                if (this.afterCreate) {
                    this.afterCreate(inputs)
                }
            },
            handleAfterModelUpdate(inputs) {
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
	    },
	    watch: {
            dependBrandId: {
                immediate: true,
	            handler(newVal, oldVal) {
		            this.formFields.brand_id = newVal
	            }
            }
	    }
	}
</script>

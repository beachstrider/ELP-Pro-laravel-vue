<template>
	<fragment>
		<a :title="$t('button.title.addItem')"
		   v-if="$global.hasPermission('locationtypesstore') && allowCreate > 0"
		   v-b-tooltip.hover
		   @click="handleAddLocationTypeClick"
			class="mr-1">
			<i class="fe fe-plus"></i>
		</a>
		<a :title="$t('button.title.editItem')"
		   v-if="$global.hasPermission('locationtypesupdate') && hasId && allowUpdate > 0"
		   @click="handleEditLocationTypeClick"
		   v-b-tooltip.hover>
			<i class="fe fe-edit"></i>
		</a>
		<b-modal v-model="visibility" size="lg" :title="operationTitle" hide-footer>
			<div class="location-type-operation">
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
									<b-col sm="12">
										<b-form-group
												:label="$t('input.title')+' *'"
												label-for="title"
												:invalid-feedback="formErrors.first('title')"
										>
											<b-form-input
													id="title"
													v-model="formFields.title"
													type="text"
													:state="((formErrors.has('title') ? false : null))"
													ref="title"
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
			</div><!-- /.location-type-operation -->
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
        is_active: true,
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
                operationTitle: this.$t('title.addLocationType')
	        }
	    },
		methods: {
            async handleSubmit() {
                this.formErrors = new Error({})
                try {
                    const response = await request({
                        url: this.formFields.id ? 'location/types/update' : 'location/types/create',
                        method: 'post',
                        data: this.formFields,
                    })

                    if (this.formFields.id) {
	                    this.handleAfterLocationTypeUpdate(this.formFields, this.formFields.id);
                    } else {
                        this.handleAfterLocationTypeCreate(response.data);
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
                        url: `/location/types/detail/${id}`,
                    })
                    this.operationTitle = this.$t('title.addLocationType')
                    const {data} = response
                    this.formFields = {...data, is_active: (data.is_active > 0)}
                } catch (e) {
                    itemEditFails(this.$notification)
                    this.formFields = {...FORM_STATE}
                } finally {
                    this.editLoading = false
                }
            },
            handleAddLocationTypeClick() {
                this.operationTitle = this.$t('title.addLocationType');
                this.visibility = true
            },
            handleEditLocationTypeClick() {
                this.operationTitle = this.$t('title.editLocationType');
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
            handleAfterLocationTypeCreate(inputs) {
                if (this.afterCreate) {
                    this.afterCreate(inputs)
                }
            },
            handleAfterLocationTypeUpdate(inputs) {
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
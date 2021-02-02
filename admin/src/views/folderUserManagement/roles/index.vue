<template>
	<div v-if="show">
		<div class="card">
			<div class="card-header card-header-flex pb-2">
				<div class="w-100 mt-2">
					<div class="row">
						<div class="col-8">
							<h5 class="mt-3 ml-0 mr-3 mb-2">
								<strong>
									<template v-if="operation === null">{{$t('title.userRoles')}}</template>
									<template v-else>{{ $t(operationTitle) }}</template>
								</strong>
							</h5>
						</div>
						<div class="col-4 text-right">
							<div v-if="operation === null">
								<div class="mt-3">
									<!-- Using components -->
									<b-input-group class="mt-3">
										<b-form-input type="search" class="form-control form-control-sm"
										              :placeholder="$t('input.whatAreYouLookingFor')"
										              v-on:keyup.enter="handleSearch" v-model="search"></b-form-input>
										<b-input-group-append>
											<b-button @click="setOperation('add')" variant="info" size="sm"
											          :title="$t('button.title.addNewItem')"
											          v-if="$global.hasPermission('rolesstore')" v-b-tooltip.hover>
												<i class="fe fe-plus"></i> {{$t('button.addNew')}}
											</b-button>
											<b-button size="sm" :title="$t('button.title.filterRecords')"
											          variant="outline-info"
											          @click="filters.visible = !filters.visible" v-b-tooltip.hover
											          v-if="$global.hasPermission('rolesview')">
												<i class="fa fa-filter"></i>
											</b-button>
											<b-button size="sm" :title="$t('button.title.resetList')"
											          variant="outline-info"
											          @click="handleResetClick()" v-b-tooltip.hover>
												<i class="fa fa-refresh"></i>
											</b-button>
										</b-input-group-append>
									</b-input-group>
								</div>
							</div>
							<div v-else>
								<b-button variant="warning" size="sm" class="mt-3" @click="handleOperationClose()"
								          v-b-tooltip.hover :title="$t('button.title.cancel')">
									<i class="fa fa-arrow-left"></i> {{$t('button.cancel')}}
								</b-button>
							</div>
						</div>
					</div><!-- /.row -->
				</div><!-- /.w-100 -->
			</div><!-- /.card-header -->
			<div class="card-body">
				<div v-show="operation === null">
					<div class="roles-table">
						<b-table hover responsive
						         ref="table"
						         :busy="listingLoading"
						         :items="dataSource"
						         :fields="columns"
						         :sort-by.sync="sortField"
						         @sort-changed="handleSortChange">
							<template v-slot:cell(id)="record">
								{{ record.index + 1 }}
							</template>
							<template v-slot:cell(permissions)="record">
								<a-tag v-for="(permission, index) in record.item.permissions" :key="index"
								       color="blue"> {{ permission.name }}
								</a-tag>
							</template>
							<template v-slot:cell(action)="record">
								<a @click="setOperation('edit', record.item.id)"
								        :title="$t('button.title.editItem')" v-if="$global.hasPermission('rolesupdate') && record.item.name !== 'SuperAdmin'"
								        v-b-tooltip.hover>
									<i class="fe fe-edit"></i>
								</a>

								<!--<a-popconfirm title="Are you sure？" @confirm="handleDeleteClick(record.item.id)"-->
								              <!--v-if="$global.hasPermission('rolesdestroy')">-->
									<!--<i slot="icon" class="fe fe-trash"></i>-->
									<!--<a type="type" class="ml-1"-->
									        <!--:title="$t('button.title.deleteItem')"-->
									        <!--v-b-tooltip.hover>-->
										<!--<i class="fe fe-trash"></i>-->
									<!--</a>-->
								<!--</a-popconfirm>-->
							</template>
						</b-table><!-- /.b-table -->
						<div class="clearfix">
							<div class="float-right">
								<b-pagination
										v-model="pagination.current"
										:total-rows="pagination.total"
										:per-page="pagination.perPage"
										:first-text="$t('paginations.first')"
										:prev-text="$t('paginations.prev')"
										:next-text="$t('paginations.next')"
										:last-text="$t('paginations.last')"
								></b-pagination>
							</div><!-- /.pull-right -->
						</div><!-- /.clearfix -->
					</div><!-- /.roles-table -->
				</div><!-- /div -->
				<div v-show="operation !== null && operation !== 'detail'">
					<div class="roles-operation">
						<b-container fluid>
							<form @submit.prevent="handleSubmit" autocomplete="off">
								<b-row>
									<b-col class="p-0" cols="12" md="12" lg="6" sm="12">
										<b-row>
											<b-col sm="6">
												<b-form-group
														:label="$t('input.name')+' *'"
														label-for="name"
														:invalid-feedback="formErrors.first('name')"
												>
													<b-form-input
															id="name"
															v-model="formFields.name"
															type="text"
															:state="((formErrors.has('name') ? false : null))"
															ref="name"
															@focus="$event.target.select()"
													></b-form-input>
												</b-form-group>
											</b-col><!--/b-col-->
										</b-row><!--/b-row-->
										<b-row>
											<b-col sm="4" v-for="(permission, index) in formFields.permissions"
											       :key="index">
												<b-form-checkbox
														v-model="permission.checked"
														:value="true"
														:unchecked-value="false"
												>
													{{ permission.name }}
												</b-form-checkbox>
											</b-col>
										</b-row>
										<hr/>
										<b-row class="operation-footer">
											<b-col sm="12">
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
												          size="sm"
												          @click="handleOperationClose(); resetPermissions()"
												          v-b-tooltip.hover :title="$t('button.title.cancel')">
													<i class="fa fa-arrow-left mr-1"></i> {{$t('button.cancel')}}
												</b-button>
											</b-col>
										</b-row>
									</b-col><!--/b-col-->
								</b-row><!--/b-row-->
							</form><!--/form-->
						</b-container><!--/b-container-->
					</div><!--/.roles-operation-->
				</div>
			</div><!-- /.card-body -->
			<div class="filter-container">
				<a-drawer
						placement="left"
						:width="'360px'"
						:wrapStyle="{overflow: 'auto',paddingBottom: '108px'}"
						:closable="false"
						@close="filters.visible = !filters.visible"
						:visible="!operation && filters.visible"
						:zIndex="10"
						:title="$t('title.advanceFilters')"
				>
					<form @submit.prevent="loadList(listQueryParams, true)" autocomplete="off">
						<b-row>
							<b-col sm="12">
								<b-form-group :label="$t('input.name')">
									<b-form-input v-model="filters.name" trim></b-form-input>
								</b-form-group>
							</b-col>
							<b-col sm="12">
								<b-form-group
										:label="$t('input.fromAddedDate')"
										label-for="fromDate">
									<b-form-datepicker placeholder="" id="fromDate" v-model="filters.from_date"
									                   class="mb-2"></b-form-datepicker>
								</b-form-group>
							</b-col><!--/b-col-->
							<b-col sm="12">
								<b-form-group
										:label="$t('input.toAddedDate')"
										label-for="toDate">
									<b-form-datepicker placeholder="" id="toDate" v-model="filters.to_date"
									                   class="mb-2"></b-form-datepicker>
								</b-form-group>
							</b-col><!--/b-col-->
						</b-row>
						<div class="drawer-footer">
							<b-button size='sm' variant="info" @click="filters.visible = !filters.visible"
							          class="mr-2" :title="$t('button.title.closePanel')" v-b-tooltip.hover>
								{{$t('button.close')}}
							</b-button>
							<b-button size='sm' variant="warning" @click="handleResetFilterClick" class="mr-2"
							          :title="$t('button.title.resetFilter')" v-b-tooltip.hover>
								{{$t('button.reset')}}
							</b-button>
							<b-button size='sm' variant="primary" button="submit" type="filled"
							          :title="$t('button.title.filterRecords')" v-b-tooltip.hover>
								{{$t('button.apply')}}
							</b-button>
						</div><!-- /.drawer-footer -->
					</form>
				</a-drawer>
			</div><!-- /.filter-container -->
		</div><!-- /.card -->
	</div>
</template>
<script>
    import ListingMixin from "../../../util/ListingMixin"
    import Error from "../../../util/Error";
    import {mapState} from "vuex"
    import Datepicker from 'vuejs-datepicker'
    import {request} from "../../../util/Request";
    import {itemAdded, itemUpdated, itemDeleted, itemDeleteFails, itemEditFails} from "../../../util/Notify";
    import {handleServerError} from "../../../util/Utils";

    const FORM_STATE = {
        name: null,
        permissions: [],
        _method: 'post',
    };

    const FILTER_STATE = {
        name: null,
        visible: false,
        from_date: null,
        to_date: null,
    };

    const COLUMN_DEFINATION = (self) => [
        {
            label: '#',
            key: 'id',
            sortable: true,
            sortKey: 'id',
        },
        {
            label: self.$t('column.name'),
            key: 'name',
            sortable: true,
            sortKey: 'name'
        },
        {
            label: self.$t('column.permission'),
            key: 'permissions',
            sortable: false,
        },
        (self.$global.hasAnyPermission(['rolesupdate', 'rolesdestroy']) ?
            {
                label: self.$t('column.action'),
                class: 'text-right',
                key: 'action',
                width: 150,
            } : {})
    ];

    export default {
        mixins: [ListingMixin],
        components: {
            Datepicker
        },
        data() {
            return {
                operationTitle: 'title.addRole',
                formFields: {...FORM_STATE},
                filters: {...FILTER_STATE},
                listUrl: 'roles',
                columns: COLUMN_DEFINATION(this),
                focusable: 'name',
                show: true,
            }
        },
        mounted() {
            this.getPermissions()
            this.setFocus()
            if (this.$route.query && this.$route.query.operation && this.$route.query.oToken) {
                this.setFocus()
                this.handleEditClick(this.$route.query.oToken)
            }
        },
        methods: {
            setOperation(operation = 'add', operationToken = null) {
                this.operationTitle = (operation === 'add' ? 'title.addRole' : 'title.editRole')
                this.$router.replace({
                    query: Object.assign({},
                        this.$route.query,
                        {
                            ...this.listQueryParams,
                            operation: operation,
                            oToken: operationToken,
                        }
                    )
                }).then(() => {
                    this.setFocus()
                }).catch(() => {
                })
            },
            async handleSubmit() {
                this.formErrors = new Error({})
                try {
                    const response = await request({
                        url: this.formFields.id ? 'roles/update' : 'roles/create',
                        method: "post",
                        data: {
                            ...this.formFields,
                            permissions: this.formFields.permissions.filter((item) => item.checked).map((item) => {
                                return item.id
                            })
                        }
                    });

                    if (this.formFields.id) {
                        itemUpdated(this.$notification)
                    } else {
                        itemAdded(this.$notification)
                    }

                    this.handleOperationClose()
                } catch (error) {
                    if (error.request && error.request.status && error.request.status === 422) {
                        this.formErrors = new Error(JSON.parse(error.request.responseText).errors)
                        return false;
                    }

                    handleServerError(error, this.$notification)
                }
            },
            async handleDeleteClick(id) {
                try {
                    const response = await request({
                        method: "post",
                        url: `/roles/delete`,
                        data: {
                            id: id
                        }
                    });
                    this.loadList(this.listQueryParams)
                    itemDeleted(this.$notification)
                } catch (errors) {
                    itemDeleteFails(this.$notification)
                }
            },
            async handleEditClick(id) {
                try {
                    const response = await request({
                        method: "get",
                        url: `/roles/detail/${id}`,
                    })
                    this.operationTitle = 'title.editRole'
                    const {data} = response

                    const matchedIndexes = data.permissions
                        .map((item) => this.formFields.permissions.findIndex((subkey) => subkey.id === item.id))

                    matchedIndexes.map((item, index) => {
                        this.formFields.permissions[item].checked = true;
                    })

                    this.formFields = {
                        ...data,
                        permissions: this.formFields.permissions
                    }
                } catch (e) {
                    itemEditFails(this.$notification)
                    this.formFields = {...FORM_STATE}
                }
            },
            async getPermissions() {
                try {
                    const response = await request({
                        url: '/dropdowns/permissions',
                        method: "post"
                    })

                    this.formFields.permissions = response.data.map((item) => {
                        return {
                            id: item.id,
                            name: item.name,
                            checked: false
                        }
                    })
                    this.permissions = this.formFields.permissions;
                } catch (e) {

                }
            },
            hasListAccess() {
                return this.$global.hasPermission('rolesview');
            },
            getExtraParams() {
                return {
                    filters: {
                        ...this.filters,
                        from_date: ((this.filters.from_date) ? this.$global.dateToUtcDate(this.filters.from_date, 'YYYY-MM-DD', 'YYYY-MM-DD') : ''),
                        to_date: ((this.filters.to_date) ? this.$global.dateToUtcDate(this.filters.to_date, 'YYYY-MM-DD', 'YYYY-MM-DD') : '')
                    }
                }
            },
            handleResetFilterClick() {
                this.filters = {...FILTER_STATE}
                this.isFilterApplied = 'reset'
                this.loadList(this.listQueryParams)
            },
            afterCloseOperation() {
                this.formFields = {...FORM_STATE}
                this.resetPermissions()
            },
            resetPermissions() {
                this.formFields.permissions = this.permissions.map((item) => {
                    return {...item, checked: false}
                })
            },
            handleAfterResetEvent() {
                this.handleResetFilterClick()
            }
        },
        computed: {
            ...mapState([
                'global',
                'settings'
            ]),
        },
    }
</script>

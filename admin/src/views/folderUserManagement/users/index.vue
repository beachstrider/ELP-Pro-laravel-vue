<template>
	<div v-if="show">
		<div class="card">
			<div class="card-header card-header-flex pb-2">
				<div class="w-100 mt-2">
					<div class="row">
						<div class="col-8">
							<h5 class="mt-3 ml-0 mr-3 mb-2">
								<strong>
									<template v-if="operation === null">{{$t('title.users')}}</template>
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
											          v-if="$global.hasPermission('usersstore')" v-b-tooltip.hover>
												<i class="fe fe-plus"></i> {{$t('button.addNew')}}
											</b-button>
											<b-button size="sm" :title="$t('button.title.filterRecords')"
											          variant="outline-info"
											          @click="filters.visible = !filters.visible" v-b-tooltip.hover
											          v-if="$global.hasPermission('usersview')">
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
								<b-button variant="warning" size="sm" class="mt-3"
								          @click="handleOperationClose()"
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
					<div class="users-table">
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
							<template v-slot:cell(profile)="record">
								<a-avatar shape="square" :size="64" icon="user"
								          v-if="record.item.profile_pic && record.item.profile_pic.download_url"
								          :src="record.item.profile_pic.system_url+'/thumbnail/64x64/'+record.item.profile_pic.name2"/>
								<a-avatar shape="square" :size="64" icon="user" v-else/>
							</template>
							<template v-slot:cell(roles)="record">
								<a-tag v-for="(role, index) in record.item.roles" :key="index"
								       color="blue"> {{ role.name }}
								</a-tag>
							</template>
							<template v-slot:cell(is_suspended)="record">
                                <span v-if="!record.item.is_suspended"
                                      class="badge badge-pill badge-success">Active</span>
								<span v-if="record.item.is_suspended"
								      class="badge badge-pill badge-warning">Suspended</span>
							</template>
							<template v-slot:cell(action)="record">
								<a  @click="setOperation('edit', record.item.id)"
								        :title="$t('button.title.editItem')" v-if="$global.hasPermission('usersupdate') && (authUser && authUser.id !== record.item.id)"
								        v-b-tooltip.hover>
									<i class="fe fe-edit"></i>
								</a>

								<a class="ml-1"
								        v-if="!record.item.is_suspended &&
								        $global.hasPermission('usersupdate') &&
								        (authUser && authUser.id !== record.item.id) &&
								        record.item.roles.filter(item => item.name === 'SuperAdmin').length <= 0"
								        @click="handleSuspendClick(record.item)"
								        :title="$t('button.title.suspendUser')" v-b-tooltip.hover>
									<i class="fe fe-minus-circle"></i>
								</a>

								<a class="ml-1"
								        v-if="record.item.is_suspended && (authUser && authUser.id !== record.item.id) && $global.hasPermission('usersupdate') && record.item.roles.filter(item => item.name === 'SuperAdmin').length <= 0"
								        @click="handleActivateClick(record.item)"
								        :title="$t('button.title.activateUser')" v-b-tooltip.hover>
									<i class="fe fe-plus-circle"></i>
								</a>

								<a-popconfirm title="Are you sure？" @confirm="handleDeleteClick(record.item.id) && (authUser && authUser.id !== record.item.id)"
								              v-if="$global.hasPermission('usersdestroy')">
									<i slot="icon" class="fe fe-trash"></i>
									<a class=" ml-1"
									        :title="$t('button.title.deleteItem')"
									        v-b-tooltip.hover>
										<i class="fe fe-trash"></i>
									</a>
								</a-popconfirm>
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
					</div><!-- /.users-table -->
				</div><!-- /div -->
				<div v-show="operation !== null && operation !== 'detail'">
					<div class="users-operation">
						<b-container fluid>
							<form @submit.prevent="handleSubmit" autocomplete="off">
								<b-row>
									<b-col class="p-0" cols="12" md="12" lg="6" sm="12">
										<b-row>
											<b-col sm="6">
												<b-form-group
													:label="$t('input.firstName')+' *'"
													label-for="firstname"
													:invalid-feedback="formErrors.first('first_name')"
												>
													<b-form-input
														id="firstname"
														v-model="formFields.first_name"
														type="text"
														:state="((formErrors.has('first_name') ? false : null))"
														ref="firstname"
														@focus="$event.target.select()"
													></b-form-input>
												</b-form-group>
											</b-col><!--/b-col-->
											<b-col sm="6">
												<b-form-group
													:label="$t('input.lastName')"
													label-for="lastname"
													:invalid-feedback="formErrors.first('last_name')"
												>
													<b-form-input
														id="lastname"
														v-model="formFields.last_name"
														type="text"
														:state="((formErrors.has('last_name') ? false : null))"
														ref="lastname"
														@focus="$event.target.select()"
													></b-form-input>
												</b-form-group>
											</b-col>
											<b-col sm="6">
												<b-form-group
														:label="$t('input.email')+' *'"
														label-for="email"
														:invalid-feedback="formErrors.first('email')">
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
														:label="$t('input.role')+' *'"
														label-for="role"
														:invalid-feedback="formErrors.first('role')">
													<b-form-select
														id="role"
														v-model="formFields.role"
														:options="dropdowns.roles"
														:state="((formErrors.has('role') ? false : null))"
														value-field="id"
														text-field="label"
														v-if="operation !== 'edit'"
													></b-form-select>
													<div v-if="operation === 'edit' && formFields.roles">
														<b-badge variant="info"
														         v-for="(roles, index) in formFields.roles"
														         :key="index">
															{{ roles.name }}
														</b-badge>
													</div>
												</b-form-group>
											</b-col><!--/b-col-->
											<b-col sm="6">
												<b-form-group
														:label="$t('input.phone')+' *'"
														label-for="phone"
														:invalid-feedback="formErrors.first('phone')">
													<b-form-input
															id="phone"
															v-model="formFields.phone"
															type="number"
															pattern="^[0-9-+()]*"
															title="+(XXX) XXX"
															:state="((formErrors.has('phone') ? false : null))"
															@focus="$event.target.select()"></b-form-input>
												</b-form-group>
											</b-col><!--/b-col-->
										</b-row><!--/b-row-->
										<b-row>
											<b-col sm="6">
												<b-form-group
														:label="$t('input.password')+' *'"
														label-for="password"
														:invalid-feedback="formErrors.first('password')">
													<b-form-input
															id="password"
															v-model="formFields.password"
															type="password"
															:state="((formErrors.has('password') ? false : null))"
															@focus="$event.target.select()"></b-form-input>
												</b-form-group>
											</b-col><!--/b-col-->
											<b-col sm="6">
                                                <b-form-group
                                                    :label="$t('input.confirmPassword')+' *'"
                                                    label-for="password_confirmation"
                                                    :invalid-feedback="formErrors.first('password_confirmation')">
                                                    <b-form-input
                                                        type="password"
                                                        id="password_confirmation"
                                                        v-model="formFields.password_confirmation"
                                                        :state="((formErrors.has('password_confirmation') ? false : null))"
                                                        @focus="$event.target.select()"></b-form-input>
                                                </b-form-group>
											</b-col><!--/b-col-->
										</b-row><!--/b-row-->
										<b-row>
											<b-col sm="6">
												<b-form-group
														:label="$t('input.status') + ':'"
														label-for="status"
														:invalid-feedback="formErrors.first('status')"
												>
													<b-form-checkbox v-model="formFields.status" name="status">
														{{$t('input.allowLogin')}}
													</b-form-checkbox>
												</b-form-group>
											</b-col>
											<b-col sm="6" class="mt-2">
												<upload v-model="formFields.profile" :disabled="formFields.profile"
												        :title="$t('button.title.uploadProfile')"
												        css-class="mr-2 btn-sm" upload-type="image" thum="profile"></upload>
												<b-button title="Remove Profile" variant="outline-primary" class="mr-2" size="sm"
												          @click="() => {formFields.profile = null; formFields.profile = null}"
												          :disabled="!formFields.profile"
												          v-if="formFields.profile">
													<i class="fa fa-close"></i>
												</b-button>
												<b-button color="outline-primary" class="mr-2" size="sm"
												          :disabled="!(formFields.profile && formFields.profile.download_url)"
												          :href="(formFields.profile ? formFields.profile.download_url : '')"
												          target="_blank"
												          v-if="formFields.profile">
													<i class="fa fa-download"></i>
												</b-button>
											</b-col><!--/b-col-->
										</b-row><!--/b-row-->

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
											          size="sm" @click="handleOperationClose()"
											          v-b-tooltip.hover :title="$t('button.title.cancel')">
													<i class="fa fa-arrow-left mr-1"></i> {{$t('button.cancel')}}
												</b-button>
											</b-col>
										</b-row>
									</b-col><!--/b-col-->
								</b-row><!--/b-row-->
							</form><!--/form-->
						</b-container><!--/b-container-->
					</div><!--/.users-operation-->
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
								<b-form-group
										:label="$t('input.role')"
										label-for="filterRole">
									<treeselect
											id="id"
											label="label"
											:multiple="true"
											:options="dropdowns.roles"
											v-model="filters.roles"
											placeholder=""
									/>
								</b-form-group>
							</b-col><!--/b-col-->
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
			<div class="suspend-container">
				<a-drawer
						placement="left"
						:width="'360px'"
						:wrapStyle="{overflow: 'auto',paddingBottom: '108px'}"
						:closable="false"
						@close="suspendFields.visible = !suspendFields.visible"
						:visible="!operation && suspendFields.visible"
						:zIndex="10"
						title="Suspend User"
				>
					<form @submit.prevent="handleSubmitSuspend" autocomplete="off">
						<b-row>
							<b-col sm="12">
								<b-form-group
										:label="$t('input.fromAddedDate')"
										label-for="suspendFromDate"
										:invalid-feedback="formErrors.first('from_suspended_at')">
									<b-form-datepicker
										placeholder=""
										:state="((formErrors.has('from_suspended_at') ? false : null))"
										id="suspendFromDate" v-model="suspendFields.from_suspended_at"
										:locale="settings.locale" class="mb-2" reset-button></b-form-datepicker>
								</b-form-group>
								<b-form-group
										:label="$t('input.toAddedDate')"
										label-for="suspendToDate"
										:invalid-feedback="formErrors.first('to_suspended_at')">
									<b-form-datepicker
											placeholder=""
											:state="((formErrors.has('to_suspended_at') ? false : null))"
											id="suspendToDate" v-model="suspendFields.to_suspended_at"
											:locale="settings.locale" class="mb-2" reset-button></b-form-datepicker>
								</b-form-group>
							</b-col><!--/b-col-->
						</b-row>
						<div class="drawer-footer mt-3">
							<b-button size='sm' variant="info" @click="suspendFields.visible = !suspendFields.visible"
							          class="mr-2" :title="$t('button.title.closePanel')" v-b-tooltip.hover>
								{{$t('button.close')}}
							</b-button>
							<b-button size='sm' variant="primary" button="submit" type="filled"
							          :title="$t('button.title.saveChanges')" v-b-tooltip.hover>
								{{$t('button.save')}}
							</b-button>
						</div>
					</form>
				</a-drawer>
			</div><!-- /.suspend-container -->
		</div><!-- /.card -->
	</div>
</template>
<script>
    import ListingMixin from '../../../util/ListingMixin'
    import Error from '../../../util/Error'
    import {mapState} from 'vuex'
    import Datepicker from 'vuejs-datepicker'
    import {request} from '../../../util/Request'
    import {itemAdded, itemUpdated, itemDeleted, itemDeleteFails, itemEditFails} from '../../../util/Notify'
    import {getAuthUser, handleServerError} from '../../../util/Utils'
    import moment from 'moment-timezone'
    import Treeselect from '@riophae/vue-treeselect'

    const FORM_STATE = {
		first_name: null,
		last_name: null,
		email: null,
		phone: null,
		username: null,
		password: null,
		profile: null,
		password_confirmation: null,
		role: undefined,
		status: false,
		_method: 'post',
    };

    const FILTER_STATE = {
        visible: false,
        role: undefined,
        from_date: null,
        to_date: null,
    };

    const SUSPEND_STATE = {
        visible: false,
        id: null,
        from_suspended_at: null,
        to_suspended_at: null,
    };

    const ACTIVATE_STATE = {
        visible: false,
        id: null,
    };

    const COLUMN_DEFINATION = (self) => [
        {
            label: '#',
            key: 'id',
            sortable: true,
            sortKey: 'id',
        },
        {
            label: self.$t('column.profile'),
            key: 'profile',
            sorter: false,
        },
        {
            label: self.$t('column.name'),
            key: 'name',
            sortable: true,
            sortKey: 'name',
        },
        {
            label: self.$t('column.email'),
            key: 'email',
            sortable: true,
            sortKey: 'email',
        },
        {
            label: self.$t('column.phone'),
            key: 'phone',
            sortable: true,
            sortKey: 'phone',
        },
        {
            label: self.$t('column.roles'),
            key: 'roles',
            sortable: false,
        },
        {
            label: self.$t('column.status'),
            key: 'is_suspended',
            sorter: false,
        },
        (self.$global.hasAnyPermission(['usersupdate', 'usersdestroy'])
            ? {
                label: self.$t('column.action'),
                class: 'text-right',
                key: 'action',
                width: 150,
            } : {}),
    ];

    export default {
        mixins: [ListingMixin],
        components: {
            Datepicker,
            Treeselect
        },
        data() {
            return {
                operationTitle: 'title.users',
                formFields: {...FORM_STATE},
                filters: {...FILTER_STATE},
                suspendFields: {...SUSPEND_STATE},
                listUrl: 'users',
                columns: COLUMN_DEFINATION(this),
                dropdowns: {
                    roles: [],
                },
                focusable: 'name',
                show: true,
                authUser: getAuthUser()
            }
        },
        mounted() {
            this.getRoles()

            if (this.$route.query && this.$route.query.operation && this.$route.query.oToken) {
                this.handleEditClick(this.$route.query.oToken)
            }
        },
        methods: {
            setOperation(operation = 'add', operationToken = null) {
                this.operationTitle = (operation === 'add' ? 'title.addUser' : 'title.editUser')
                this.$router.replace({
                    query: Object.assign({},
                        this.$route.query,
                        {
                            ...this.listQueryParams,
                            operation: operation,
                            oToken: operationToken,
                        },
                    ),
                }).then(() => {
                }).catch(() => {
                })
            },
            async handleSubmit() {
                this.formErrors = new Error({})
                try {
                    const response = await request({
                        url: this.formFields.id ? 'users/update' : 'users/create',
                        method: 'post',
                        data: this.formFields,
                    })

                    if (this.formFields.id) {
                        itemUpdated(this.$notification)
                    } else {
                        itemAdded(this.$notification)
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
            async handleDeleteClick(id) {
                try {
                    const response = await request({
                        method: 'post',
                        url: '/users/delete',
                        data: {
                            id: id,
                        },
                    })
                    this.loadList(this.listQueryParams)
                    itemDeleted(this.$notification)
                } catch (errors) {
                    itemDeleteFails(this.$notification)
                }
            },
            async getRoles() {
                try {
                    const response = await request({
                        url: '/dropdowns/roles',
                        method: 'post',
                    })

                    this.dropdowns.roles = response.data.map((item) => {
                        return {
                            id: item.id,
                            label: item.name,
                        }
                    })
                } catch (e) {

                }
            },
            async handleEditClick(id) {
                try {
                    const response = await request({
                        method: 'get',
                        url: `/users/detail/${id}`,
                    })
                    this.operationTitle = this.$t('title.editUser')
                    const {data} = response
                    this.formFields = {
                        ...data,
                        status: (data.is_suspended) ? false : true,
                        profile: data.profile_pic,
                    }
                } catch (e) {
                    itemEditFails(this.$notification)
                    this.formFields = {...FORM_STATE}
                }
            },
            async handleSubmitSuspend() {
                try {
                    const response = await request({
                        url: 'users/suspend',
                        method: "post",
                        data: {...this.suspendFields}
                    })

                    const {data} = response
                    itemUpdated(this.$notification)
                    this.suspendFields = {...SUSPEND_STATE}
                    this.loadList(this.listQueryParams)
                } catch (error) {
                    if (error.request && error.request.status && error.request.status === 422) {
                        this.formErrors = new Error(JSON.parse(error.request.responseText).errors)
                        return false
                    }

                    handleServerError(error, this.$notification)
                }
            },
            async handleSubmitActivate() {
                try {
                    const response = await request({
                        url: 'users/activate',
                        method: "post",
                        data: {...this.activateFields}
                    })

                    const {data} = response
                    itemUpdated(this.$notification)
                    this.activateFields = {...ACTIVATE_STATE}
                    this.loadList(this.listQueryParams)
                } catch (error) {
                    if (error.request && error.request.status && error.request.status === 422) {
                        this.formErrors = new Error(JSON.parse(error.request.responseText).errors)
                        return false
                    }

                    handleServerError(error, this.$notification)
                }
            },
            handleSuspendClick(record) {
                this.suspendFields = {
                    visible: true,
                    id: record.id,
                    from_suspended_at: (moment(record.from_suspended_at).isValid() ? record.from_suspended_at : null),
                    to_suspended_at: (moment(record.to_suspended_at).isValid() ? record.to_suspended_at : null),
                }
            },
            handleActivateClick(record) {
                this.activateFields = {
                    visible: true,
                    id: record.id,
                }

                this.handleSubmitActivate()
            },
            hasListAccess() {
                return this.$global.hasPermission('usersview')
            },
            getExtraParams() {
                return {
                    filters: {
                        ...this.filters,
                        from_date: ((this.filters.from_date) ? this.$global.dateToUtcDate(this.filters.from_date, 'YYYY-MM-DD', 'YYYY-MM-DD') : ''),
                        to_date: ((this.filters.to_date) ? this.$global.dateToUtcDate(this.filters.to_date, 'YYYY-MM-DD', 'YYYY-MM-DD') : ''),
                    },
                }
            },
            handleResetFilterClick() {
                this.filters = {...FILTER_STATE}
                this.isFilterApplied = 'reset'
                this.loadList(this.listQueryParams)
            },
            afterCloseOperation() {
                this.formFields = {...FORM_STATE}
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


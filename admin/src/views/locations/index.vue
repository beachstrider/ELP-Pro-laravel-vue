<template>
	<div v-if="show">
		<div class="card">
			<div class="card-header card-header-flex pb-2">
				<div class="w-100 mt-2">
					<div class="row">
						<div class="col-8">
							<h5 class="mt-3 ml-0 mr-3 mb-2">
								<strong>
									<template v-if="operation === null">{{$t('title.locations')}}</template>
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
											          v-if="$global.hasPermission('locationsstore')" v-b-tooltip.hover>
												<i class="fe fe-plus"></i> {{$t('button.addNew')}}
											</b-button>
											<b-button size="sm" :title="$t('button.title.filterRecords')"
											          variant="outline-info"
											          @click="filters.visible = !filters.visible" v-b-tooltip.hover
											          v-if="$global.hasPermission('locationsview')">
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
                <div class="locations-table">
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
                        <template v-slot:cell(action)="record">
                            <a @click="setOperation('edit', record.item.id)"
                               :title="$t('button.title.editItem')" v-if="$global.hasPermission('locationsupdate')"
                               v-b-tooltip.hover>
                                <i class="fe fe-edit"></i>
                            </a>
                            <a-popconfirm title="Are you sure？" @confirm="handleDeleteClick(record.item.id)"
                                          v-if="$global.hasPermission('locationsdestroy')">
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
                </div><!-- /.locations-table -->
                <div class="locations-operation">
                    <a-drawer
                        placement="right"
                        :width="'650px'"
                        :wrapStyle="{overflow: 'auto',paddingBottom: '108px'}"
                        :closable="false"
                        @close="handleOperationClose"
                        :visible="operation !== null && operation !== 'detail'"
                        :zIndex="10"
                        :title="$t(operationTitle)"
                    >
                        <form @submit.prevent="handleSubmit" autocomplete="off">
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
                                <div class="drawer-footer">
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
                                </div>
                            </b-row><!--/b-row-->
                        </form><!--/form-->
                    </a-drawer>
                </div><!--/.locations-operation-->
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
										:label="$t('input.country')+' *'">
										<treeselect
											:multiple="true"
											:options="dropdowns.countries"
											placeholder=""
											v-model="filters.countries"
										/>
									</b-form-group>
								</b-col><!--/b-col-->
                                <b-col sm="12">
                                    <b-form-group
                                        :label="$t('input.code')+' *'"
                                    >
                                        <b-form-input
                                            id="code"
                                            v-model="filters.code"
                                            type="text"
                                        ></b-form-input>
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
			</div><!-- /.card-body-->
		</div><!-- /.card -->
	</div>
</template>
<script>
    import ListingMixin from '../../util/ListingMixin'
    import Error from '../../util/Error'
    import {mapState} from 'vuex'
    import Datepicker from 'vuejs-datepicker'
    import {request} from '../../util/Request'
    import {
        itemAdded,
        itemUpdated,
        itemDeleted,
        itemDeleteFails,
        itemEditFails,
        itemDeleteFailsBecsDependency
    } from '../../util/Notify'
    import {handleServerError} from '../../util/Utils'
    import Treeselect from '@riophae/vue-treeselect'

    const FORM_STATE = {
        street: null,
        street_no: null,
        zip: null,
        city: null,
        country: null,
        code: null,
        from_opening_hours: null,
        to_opening_hours: null,
        _method: 'post',
    };

    const FILTER_STATE = {
        visible: false,
        from_date: null,
        to_date: null,
        code: null,
        countries: [],
    };

    const COLUMN_DEFINATION = (self) => [
        {
            label: '#',
            key: 'id',
            sortable: true,
            sortKey: 'id',
        },
        {
            label: self.$t('column.code'),
            key: 'code',
            sortable: true,
            sortKey: 'code',
        },
        {
            label: self.$t('column.street'),
            key: 'street',
            sortable: true,
            sortKey: 'street',
        },
        {
            label: self.$t('column.street_no'),
            key: 'street_no',
            sortable: true,
            sortKey: 'street_no',
        },
        {
            label: self.$t('column.zip'),
            key: 'zip',
            sortable: true,
            sortKey: 'zip',
        },
        {
            label: self.$t('column.city'),
            key: 'city',
            sortable: true,
            sortKey: 'city',
        },
        {
            label: self.$t('column.country'),
            key: 'country',
            sortable: true,
            sortKey: 'country',
        },
        {
            label: self.$t('column.fromOpeningHours'),
            key: 'from_opening_hours',
            sortable: true,
            sortKey: 'from_opening_hours',
        },
        {
            label: self.$t('column.toOpeningHours'),
            key: 'to_opening_hours',
            sortable: true,
            sortKey: 'to_opening_hours',
        },
        (self.$global.hasAnyPermission(['locationsupdate', 'locationsdestroy'])
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
                operationTitle: 'title.locations',
                formFields: {...FORM_STATE},
                filters: {...FILTER_STATE},
                listUrl: 'locations',
                columns: COLUMN_DEFINATION(this),
                dropdowns: {
                    countries: [],
                },
                show: true,
            }
        },
        mounted() {
            this.getCountries();

            if (this.$route.query && this.$route.query.operation && this.$route.query.oToken) {
                this.handleEditClick(this.$route.query.oToken)
            }
        },
        methods: {
            setOperation(operation = 'add', operationToken = null) {
                this.operationTitle = (operation === 'add' ? 'title.addLocation' : 'title.editLocation')
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
                        url: this.formFields.id ? 'locations/update' : 'locations/create',
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
                        url: '/locations/delete',
                        data: {
                            id: id,
                        },
                    })
                    this.loadList(this.listQueryParams)
                    itemDeleted(this.$notification)
                } catch (error) {
                    if (error.request && error.request.status && error.request.status === 422) {
                        itemDeleteFailsBecsDependency(this.$notification)
                        return false;
                    }

                    itemDeleteFails(this.$notification)
                }
            },
            async handleEditClick(id) {
                try {
                    const response = await request({
                        method: 'get',
                        url: `/locations/detail/${id}`,
                    })
                    this.operationTitle = this.$t('title.editLocation')
                    const {data} = response
                    this.formFields = {...data}
                } catch (e) {
                    itemEditFails(this.$notification)
                    this.formFields = {...FORM_STATE}
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
            hasListAccess() {
                return this.$global.hasPermission('locationsview')
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
            },
        },
        computed: {
            ...mapState([
                'global',
                'settings'
            ]),
        },
    }
</script>

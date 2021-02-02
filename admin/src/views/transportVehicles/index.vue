<template>
    <div v-if="show">
        <div class="card">
            <div class="card-header card-header-flex pb-2">
                <div class="w-100 mt-2">
                    <div class="row">
                        <div class="col-8">
                            <h5 class="mt-3 ml-0 mr-3 mb-2">
                                <strong>
                                    <template v-if="operation === null">{{$t('title.transportVehicles')}}</template>
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
                                                      v-if="$global.hasPermission('transportvehiclesstore')" v-b-tooltip.hover>
                                                <i class="fe fe-plus"></i> {{$t('button.addNew')}}
                                            </b-button>
                                            <b-button size="sm" :title="$t('button.title.filterRecords')"
                                                      variant="outline-info"
                                                      @click="filters.visible = !filters.visible" v-b-tooltip.hover
                                                      v-if="$global.hasPermission('transportvehiclesview')">
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
                <div class="transportvehicles-table">
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
                        <template v-slot:cell(is_active)="record">
                            <b-badge v-show="record.item.is_active > 0" variant="success">{{ $t('msc.active') }}</b-badge>
                            <b-badge v-show="record.item.is_active <= 0">{{ $t('msc.de_active') }}</b-badge>
                        </template>
                        <template v-slot:cell(action)="record">
                            <a @click="setOperation('edit', record.item.id)"
                               :title="$t('button.title.editItem')" v-if="$global.hasPermission('transportvehiclesupdate')"
                               v-b-tooltip.hover>
                                <i class="fe fe-edit"></i>
                            </a>
                            <a-popconfirm title="Are you sure？" @confirm="handleDeleteClick(record.item.id)"
                                          v-if="$global.hasPermission('transportvehiclesdestroy')">
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
                </div><!-- /.transportvehicles-table -->
                <div class="transportvehicles-operation">
                    <a-drawer
                        placement="right"
                        :width="'360px'"
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
                                        <b-col sm="12">
                                            <b-form-group
                                                    :label="$t('input.supplier')+' *'"
                                                    label-for="supplier"
                                                    :invalid-feedback="formErrors.first('supplier_id')">
                                                <treeselect
                                                    :multiple="false"
                                                    :options="dropdowns.suppliers"
                                                    placeholder=""
                                                    v-model="formFields.supplier_id"
                                                    :class="[{'invalid is-invalid': (formErrors.has('supplier_id'))}]"
                                                />
                                            </b-form-group>
                                        </b-col><!--/b-col-->
                                        <b-col sm="12">
                                            <b-form-group
                                                :label="$t('input.meansOfTransport')+' *'"
                                                label-for="type"
                                                :invalid-feedback="formErrors.first('type')">
                                                <treeselect
                                                    :multiple="false"
                                                    :options="dropdowns.transportVehiclesTypes"
                                                    placeholder=""
                                                    v-model="formFields.type"
                                                    :class="[{'invalid is-invalid': (formErrors.has('type'))}]"
                                                />
                                            </b-form-group>
                                        </b-col><!--/b-col-->
                                        <b-col sm="12">
                                            <b-form-group
                                                :label="$t('input.brand')+' *'"
                                                label-for="brand"
                                                :invalid-feedback="formErrors.first('brand')"
                                            >
                                                <b-form-input
                                                    id="brand"
                                                    v-model="formFields.brand"
                                                    type="text"
                                                    :state="((formErrors.has('brand') ? false : null))"
                                                    @focus="$event.target.select()"
                                                ></b-form-input>
                                            </b-form-group>
                                        </b-col><!--/b-col-->
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
                                        <b-col sm="12">
                                            <b-form-group
                                                :label="$t('input.capacity')+' *'"
                                                label-for="capacity"
                                                :invalid-feedback="formErrors.first('capacity')"
                                            >
                                                <b-form-input
                                                    id="capacity"
                                                    v-model="formFields.capacity"
                                                    type="text"
                                                    :state="((formErrors.has('capacity') ? false : null))"
                                                    ref="capacity"
                                                    @focus="$event.target.select()"
                                                ></b-form-input>
                                            </b-form-group>
                                        </b-col><!--/b-col-->
                                        <b-col sm="12">
                                            <b-form-group
                                                :label="$t('input.plateNumber')+' *'"
                                                label-for="plate_number"
                                                :invalid-feedback="formErrors.first('plate_number')"
                                            >
                                                <b-form-input
                                                    id="plate_number"
                                                    v-model="formFields.plate_number"
                                                    type="text"
                                                    :state="((formErrors.has('plate_number') ? false : null))"
                                                    ref="plate_number"
                                                    @focus="$event.target.select()"
                                                ></b-form-input>
                                            </b-form-group>
                                        </b-col><!--/b-col-->
                                        <b-col sm="12">
                                            <b-form-group
                                                :label="$t('input.euroNorm')+' *'"
                                                label-for="euro_norm"
                                                :invalid-feedback="formErrors.first('euro_norm')"
                                            >
                                                <b-form-input
                                                    id="euro_norm"
                                                    v-model="formFields.euro_norm"
                                                    type="text"
                                                    :state="((formErrors.has('euro_norm') ? false : null))"
                                                    @focus="$event.target.select()"
                                                ></b-form-input>
                                            </b-form-group>
                                        </b-col><!--/b-col-->
                                        <b-col sm="12" class="mb-5">
                                            <b-form-group
                                                :label="$t('input.yearOfProduction')+' *'"
                                                label-for="year_of_production"
                                                :invalid-feedback="formErrors.first('year_of_production')"
                                            >
                                                <b-form-input
                                                    id="year_of_production"
                                                    v-model="formFields.year_of_production"
                                                    type="text"
                                                    :state="((formErrors.has('year_of_production') ? false : null))"
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
                </div><!--/.transportvehicles-operation-->
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
								<b-col sm="12" v-if="$global.hasRole('SuperAdmin')">
									<b-form-group
										:label="$t('input.supplier')+' *'">
										<treeselect
											:multiple="true"
											:options="dropdowns.suppliers"
											placeholder=""
											v-model="filters.suppliers"
										/>
									</b-form-group>
								</b-col><!--/b-col-->
                                <b-col sm="12">
                                    <b-form-group
                                            :label="$t('input.type')+' *'">
                                        <treeselect
                                            :multiple="true"
                                            :options="dropdowns.transportVehiclesTypes"
                                            placeholder=""
                                            v-model="filters.types"
                                        />
                                    </b-form-group>
                                </b-col><!--/b-col-->
                                <b-col sm="12">
                                    <b-form-group
                                            :label="$t('input.startDate')"
                                            label-for="fromDate">
                                        <b-form-datepicker placeholder="" id="fromDate" v-model="filters.start_date"
                                                           class="mb-2"></b-form-datepicker>
                                    </b-form-group>
                                </b-col><!--/b-col-->
                                <b-col sm="12">
                                    <b-form-group
                                            :label="$t('input.endDate')"
                                            label-for="toDate">
                                        <b-form-datepicker placeholder="" id="toDate" v-model="filters.end_date"
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
    import {itemAdded, itemUpdated, itemDeleted, itemDeleteFails, itemEditFails} from '../../util/Notify'
    import {handleServerError} from '../../util/Utils'
    import Treeselect from '@riophae/vue-treeselect'

    const FORM_STATE = {
        supplier_id: null,
        title: null,
        capacity: null,
        type: null,
        plate_number: null,
        year_of_production: null,
        brand: null,
        euro_norm: null,
        _method: 'post',
    };

    const FILTER_STATE = {
        visible: false,
        start_date: null,
        end_date: null,
        types: null
    };

    const COLUMN_DEFINATION = (self) => [
        {
            label: '#',
            key: 'id',
            sortable: true,
            sortKey: 'id',
        },
        {
            label: self.$t('column.title'),
            key: 'title',
            sortable: true,
            sortKey: 'title',
        },
        {
            label: self.$t('column.supplier'),
            key: 'supplier.name',
            sortable: true,
            sortKey: 'supplier_id',
        },
        {
            label: self.$t('column.capacity'),
            key: 'capacity',
            sortable: true,
            sortKey: 'capacity',
        },
        {
            label: self.$t('column.type'),
            key: 'type',
            sortable: true,
            sortKey: 'type',
        },
        {
            label: self.$t('column.plateNumber'),
            key: 'plate_number',
            sortable: true,
            sortKey: 'plate_number',
        },
        {
            label: self.$t('column.brand'),
            key: 'brand',
            sortable: true,
            sortKey: 'brand',
        },
        {
            label: self.$t('column.yearOfProduction'),
            key: 'year_of_production',
            sortable: true,
            sortKey: 'year_of_production',
        },
        {
            label: self.$t('column.euroNorm'),
            key: 'euro_norm',
            sortable: true,
            sortKey: 'euro_norm',
        },
        (self.$global.hasAnyPermission(['transportvehiclesupdate', 'transportvehiclesdestroy'])
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
                operationTitle: 'title.Transportvehicles',
                formFields: {...FORM_STATE},
                filters: {...FILTER_STATE},
                listUrl: 'transport/vehicles',
                columns: COLUMN_DEFINATION(this),
                dropdowns: {
                    suppliers:[],
                    transportVehiclesTypes:[],
                },
                show: true,
            }
        },
        mounted() {
            this.getSuppliersByType();
            this.getTransportVehiclesTypes()

            if (this.$route.query && this.$route.query.operation && this.$route.query.oToken) {
                this.handleEditClick(this.$route.query.oToken)
            }
        },
        methods: {
            setOperation(operation = 'add', operationToken = null) {
                this.operationTitle = (operation === 'add' ? 'title.addTransportVehicles' : 'title.editTransportVehicles')
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
                        url: this.formFields.id ? 'transport/vehicles/update' : 'transport/vehicles/create',
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
                        url: '/transport/vehicles/delete',
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
            async handleEditClick(id) {
                try {
                    const response = await request({
                        method: 'get',
                        url: `/transport/vehicles/detail/${id}`,
                    })
                    this.operationTitle = this.$t('title.editTransportVehicles')
                    const {data} = response

                    const {supplier} = data
                    delete data.supplier
                    this.formFields = {...data, supplier_id: (supplier ? supplier.id : null)}

                } catch (e) {
                    itemEditFails(this.$notification)
                    this.formFields = {...FORM_STATE}
                }
            },
            async getSuppliersByType() {
                try {
                    const response = await request({
                        url: `/dropdowns/suppliers/all`,
                        method: "post"
                    })

                    const {data} = response
                    this.dropdowns.suppliers = data

                } catch (e) {
                    this.dropdowns.suppliers = []
                }
            },
            async getTransportVehiclesTypes() {
                try {
                    const response = await request({
                        url: '/dropdowns/transport/vehicles/types',
                        method: "post"
                    })

                    const {data} = response
                    this.dropdowns.transportVehiclesTypes = data

                } catch (e) {
                    this.dropdowns.transportVehiclesTypes = []
                }
            },
            hasListAccess() {
                return this.$global.hasPermission('transportvehiclesview')
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

<template>
    <div v-if="show">
        <div class="card">
            <div class="card-header card-header-flex pb-2">
                <div class="w-100 mt-2">
                    <div class="row">
                        <div class="col-8">
                            <h5 class="mt-3 ml-0 mr-3 mb-2">
                                <strong>
                                    <template v-if="operation === null">{{$t('title.drivers')}}</template>
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
                                                      v-if="$global.hasPermission('driversstore')" v-b-tooltip.hover>
                                                <i class="fe fe-plus"></i> {{$t('button.addNew')}}
                                            </b-button>
                                            <b-button size="sm" :title="$t('button.title.filterRecords')"
                                                      variant="outline-info"
                                                      @click="filters.visible = !filters.visible" v-b-tooltip.hover
                                                      v-if="$global.hasPermission('driversview')">
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
                <div class="drivers-table">
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
                               :title="$t('button.title.editItem')" v-if="$global.hasPermission('driversupdate')"
                               v-b-tooltip.hover>
                                <i class="fe fe-edit"></i>
                            </a>
                            <a-popconfirm title="Are you sure？" @confirm="handleDeleteClick(record.item.id)"
                                          v-if="$global.hasRole('SuperAdmin') || record.item.supplier.id == user.id">
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
                </div><!-- /.drivers-table -->
                <div class="drivers-operation">
                    <a-drawer
                        placement="right"
                        :width="'950px'"
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
                                    </b-row><!--/b-row-->
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
                                                :label="$t('input.email')+' *'"
                                                label-for="email"
                                                :invalid-feedback="formErrors.first('email')"
                                            >
                                                <b-form-input
                                                    id="email"
                                                    v-model="formFields.email"
                                                    type="email"
                                                    :state="((formErrors.has('email') ? false : null))"
                                                    ref="email"
                                                    @focus="$event.target.select()"
                                                ></b-form-input>
                                            </b-form-group>
                                        </b-col><!--/b-col-->
                                        <b-col sm="6">
                                            <b-form-group
                                                :label="$t('input.password')+  (!formFields.id ? ' *' : '')"
                                                label-for="password"
                                                :invalid-feedback="formErrors.first('password')"
                                            >
                                                <b-form-input
                                                    id="password"
                                                    v-model="formFields.password"
                                                    type="password"
                                                    :state="((formErrors.has('password') ? false : null))"
                                                    ref="password"
                                                    @focus="$event.target.select()"
                                                ></b-form-input>
                                            </b-form-group>
                                        </b-col><!--/b-col-->
                                    </b-row><!--/b-row-->
                                    <b-row>
                                        <b-col sm="12">
                                            <b-form-group :label="$t('input.is_active')">
                                                <div>
                                                    <b-form-checkbox v-model="formFields.is_active" name="check-button" switch size="lg">
                                                        {{formFields.is_active ? $t('msc.yes') : $t('msc.no') }}
                                                    </b-form-checkbox>
                                                </div>
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

                            <b-row>
                                <!--# Start driver documents #-->
                                <b-col lg="12" md="12" sm="12" class="mt-3 mb-5">
                                    <b-card class="mb-0">
                                        <b-card-header v-b-toggle.driver-documents class="p-0">
                                            <h4 class="mb-0">
														<span class="badge badge-primary">
															{{$t('title.driverAttachments')}}
														</span>
                                                <small v-show="driver_documents.length > 0">
                                                    - {{driver_documents.length}} Item/s
                                                </small>
                                            </h4>
                                        </b-card-header><!-- /.p-0-->

                                        <b-collapse id="driver-documents">
                                            <div class="bg-light p-3">
                                                <b-row>
                                                    <b-col lg="4" md="4" sm="12">
                                                        <div class="form-group">
                                                            <b-form-group
                                                                :label="$t('input.title')+' *'"
                                                                label-for="title"
                                                                :invalid-feedback="formErrors.first('title')"
                                                            >
                                                                <b-form-input
                                                                    id="title"
                                                                    v-model="driver_document.title"
                                                                    type="text"
                                                                    :state="((formErrors.has('title') ? false : null))"
                                                                    @focus="$event.target.select()"
                                                                ></b-form-input>
                                                            </b-form-group>
                                                            <div class="invalid-feedback">{{formErrors.first('title')}}</div>
                                                        </div><!-- /.form-group -->
                                                    </b-col><!--/b-col-->
                                                    <b-col lg="4" md="4" sm="12">
                                                        <b-form-group class="mt-4 pt-2">
                                                            <upload v-model="driver_document.document"
                                                                    :disabled="driver_document.document"
                                                                    :title="(driver_document.document ? $t('msc.uploadedFile') : $t('msc.uploadFile'))"
                                                                    css-class="mt-0 btn-sm"></upload>
                                                            <b-button :title="$t('msc.removeUpload')"
                                                                      variant="outline-primary"
                                                                      v-b-tooltip.hover
                                                                      class="ml-2 ml-2 btn-sm"
                                                                      @click="() => {driver_document.document = null; driver_document.document = null}"
                                                                      :disabled="!driver_document.document"
                                                                      v-if="driver_document.document">
                                                                <i class="fa fa-close"></i>
                                                            </b-button>
                                                            <b-button :title="$t('button.download')"
                                                                      v-b-tooltip.hover
                                                                      variant="outline-primary"
                                                                      class="ml-2 ml-2 btn-sm"
                                                                      v-if="driver_document.document && driver_document.document.download_url"
                                                                      :disabled="!(driver_document.document && driver_document.document.download_url)"
                                                                      :href="(driver_document.document ? driver_document.document.download_url : '')"
                                                                      target="_blank">
                                                                <i class="fa fa-cloud-download"></i>
                                                            </b-button>
                                                        </b-form-group>
                                                        <div class="invalid-feedback d-block">{{formErrors.first('document')}}</div>
                                                    </b-col><!--/b-col-->
                                                    <b-col lg="2" md="2" sm="12">
                                                        <div class="form-group">
                                                            <label class="d-block"><pre> </pre></label>
                                                            <b-button variant="info" type="button" size="sm" class="btn-block" @click="handleAddUpdateDriverDocumentClick()">
                                                                <i class="fa fa-plus"></i>
                                                            </b-button><!--/b-button-->
                                                        </div><!-- /.form-group -->
                                                    </b-col><!--/b-col-->
                                                    <b-col lg="2" md="2" sm="12">
                                                        <div class="form-group">
                                                            <label class="d-block"><pre> </pre></label>
                                                            <b-button variant="warning" type="button" size="sm" class="btn-block" @click="resetDriverDocument()">
                                                                {{$t('button.reset')}}
                                                            </b-button><!--/b-button-->
                                                        </div><!-- /.form-group -->
                                                    </b-col><!--/b-col-->
                                                </b-row><!--/b-row-->
                                                <b-row>
                                                    <b-col cols="12">
                                                        <table class="table table-bordered bg-white">
                                                            <thead>
                                                            <tr>
                                                                <th width="50">#</th>
                                                                <th width="180">{{$t('column.title')}}</th>
                                                                <th width="60">{{$t('column.action')}}</th>
                                                            </tr>
                                                            </thead>
                                                            <tbody>
                                                            <tr v-for="(ct, index) in driver_documents" :class="[{'table-primary': ct.token === driver_document.token}]">
                                                                <td>{{index + 1}}</td>
                                                                <td>
                                                                    {{ct.title}}
                                                                </td>
                                                                <td>
                                                                    <a @click="handleEditDriverDocumentClick(ct.token)"
                                                                       :title="$t('button.title.editItem')"
                                                                       v-b-tooltip.hover>
                                                                        <i class="fe fe-edit"></i>
                                                                    </a>
                                                                    <a :title="$t('button.download')" class=" ml-1"
                                                                       :href="ct.document.download_url"
                                                                       target="_blank" v-b-tooltip.hover>
                                                                        <i class="fa fa-cloud-download"></i>
                                                                    </a>
                                                                    <a-popconfirm title="Are you sure？" @confirm="handleDeleteDriverDocumentClick(ct.token)">
                                                                        <i slot="icon" class="fe fe-trash"></i>
                                                                        <a class=" ml-1"
                                                                           :title="$t('button.title.deleteItem')"
                                                                           v-b-tooltip.hover>
                                                                            <i class="fe fe-trash"></i>
                                                                        </a>
                                                                    </a-popconfirm>
                                                                </td>
                                                            </tr>
                                                            </tbody>
                                                            <tfoot v-show="driver_documents.length <= 0">
                                                            <tr>
                                                                <th colspan="5">{{$t('title.noDataAvailable')}}</th>
                                                            </tr>
                                                            </tfoot>
                                                        </table><!-- /.table table-bordered -->
                                                    </b-col><!--/b-col-->
                                                </b-row><!--/b-row-->
                                            </div><!-- /.bg-light -->
                                        </b-collapse><!-- /#driver-documents-->
                                    </b-card><!-- /b-card -->
                                </b-col><!--/b-col-->
                            </b-row>
                        </form><!--/form-->
                    </a-drawer>
                </div><!--/.drivers-operation-->
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
    import {handleServerError, getAuthUser} from '../../util/Utils'
    import Treeselect from '@riophae/vue-treeselect'
    import DriverDocumentMixing from "./DriverDocumentMixing";

    const FORM_STATE = {
        supplier_id: null,
        name: null,
        email: null,
        phone: null,
        password: null,
        is_active: true,
        driver_documents: [],
        _method: 'post',
    };

    const FILTER_STATE = {
        visible: false,
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
            key: 'user.name',
            sortable: false,
        },
        {
            label: self.$t('column.phone'),
            key: 'user.phone',
            sortable: false,
            sortKey: 'title',
        },
        {
            label: self.$t('column.email'),
            key: 'user.email',
            sortable: false,
        },
        {
            label: self.$t('column.supplier'),
            key: 'supplier.name',
            sortable: true,
            sortKey: 'supplier_id',
        },
        {
            label: self.$t('column.status'),
            key: 'is_active',
            sortable: true,
            sortKey: 'is_active',
        },
        (self.$global.hasAnyPermission(['driversupdate', 'driversdestroy'])
        ? {
            label: self.$t('column.action'),
            class: 'text-right',
            key: 'action',
            width: 150,
        } : {}),
    ];

    export default {
        mixins: [ListingMixin, DriverDocumentMixing],
        components: {
            Datepicker,
            Treeselect
        },
        data() {
            return {
                operationTitle: 'title.drivers',
                formFields: {...FORM_STATE},
                filters: {...FILTER_STATE},
                listUrl: 'drivers',
                columns: COLUMN_DEFINATION(this),
                dropdowns: {
                    suppliers:[],
                },
                show: true,
                user: getAuthUser()
            }
        },
        mounted() {
            this.getSuppliersByType()

            if (this.$route.query && this.$route.query.operation && this.$route.query.oToken) {
                this.handleEditClick(this.$route.query.oToken)
            }
        },
        methods: {
            setOperation(operation = 'add', operationToken = null) {
                this.operationTitle = (operation === 'add' ? 'title.addDriver' : 'title.editDriver');
                this.resetDriverDocument();
                this.driver_documents.length = 0;
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
                        url: this.formFields.id ? 'drivers/update' : 'drivers/create',
                        method: 'post',
                        data: {...this.formFields, driver_documents: this.driver_documents},
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
                        url: '/drivers/delete',
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
                        url: `/drivers/detail/${id}`,
                    })
                    this.operationTitle = this.$t('title.editDriver')
                    const {data} = response

                    const {supplier, driver_documents} = data;
                    delete data.supplier; delete data.driver_documents;

                    const {user} = data;
                    delete data.user;

                    this.formFields = {
                        ...data,
                        is_active: (data.is_active > 0),
                        supplier_id: (supplier ? supplier.id : null),
                        name: (user ? user.name : null),
                        email: (user ? user.email : null),
                        phone: (user ? user.phone : null),
                        password: null
                    }

                    this.driver_documents = driver_documents.map((item) => {
                        return {
                            id: item.id,
                            token: item.token,
                            document: item.document,
                            title: item.title
                        }
                    })
                } catch (e) {
                    itemEditFails(this.$notification)
                    this.formFields = {...FORM_STATE}
                }
            },
            async getSuppliersByType() {
                try {
                    const slug = 'transport';
                    const response = await request({
                        url: `/dropdowns/suppliers/${slug}`,
                        method: "post"
                    })

                    const {data} = response
                    this.dropdowns.suppliers = data

                } catch (e) {
                    this.dropdowns.suppliers = []
                }
            },
            hasListAccess() {
                return this.$global.hasPermission('driversview')
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
<style lang="scss">
    .min-24px {
        min-height: 20px;
    }

    @media screen and (max-width: 768px) {
        .ant-drawer-content-wrapper {
            width: 98% !important;
        }

        .min-24px {
            min-height:24px !important;
        }
    }
</style>

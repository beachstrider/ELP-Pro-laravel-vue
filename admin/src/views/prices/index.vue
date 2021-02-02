<template>
    <div v-if="show">
        <div class="card">
            <div class="card-header card-header-flex pb-2">
                <div class="w-100 mt-2">
                    <div class="row">
                        <div class="col-8">
                            <h5 class="mt-3 ml-0 mr-3 mb-2">
                                <strong>
                                    <template v-if="operation === null">{{$t('title.prices')}}</template>
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
                                                      v-if="$global.hasPermission('pricesstore')" v-b-tooltip.hover>
                                                <i class="fe fe-plus"></i> {{$t('button.addNew')}}
                                            </b-button>
                                            <b-button size="sm" :title="$t('button.title.filterRecords')"
                                                      variant="outline-info"
                                                      @click="filters.visible = !filters.visible" v-b-tooltip.hover
                                                      v-if="$global.hasPermission('pricesview')">
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
                <div class="prices-table">
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
                        <template v-slot:cell(supplier)="record">
                            {{ (record.item.supplier ? record.item.supplier.name : '') }}
                        </template>
                        <template v-slot:cell(route)="record">
                            {{ (record.item.route ? record.item.route.name : '') }}
                        </template>
                        <template v-slot:cell(action)="record">
                            <a @click="setOperation('edit', record.item.id)"
                               :title="$t('button.title.editItem')" v-if="$global.hasPermission('pricesupdate')"
                               v-b-tooltip.hover>
                                <i class="fe fe-edit"></i>
                            </a>
                            <a-popconfirm title="Are you sure？" @confirm="handleDeleteClick(record.item.id)"
                                          v-if="$global.hasPermission('pricesdestroy')">
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
                </div><!-- /.prices-table-->
                <div class="prices-operation">
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
                                        <b-col sm="4">
                                            <b-form-group
                                                :label="$t('input.supplier')+' *'"
                                                label-for="supplier_id"
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
                                        <b-col sm="4">
                                            <b-form-group
                                                :label="$t('input.route')+' *'"
                                                label-for="route_id"
                                                :invalid-feedback="formErrors.first('route_id')">
                                                <treeselect
                                                    :multiple="false"
                                                    :options="dropdowns.routes"
                                                    placeholder=""
                                                    v-model="formFields.route_id"
                                                    :class="[{'invalid is-invalid': (formErrors.has('route_id'))}]"
                                                />
                                            </b-form-group>
                                        </b-col><!--/b-col-->
                                        <b-col sm="4">
                                            <b-form-group
                                                :label="$t('input.brand')+' *'"
                                                label-for="brand_id"
                                                :invalid-feedback="formErrors.first('brand_id')">
                                                <treeselect
                                                    :multiple="false"
                                                    :options="dropdowns.brands"
                                                    placeholder=""
                                                    v-model="formFields.brand_id"
                                                    :class="[{'invalid is-invalid': (formErrors.has('brand_id'))}]"
                                                />
                                            </b-form-group>
                                        </b-col><!--/b-col-->
                                        <b-col sm="4">
                                            <b-form-group
                                                :label="$t('input.model')+' *'"
                                                label-for="model_id"
                                                :invalid-feedback="formErrors.first('model_id')">
                                                <treeselect
                                                    :multiple="false"
                                                    :options="dropdowns.models"
                                                    placeholder=""
                                                    v-model="formFields.model_id"
                                                    :class="[{'invalid is-invalid': (formErrors.has('model_id'))}]"
                                                />
                                            </b-form-group>
                                        </b-col><!--/b-col-->
                                        <b-col sm="4">
                                            <b-form-group
                                                :label="$t('input.logistic_type')+' *'"
                                                label-for="logistic_type_id"
                                                :invalid-feedback="formErrors.first('logistic_type_id')">
                                                <treeselect
                                                    :multiple="false"
                                                    :options="dropdowns.logistic_types"
                                                    placeholder=""
                                                    v-model="formFields.logistic_type_id"
                                                    :class="[{'invalid is-invalid': (formErrors.has('logistic_type_id'))}]"
                                                />
                                            </b-form-group>
                                        </b-col><!--/b-col-->
                                        <b-col sm="4">
                                            <b-form-group
                                                :label="$t('input.leading_factors')+' *'"
                                                label-for="leading_factors"
                                                :invalid-feedback="formErrors.first('leading_factors')"
                                            >
                                                <b-form-input
                                                    id="leading_factors"
                                                    v-model="formFields.leading_factors"
                                                    type="text"
                                                    :state="((formErrors.has('leading_factors') ? false : null))"
                                                    ref="street"
                                                    @focus="$event.target.select()"
                                                ></b-form-input>
                                            </b-form-group>
                                        </b-col><!--/b-col-->
                                    </b-row><!--/b-row-->
                                    <b-row>
                                        <b-col sm="3">
                                            <b-form-group
                                                :label="$t('input.lead_time_pickup')+' *'"
                                                label-for="lead_time_pickup"
                                                :invalid-feedback="formErrors.first('lead_time_pickup')"
                                            >
                                                <b-form-input
                                                    id="lead_time_pickup"
                                                    v-model="formFields.lead_time_pickup"
                                                    type="number"
                                                    :state="((formErrors.has('lead_time_pickup') ? false : null))"
                                                    ref="street"
                                                    @focus="$event.target.select()"
                                                ></b-form-input>
                                            </b-form-group>
                                        </b-col><!--/b-col-->
                                        <b-col sm="3">
                                            <b-form-group
                                                :label="$t('input.lead_time_transport')+' *'"
                                                label-for="lead_time_transport"
                                                :invalid-feedback="formErrors.first('lead_time_transport')"
                                            >
                                                <b-form-input
                                                    id="lead_time_transport"
                                                    v-model="formFields.lead_time_transport"
                                                    type="number"
                                                    :state="((formErrors.has('lead_time_transport') ? false : null))"
                                                    ref="street"
                                                    @focus="$event.target.select()"
                                                ></b-form-input>
                                            </b-form-group>
                                        </b-col><!--/b-col-->
                                        <b-col sm="3">
                                            <b-form-group
                                                :label="$t('input.single_loaded_price')+' *'"
                                                label-for="single_loaded_price"
                                                :invalid-feedback="formErrors.first('single_loaded_price')"
                                            >
                                                <b-form-input
                                                    id="single_loaded_price"
                                                    v-model="formFields.single_loaded_price"
                                                    type="number"
                                                    :state="((formErrors.has('single_loaded_price') ? false : null))"
                                                    ref="street"
                                                    @focus="$event.target.select()"
                                                ></b-form-input>
                                            </b-form-group>
                                        </b-col><!--/b-col-->
                                        <b-col sm="3">
                                            <b-form-group
                                                :label="$t('input.full_loaded_price')+' *'"
                                                label-for="full_loaded_price"
                                                :invalid-feedback="formErrors.first('full_loaded_price')"
                                            >
                                                <b-form-input
                                                    id="full_loaded_price"
                                                    v-model="formFields.full_loaded_price"
                                                    type="number"
                                                    :state="((formErrors.has('full_loaded_price') ? false : null))"
                                                    ref="street"
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
                            <b-row>
                                <!--# Start price documents #-->
                                <b-col lg="12" md="12" sm="12" class="mt-3 mb-5">
                                    <b-card class="mb-0">
                                        <b-card-header v-b-toggle.price-documents class="p-0">
                                            <h4 class="mb-0">
														<span class="badge badge-primary">
															{{$t('title.priceAttachments')}}
														</span>
                                                <small v-show="price_documents.length > 0">
                                                    - {{price_documents.length}} Item/s
                                                </small>
                                            </h4>
                                        </b-card-header><!-- /.p-0-->

                                        <b-collapse id="price-documents">
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
                                                                    v-model="price_document.title"
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
                                                            <upload v-model="price_document.document"
                                                                    :disabled="price_document.document"
                                                                    :title="(price_document.document ? $t('msc.uploadedFile') : $t('msc.uploadFile'))"
                                                                    css-class="mt-0 btn-sm"></upload>
                                                            <b-button :title="$t('msc.removeUpload')"
                                                                      variant="outline-primary"
                                                                      v-b-tooltip.hover
                                                                      class="ml-2 ml-2 btn-sm"
                                                                      @click="() => {price_document.document = null; price_document.document = null}"
                                                                      :disabled="!price_document.document"
                                                                      v-if="price_document.document">
                                                                <i class="fa fa-close"></i>
                                                            </b-button>
                                                            <b-button :title="$t('button.download')"
                                                                      v-b-tooltip.hover
                                                                      variant="outline-primary"
                                                                      class="ml-2 ml-2 btn-sm"
                                                                      v-if="price_document.document && price_document.document.download_url"
                                                                      :disabled="!(price_document.document && price_document.document.download_url)"
                                                                      :href="(price_document.document ? price_document.document.download_url : '')"
                                                                      target="_blank">
                                                                <i class="fa fa-cloud-download"></i>
                                                            </b-button>
                                                        </b-form-group>
                                                        <div class="invalid-feedback d-block">{{formErrors.first('document')}}</div>
                                                    </b-col><!--/b-col-->
                                                    <b-col lg="2" md="2" sm="12">
                                                        <div class="form-group">
                                                            <label class="d-block"><pre> </pre></label>
                                                            <b-button variant="info" type="button" size="sm" class="btn-block" @click="handleAddUpdatePriceDocumentClick()">
                                                                <i class="fa fa-plus"></i>
                                                            </b-button><!--/b-button-->
                                                        </div><!-- /.form-group -->
                                                    </b-col><!--/b-col-->
                                                    <b-col lg="2" md="2" sm="12">
                                                        <div class="form-group">
                                                            <label class="d-block"><pre> </pre></label>
                                                            <b-button variant="warning" type="button" size="sm" class="btn-block" @click="resetPriceDocument()">
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
                                                            <tr v-for="(ct, index) in price_documents" :class="[{'table-primary': ct.token === price_document.token}]">
                                                                <td>{{index + 1}}</td>
                                                                <td>
                                                                    {{ct.title}}
                                                                </td>
                                                                <td>
                                                                    <a @click="handleEditPriceDocumentClick(ct.token)"
                                                                       :title="$t('button.title.editItem')"
                                                                       v-b-tooltip.hover>
                                                                        <i class="fe fe-edit"></i>
                                                                    </a>
                                                                    <a :title="$t('button.download')" class=" ml-1"
                                                                       :href="ct.document.download_url"
                                                                       target="_blank" v-b-tooltip.hover>
                                                                        <i class="fa fa-cloud-download"></i>
                                                                    </a>
                                                                    <a-popconfirm title="Are you sure？" @confirm="handleDeletePriceDocumentClick(ct.token)">
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
                                                            <tfoot v-show="price_documents.length <= 0">
                                                            <tr>
                                                                <th colspan="5">{{$t('title.noDataAvailable')}}</th>
                                                            </tr>
                                                            </tfoot>
                                                        </table><!-- /.table table-bordered -->
                                                    </b-col><!--/b-col-->
                                                </b-row><!--/b-row-->
                                            </div><!-- /.bg-light -->
                                        </b-collapse><!-- /#price-documents-->
                                    </b-card><!-- /b-card -->
                                </b-col><!--/b-col-->
                            </b-row>
                        </form><!--/form-->
                    </a-drawer>
                </div><!--/.prices-operation-->
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
                                        :label="$t('input.route')+' *'">
                                        <treeselect
                                            :multiple="true"
                                            :options="dropdowns.routes"
                                            placeholder=""
                                            v-model="filters.routes"
                                        />
                                    </b-form-group>
                                </b-col><!--/b-col-->
                                <b-col sm="12">
                                    <b-form-group
                                        :label="$t('input.logistic_type')+' *'">
                                        <treeselect
                                            :multiple="true"
                                            :options="dropdowns.logistic_types"
                                            placeholder=""
                                            v-model="filters.logistic_types"
                                        />
                                    </b-form-group>
                                </b-col><!--/b-col-->
                                <b-col sm="12">
                                    <b-form-group :label="$t('input.fromAddedDate')">
                                        <b-form-datepicker placeholder="" v-model="filters.from_date"
                                                           class="mb-2"></b-form-datepicker>
                                    </b-form-group>
                                </b-col><!--/b-col-->
                                <b-col sm="12">
                                    <b-form-group :label="$t('input.toAddedDate')">
                                        <b-form-datepicker placeholder="" v-model="filters.to_date"
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
    import PriceDocumentMixin from "./PriceDocumentMixin"

    const FORM_STATE = {
        supplier_id: null,
        route_id: null,
        brand_id: null,
        model_id: null,
        logistic_type_id: null,
        leading_factors: null,
        lead_time_pickup: null,
        lead_time_transport: null,
        full_loaded_price: null,
        single_loaded_price: null,
        price_documents: [],
        _method: 'post',
    };

    const FILTER_STATE = {
        visible: false,
        from_date: null,
        to_date: null,
        suppliers: [],
        routes: [],
        logistic_types: [],
    };

    const COLUMN_DEFINATION = (self) => [
        {
            label: '#',
            key: 'id',
            sortable: true,
            sortKey: 'id',
        },
        {
            label: self.$t('column.supplier'),
            key: 'supplier',
            sortable: true,
            sortKey: 'supplier',
        },
        {
            label: self.$t('column.route'),
            key: 'route',
            sortable: true,
            sortKey: 'route',
        },
        {
            label: self.$t('column.lead_time_pickup'),
            key: 'lead_time_pickup',
            sortable: true,
            sortKey: 'lead_time_pickup',
        },
        {
            label: self.$t('column.lead_time_transport'),
            key: 'lead_time_transport',
            sortable: true,
            sortKey: 'lead_time_transport',
        },
        {
            label: self.$t('column.single_loaded_price'),
            key: 'single_loaded_price',
            sortable: true,
            sortKey: 'single_loaded_price',
        },
        {
            label: self.$t('column.full_loaded_price'),
            key: 'full_loaded_price',
            sortable: true,
            sortKey: 'full_loaded_price',
        },
        (self.$global.hasAnyPermission(['pricesupdate', 'pricesdestroy'])
            ? {
                label: self.$t('column.action'),
                class: 'text-right',
                key: 'action',
                width: 150,
            } : {}),
    ];

    export default {
        mixins: [ListingMixin, PriceDocumentMixin],
        components: {
            Treeselect
        },
        data() {
            return {
                operationTitle: 'title.prices',
                formFields: {...FORM_STATE},
                filters: {...FILTER_STATE},
                listUrl: 'prices',
                columns: COLUMN_DEFINATION(this),
                dropdowns: {
                    suppliers: [],
                    routes: [],
                    brands: [],
                    models: [],
                    logistic_types: [],
                },
                show: true,
            }
        },
        mounted() {
            this.getLogisticTypes();
            this.getSuppliersByType();
            this.getRoutes();
            this.getBrands();
            this.getModels();

            if (this.$route.query && this.$route.query.operation && this.$route.query.oToken) {
                this.handleEditClick(this.$route.query.oToken)
            }
        },
        methods: {
            setOperation(operation = 'add', operationToken = null) {
                this.operationTitle = (operation === 'add' ? 'title.addPrice' : 'title.editPrice')
                this.resetPriceDocument()
                this.price_documents.length = 0
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
                        url: this.formFields.id ? 'prices/update' : 'prices/create',
                        method: 'post',
                        data: {...this.formFields, price_documents: this.price_documents},
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
                        url: '/prices/delete',
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
                        url: `/prices/detail/${id}`,
                    })
                    this.operationTitle = this.$t('title.editPrice')
                    const {data} = response
                    const {supplier, route, brand, model, logistic_type, price_documents} = data
                    delete data.supplier
                    delete data.route
                    delete data.brand
                    delete data.model
                    delete data.logistic_type
                    delete data.price_documents
                    this.formFields = {...data,
                        supplier_id: (supplier ? supplier.id : null),
                        route_id: (route ? route.id : null),
                        brand_id: (brand ? brand.id : null),
                        model_id: (model ? model.id : null),
                        logistic_type_id: (logistic_type ? logistic_type.id : null),
                    }
                    this.price_documents = price_documents.map((item) => {
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
            async getRoutes() {
                try {
                    const response = await request({
                        url: '/dropdowns/routes',
                        method: "post"
                    })

                    const {data} = response
                    this.dropdowns.routes = data
                } catch (e) {
                    this.dropdowns.routes = []
                }
            },
            async getBrands() {
                try {
                    const response = await request({
                        url: '/dropdowns/brands',
                        method: "post"
                    })

                    const {data} = response
                    this.dropdowns.brands = data

                } catch (e) {
                    this.dropdowns.brands = []
                }
            },
            async getModels() {
                try {
                    const response = await request({
                        url: '/dropdowns/models',
                        method: "post"
                    })

                    const {data} = response
                    this.dropdowns.models = data

                } catch (e) {
                    this.dropdowns.models = []
                }
            },
            async getLogisticTypes() {
                try {
                    const response = await request({
                        url: '/dropdowns/logistic/types',
                        method: "post"
                    })

                    const {data} = response
                    this.dropdowns.logistic_types = data

                } catch (e) {
                    this.dropdowns.logistic_types = []
                }
            },
            hasListAccess() {
                return this.$global.hasPermission('pricesview')
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

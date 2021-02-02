<template>
	<div v-if="show">
		<div class="card">
			<div class="card-header card-header-flex pb-2">
				<div class="w-100 mt-2">
					<div class="row">
						<div class="col-8">
							<h5 class="mt-3 ml-0 mr-3 mb-2">
								<strong>
									<template v-if="operation === null">{{$t('title.models')}}</template>
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
											          v-if="$global.hasPermission('modelsstore')" v-b-tooltip.hover>
												<i class="fe fe-plus"></i> {{$t('button.addNew')}}
											</b-button>
											<b-button size="sm" :title="$t('button.title.filterRecords')"
											          variant="outline-info"
											          @click="filters.visible = !filters.visible" v-b-tooltip.hover
											          v-if="$global.hasPermission('modelsview')">
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
                <div class="models-table">
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
                        <template v-slot:cell(brand)="record">
                            {{ (record.item.brand) ? record.item.brand.title : '' }}
                        </template>
                        <template v-slot:cell(is_active)="record">
                            <b-badge v-show="record.item.is_active > 0" variant="success">{{ $t('msc.active') }}</b-badge>
                            <b-badge v-show="record.item.is_active <= 0">{{ $t('msc.de_active') }}</b-badge>
                        </template>
                        <template v-slot:cell(action)="record">
                            <a @click="setOperation('edit', record.item.id)"
                               :title="$t('button.title.editItem')" v-if="$global.hasPermission('modelsupdate')"
                               v-b-tooltip.hover>
                                <i class="fe fe-edit"></i>
                            </a>
                            <a-popconfirm title="Are you sure？" @confirm="handleDeleteClick(record.item.id)"
                                          v-if="$global.hasPermission('modelsdestroy')">
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
                </div><!-- /.models-table -->
                <div class="models-operation">
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
				                                    :label="$t('input.brand')+' *'"
				                                    label-for="brand"
				                                    :invalid-feedback="formErrors.first('brand_id')">
			                                    <treeselect
				                                    :multiple="false"
				                                    :options="dropdowns.brands"
				                                    placeholder=""
				                                    v-model="formFields.brand_id"
				                                    :class="[{'invalid': (formErrors.has('brand_id'))}]"
			                                    />
		                                    </b-form-group>
	                                    </b-col><!--/b-col-->
                                        <b-col sm="12">
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
                                        <b-col sm="12">
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
                                        <b-col sm="12">
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
                                        <b-col sm="12">
                                            <b-form-group
                                                :label="$t('input.height')+' *'"
                                                label-for="height"
                                                :invalid-feedback="formErrors.first('height')"
                                            >
                                                <b-form-input
                                                    id="height"
                                                    step=".01"
                                                    type="number"
                                                    v-model="formFields.height"
                                                    :state="((formErrors.has('height') ? false : null))"
                                                    @focus="$event.target.select()"
                                                ></b-form-input>
                                            </b-form-group>
                                        </b-col><!--/b-col-->
	                                    <b-col sm="12">
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
	                                    <b-col sm="12">
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
                        </form><!--/form-->
                    </a-drawer>
                </div><!--/.models-operation-->
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
										:label="$t('input.brand')+' *'">
										<treeselect
											:multiple="true"
											:options="dropdowns.brands"
											placeholder=""
											v-model="filters.brands"
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

    const FILTER_STATE = {
        visible: false,
        brands: [],
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
            label: self.$t('column.model'),
            key: 'title',
            sortable: true,
            sortKey: 'title',
        },
        {
            label: self.$t('column.brand'),
            key: 'brand',
            sortable: true,
            sortKey: 'brand_id',
        },
        {
            label: self.$t('column.type'),
            key: 'type',
            sortable: true,
            sortKey: 'type',
        },
        {
            label: self.$t('column.width'),
            key: 'width',
            sortable: true,
            sortKey: 'width',
        },
        {
            label: self.$t('column.height'),
            key: 'height',
            sortable: true,
            sortKey: 'height',
        },
        {
            label: self.$t('column.length'),
            key: 'length',
            sortable: true,
            sortKey: 'length',
        },
        {
            label: self.$t('column.status'),
            key: 'is_active',
            sortable: true,
            sortKey: 'is_active',
        },
        (self.$global.hasAnyPermission(['modelsupdate', 'modelsdestroy'])
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
                operationTitle: 'title.models',
                formFields: {...FORM_STATE},
                filters: {...FILTER_STATE},
                listUrl: 'models',
                columns: COLUMN_DEFINATION(this),
                dropdowns: {
                    roles: [],
                    brands: [],
                },
                show: true,
            }
        },
        mounted() {
            this.getBrands()

            if (this.$route.query && this.$route.query.operation && this.$route.query.oToken) {
                this.handleEditClick(this.$route.query.oToken)
            }
        },
        methods: {
            setOperation(operation = 'add', operationToken = null) {
                this.operationTitle = (operation === 'add' ? 'title.addModel' : 'title.editModel')
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
                        url: this.formFields.id ? 'models/update' : 'models/create',
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
                        url: '/models/delete',
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
                        url: `/models/detail/${id}`,
                    })
                    this.operationTitle = this.$t('title.editModel')
                    const {data} = response
                    const {brand} = data
	                delete data.brand
                    this.formFields = {...data, is_active: (data.is_active > 0), brand_id: (brand ? brand.id : null)}
                } catch (e) {
                    itemEditFails(this.$notification)
                    this.formFields = {...FORM_STATE}
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
            hasListAccess() {
                return this.$global.hasPermission('modelsview')
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

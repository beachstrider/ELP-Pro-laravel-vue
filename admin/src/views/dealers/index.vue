<template>
    <div v-if="show">
        <div class="card">
            <div class="card-header card-header-flex pb-2">
                <div class="w-100 mt-2">
                    <div class="row">
                        <div class="col-8">
                            <h5 class="mt-3 ml-0 mr-3 mb-2">
                                <strong>
                                    <template v-if="operation === null">{{$t('title.dealers')}}</template>
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
                                                      v-if="$global.hasPermission('dealersstore')" v-b-tooltip.hover>
                                                <i class="fe fe-plus"></i> {{$t('button.addNew')}}
                                            </b-button>
                                            <b-button size="sm" :title="$t('button.title.filterRecords')"
                                                      variant="outline-info"
                                                      @click="filters.visible = !filters.visible" v-b-tooltip.hover
                                                      v-if="$global.hasPermission('dealersview')">
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
                <div class="dealers-table">
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

                            <b-badge v-show="record.item.is_active > 0" variant="success">{{ $t('msc.active') }}
                            </b-badge>
                            <b-badge v-show="record.item.is_active <= 0">{{ $t('msc.de_active') }}</b-badge>
                        </template>
                        <template v-slot:cell(action)="record">
                            <a @click="setOperation('edit', record.item.id)"
                               :title="$t('button.title.editItem')" v-if="$global.hasPermission('dealersupdate')"
                               v-b-tooltip.hover>
                                <i class="fe fe-edit"></i>
                            </a>
                            <a-popconfirm title="Are you sure？" @confirm="handleDeleteClick(record.item.id)"
                                          v-if="$global.hasPermission('dealersdestroy')">
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
                </div><!-- /.dealers-table -->
                <div class="dealers-operation">
                    <a-drawer
                            placement="right"
                            :width="'80%'"
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
                                        <b-col lg="4" md="4" sm="12">
                                            <b-form-group
                                                :label="$t('input.dealerId')+' *'"
                                                label-for="dealer_id"
                                                :invalid-feedback="formErrors.first('dealer_id')"
                                            >
                                                <b-form-input
                                                    id="dealer_id"
                                                    v-model="formFields.dealer_id"
                                                    type="text"
                                                    :state="((formErrors.has('dealer_id') ? false : null))"
                                                    ref="name"
                                                    @focus="$event.target.select()"
                                                ></b-form-input>
                                            </b-form-group>
                                        </b-col><!--/b-col-->
                                        <b-col lg="4" md="4" sm="12">
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
                                        <b-col lg="4" md="4" sm="12">
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

                                    </b-row><!--/b-row-->
                                    <b-row>
                                        <b-col lg="4" md="4" sm="12">
                                            <div class="form-group">
                                                <div class="clearfix">
                                                    <span class="pull-left">
                                                        <label for="main_location" class="d-block">{{$t('input.mainLocation')}} *</label>
                                                    </span><!-- /.pull-left -->
                                                    <span class="pull-right">
                                                        <quick-location-form
                                                            allow-update="1"
                                                            :allow-create="(mainLocationDisable ? 0 : 1)"
                                                            :id="formFields.main_location_id"
                                                            :after-create="handleAfterQuickMainLocationCreated"
                                                            :after-update="handleAfterQuickMainLocationUpdated">
                                                        </quick-location-form>
                                                    </span><!-- /.pull-right -->
                                                </div><!-- /.clearfix -->
                                                <treeselect
                                                    :disabled="mainLocationDisable"
                                                    :multiple="false"
                                                    :options="mainLocations"
                                                    placeholder=""
                                                    v-model="formFields.main_location_id"
                                                    :class="[{'invalid is-invalid': (formErrors.has('main_location_id'))}]"
                                                />
                                                <div class="invalid-feedback">{{formErrors.first('main_location_id')}}</div>
                                            </div><!-- /.form-group -->
                                        </b-col><!--/b-col-->
                                        <b-col lg="4" md="4" sm="12">
                                            <b-form-group
                                                :label="$t('input.phone')+' *'"
                                                label-for="phone"
                                                :invalid-feedback="formErrors.first('phone')"
                                            >
                                                <b-form-input
                                                    id="phone"
                                                    v-model="formFields.phone"
                                                    type="text"
                                                    :state="((formErrors.has('phone') ? false : null))"
                                                    ref="phone"
                                                    @focus="$event.target.select()"
                                                ></b-form-input>
                                            </b-form-group>
                                        </b-col><!--/b-col-->
                                        <b-col lg="4" md="4" sm="12">
                                            <b-form-group
                                                :label="$t('input.fax')+' *'"
                                                label-for="fax"
                                                :invalid-feedback="formErrors.first('fax')"
                                            >
                                                <b-form-input
                                                    id="fax"
                                                    v-model="formFields.fax"
                                                    type="text"
                                                    :state="((formErrors.has('fax') ? false : null))"
                                                    ref="fax"
                                                    @focus="$event.target.select()"
                                                ></b-form-input>
                                            </b-form-group>
                                        </b-col><!--/b-col-->
                                    </b-row><!--/b-row-->
                                    <b-row>
                                        <b-col lg="12" md="12" sm="12">

                                            <b-form-group
                                                :label="$t('input.comment')+' '"
                                                label-for="comment"
                                                :invalid-feedback="formErrors.first('comment')"
                                            >
                                                <b-form-input
                                                    id="comment"
                                                    v-model="formFields.comment"
                                                    type="text"
                                                    :state="((formErrors.has('comment') ? false : null))"
                                                    ref="comment"
                                                    @focus="$event.target.select()"
                                                ></b-form-input>
                                            </b-form-group>
                                        </b-col>
                                    </b-row>

                                    <b-row>
                                        <!--# Start dealer additional location #-->
                                        <b-col lg="12" md="12" sm="12">
                                            <b-card class="mb-0">
                                                <b-card-header v-b-toggle.dealer-location class="p-0">
                                                    <h4 class="mb-0">
														<span class="badge badge-primary">
															{{$t('title.dealerAdditionalLocations')}}
														</span>
                                                        <small v-show="dealer_additional_locations.length > 0">
                                                            - {{dealer_additional_locations.length}} Item/s
                                                        </small>
                                                    </h4>
                                                </b-card-header><!-- /.p-0-->
                                                <b-collapse id="dealer-location">
                                                    <div class="bg-light p-3">
                                                        <b-row>
                                                            <b-col lg="4" md="4" sm="12">
                                                                <div class="form-group">
                                                                    <div class="clearfix">
                                                                        <span class="pull-left">
                                                                            <label for="additional_location" class="d-block">{{$t('input.location')}} *</label>
                                                                        </span><!-- /.pull-left -->
                                                                    </div><!-- /.clearfix -->
                                                                    <treeselect
                                                                        :multiple="false"
                                                                        :options="mainLocations"
                                                                        placeholder=""
                                                                        v-model="dealer_additional_location.location_id"
                                                                        :class="[{'invalid is-invalid': (formErrors.has('additional_location_id'))}]"
                                                                    />
                                                                    <div class="invalid-feedback">{{formErrors.first('additional_location_id')}}</div>
                                                                </div><!-- /.form-group -->
                                                            </b-col><!--/b-col-->
                                                            <b-col lg="4" md="4" sm="12">
                                                                <div class="form-group">
                                                                    <div class="clearfix">
                                                                        <span class="pull-left">
                                                                            <label for="dealerContactLocation" class="d-block">{{$t('input.locationType')}} *</label>
                                                                        </span><!-- /.pull-left -->
                                                                        <span class="pull-right">
                                                                            <quick-location-type-form
                                                                                allow-update="1"
                                                                                allow-create="1"
                                                                                :id="dealer_additional_location.location_type_id"
                                                                                :after-create="handleAfterQuickLocationTypeCreated"
                                                                                :after-update="handleAfterQuickLocationTypeUpdated">
                                                                            </quick-location-type-form>
                                                                        </span><!-- /.pull-right -->
                                                                    </div><!-- /.clearfix -->
                                                                    <treeselect
                                                                        :multiple="false"
                                                                        :options="dropdowns.location_types"
                                                                        placeholder=""
                                                                        v-model="dealer_additional_location.location_type_id"
                                                                        :class="[{'invalid is-invalid': (formErrors.has('location_type_id'))}]"
                                                                    />
                                                                    <div class="invalid-feedback">{{formErrors.first('location_type_id')}}</div>
                                                                </div><!-- /.form-group -->
                                                            </b-col><!--/b-col-->

                                                            <b-col lg="2" md="2" sm="12">
                                                                <div class="form-group">
                                                                    <label class="d-block"><pre> </pre></label>
                                                                    <b-button variant="info" type="button" size="sm" class="btn-block" @click="handleAddUpdateDealerAdditionalLocationClick()">
                                                                        {{$t('button.assignLocation')}}
                                                                    </b-button><!--/b-button-->
                                                                </div><!-- /.form-group -->
                                                            </b-col><!--/b-col-->
                                                            <b-col lg="2" md="2" sm="12">
                                                                <div class="form-group">
                                                                    <label class="d-block"><pre> </pre></label>
                                                                    <b-button variant="warning" type="button" size="sm" class="btn-block" @click="resetDealerAdditionalLocation()">
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
                                                                            <th width="180">{{$t('column.locations')}}</th>
                                                                            <th width="180">{{$t('column.locationType')}}</th>
                                                                            <th width="60">{{$t('column.action')}}</th>
                                                                        </tr>
                                                                    </thead>
                                                                    <tbody>
                                                                        <tr v-for="(ct, index) in dealer_additional_locations" :class="[{'table-primary': ct.token === dealer_additional_location.token}]">
                                                                            <td>{{index + 1}}</td>
                                                                            <td>{{ [_.find(dropdowns.locations, {id: ct.location_id})].map((item) => item.label).join(', ') }}</td>
                                                                            <td>{{ [_.find(dropdowns.location_types, {id: ct.location_type_id})].map((item) => item.label).join(', ') }}</td>
                                                                            <td>
                                                                                <a @click="handleEditDealerAdditionalLocationClick(ct.token)"
                                                                                   :title="$t('button.title.editItem')"
                                                                                   v-b-tooltip.hover>
                                                                                    <i class="fe fe-edit"></i>
                                                                                </a>
                                                                                <a-popconfirm title="Are you sure？" @confirm="handleDeleteDealerAdditionalLocationClick(ct.token)">
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
                                                                    <tfoot v-show="dealer_additional_locations.length <= 0">
                                                                        <tr>
                                                                            <th colspan="5">{{$t('title.noDataAvailable')}}</th>
                                                                        </tr>
                                                                    </tfoot>
                                                                </table><!-- /.table table-bordered -->
                                                            </b-col><!--/b-col-->
                                                        </b-row><!--/b-row-->
                                                    </div><!-- /.bg-light -->
                                                </b-collapse><!-- /#dealer-location -->
                                            </b-card><!--/b-card-->
                                        </b-col><!--/b-col-->

                                        <!--# Start dealer contacts #-->
                                        <b-col lg="12" md="12" sm="12" class="mt-3">
                                            <b-card class="mb-0">
                                                <b-card-header v-b-toggle.dealer-contacts class="p-0">
                                                    <h4 class="mb-0">
														<span class="badge badge-primary">
															{{$t('title.dealerContacts')}}
														</span>
                                                        <small v-show="dealer_contacts.length > 0">
                                                            - {{dealer_contacts.length}} Item/s
                                                        </small>
                                                    </h4>
                                                </b-card-header><!-- /.p-0-->
                                                <b-collapse id="dealer-contacts">
                                                    <div class="bg-light p-3">
                                                        <b-row>
                                                            <b-col lg="4" md="4" sm="12">
                                                                <div class="form-group">
                                                                    <div class="clearfix">
                                                                <span class="pull-left">
                                                                    <label for="contact" class="d-block">{{$t('input.contact')}} *</label>
                                                                </span><!-- /.pull-left -->
                                                                        <span class="pull-right">
                                                                    <quick-contact-form
                                                                            allow-update="1"
                                                                            allow-create="1"
                                                                            :id="dealer_contact.contact_id"
                                                                            :after-create="handleAfterQuickContactCreated"
                                                                            :after-update="handleAfterQuickContactUpdated">
                                                                    </quick-contact-form>
                                                                </span><!-- /.pull-right -->
                                                                    </div><!-- /.clearfix -->
                                                                    <treeselect
                                                                            :multiple="false"
                                                                            :options="dropdowns.contacts"
                                                                            placeholder=""
                                                                            v-model="dealer_contact.contact_id"
                                                                            :class="[{'invalid is-invalid': (formErrors.has('contact_id'))}]"
                                                                    />
                                                                    <div class="invalid-feedback">{{formErrors.first('contact_id')}}</div>
                                                                </div><!-- /.form-group -->
                                                            </b-col><!--/b-col-->
                                                            <b-col lg="4" md="4" sm="12">
                                                                <div class="form-group">
                                                                    <div class="clearfix">
                                                                <span class="pull-left">
                                                                    <label for="contact" class="d-block">{{$t('input.locations')}} </label>
                                                                </span><!-- /.pull-left -->
                                                                    </div><!-- /.clearfix -->
                                                                    <treeselect
                                                                            :multiple="true"
                                                                            :options="dropdowns.locations.filter(item => (_.includes(dealer_additional_locations.map(i => i.location_id), item.id) || formFields.main_location_id === item.id ))"
                                                                            placeholder=""
                                                                            v-model="dealer_contact.locations"
                                                                            :class="[{'invalid is-invalid': (formErrors.has('locations'))}]"
                                                                    />
                                                                    <div class="invalid-feedback">{{formErrors.first('locations')}}</div>
                                                                </div><!-- /.form-group -->
                                                            </b-col><!--/b-col-->

                                                            <b-col lg="2" md="2" sm="12">
                                                                <div class="form-group">
                                                                    <label class="d-block"><pre> </pre></label>
                                                                    <b-button variant="info" type="button" size="sm" class="btn-block" @click="handleAddUpdateDealerContactClick()">
                                                                        {{$t('button.assignLocation')}}
                                                                    </b-button><!--/b-button-->
                                                                </div><!-- /.form-group -->
                                                            </b-col><!--/b-col-->
                                                            <b-col lg="2" md="2" sm="12">
                                                                <div class="form-group">
                                                                    <label class="d-block"><pre> </pre></label>
                                                                    <b-button variant="warning" type="button" size="sm" class="btn-block" @click="resetDealerContact()">
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
                                                                            <th width="180">{{$t('column.contact')}}</th>
                                                                            <th width="180">{{$t('column.locations')}}</th>
                                                                            <th width="60">{{$t('column.action')}}</th>
                                                                        </tr>
                                                                    </thead>
                                                                    <tbody>
                                                                        <tr v-for="(ct, index) in dealer_contacts" :class="[{'table-primary': ct.token === dealer_contact.token}]">
                                                                            <td>{{index + 1}}</td>
                                                                            <td>
                                                                                <div><strong>{{$t('column.contact')}}:</strong> {{ [_.find(dropdowns.contacts, {id: ct.contact_id})].map((item) => item.label).join(', ') }}</div>
                                                                                <div><strong>{{$t('column.function')}}:</strong> {{ [_.find(dropdowns.contacts, {id: ct.contact_id})].map((item) => item.functions).join(', ') }}</div>
                                                                                <div><strong>{{$t('column.phone')}}:</strong> {{ [_.find(dropdowns.contacts, {id: ct.contact_id})].map((item) => item.phone).join(', ') }}</div>
                                                                                <div><strong>{{$t('column.email')}}:</strong> {{ [_.find(dropdowns.contacts, {id: ct.contact_id})].map((item) => item.email).join(', ') }}</div>
                                                                            </td>
                                                                            <td>
                                                                            <span class="white-space-pre">
                                                                                {{ getLocationsLabel(ct, formFields.dealer_additional_locations[0]) }}
                                                                            </span><!-- /.while-space-p`re -->
                                                                            </td>
                                                                            <td>
                                                                                <a @click="handleEditDealerContactClick(ct.token)"
                                                                                   :title="$t('button.title.editItem')"
                                                                                   v-b-tooltip.hover>
                                                                                    <i class="fe fe-edit"></i>
                                                                                </a>
                                                                                <a-popconfirm title="Are you sure？" @confirm="handleDeleteDealerContactClick(ct.token)">
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
                                                                    <tfoot v-show="dealer_contacts.length <= 0">
                                                                        <tr>
                                                                            <th colspan="5">{{$t('title.noDataAvailable')}}</th>
                                                                        </tr>
                                                                    </tfoot>
                                                                </table><!-- /.table table-bordered -->
                                                            </b-col><!--/b-col-->
                                                        </b-row><!--/b-row-->
                                                    </div><!-- /.bg-light -->
                                                </b-collapse><!-- /#dealer-contacts -->
                                            </b-card><!--/b-card-->
                                        </b-col><!--/b-col-->

                                        <!--# Start dealer brands #-->
                                        <b-col lg="12" md="12" sm="12" class="mt-3">
                                            <b-card class="mb-0">
                                                <b-card-header v-b-toggle.dealer-brands class="p-0">
                                                    <h4 class="mb-0">
														<span class="badge badge-primary">
															{{$t('title.dealerBrands')}}
														</span>
                                                        <small v-show="dealer_brands.length > 0">
                                                            - {{dealer_brands.length}} Item/s
                                                        </small>
                                                    </h4>
                                                </b-card-header><!-- /.p-0-->
                                                <b-collapse id="dealer-brands">
                                                    <div class="bg-light p-3">
                                                        <b-row>
                                                            <b-col lg="4" md="4" sm="12">
                                                                <div class="form-group">
                                                                    <div class="clearfix">
                                                                <span class="pull-left">
                                                                    <label for="brand" class="d-block">{{$t('input.brand')}} *</label>
                                                                </span><!-- /.pull-left -->
                                                                        <span class="pull-right">
                                                                    <quick-brand-form
                                                                            allow-update="1"
                                                                            allow-create="1"
                                                                            :id="dealer_brand.brand_id"
                                                                            :after-create="handleAfterQuickBrandCreated"
                                                                            :after-update="handleAfterQuickBrandUpdated">
                                                                    </quick-brand-form>
                                                                </span><!-- /.pull-right -->
                                                                    </div><!-- /.clearfix -->
                                                                    <treeselect
                                                                            :multiple="false"
                                                                            :options="dropdowns.brands"
                                                                            placeholder=""
                                                                            v-model="dealer_brand.brand_id"
                                                                            :class="[{'invalid is-invalid': (formErrors.has('brand_id'))}]"
                                                                            @select="handleBrandSelect"
                                                                    />
                                                                    <div class="invalid-feedback">{{formErrors.first('brand_id')}}</div>
                                                                </div><!-- /.form-group -->
                                                            </b-col><!--/b-col-->
                                                            <b-col lg="4" md="4" sm="12">
                                                                <div class="form-group">
                                                                    <div class="clearfix">
                                                                <span class="pull-left">
                                                                    <label for="brand" class="d-block">{{$t('input.models')}} *</label>
                                                                </span><!-- /.pull-left -->
                                                                        <span class="pull-right">
                                                                    <quick-model-form
                                                                            :allow-create="(dealer_brand.brand_id ? 1 : 0)"
                                                                            :depend-brand-id="dealer_brand.brand_id"
                                                                            :after-create="handleAfterQuickModelCreated">
                                                                    </quick-model-form>
                                                                </span><!-- /.pull-right -->
                                                                    </div><!-- /.clearfix -->
                                                                    <treeselect
                                                                            :multiple="true"
                                                                            :options="_.filter(dropdowns.models, (item) => item.brand_id === dealer_brand.brand_id)"
                                                                            placeholder=""
                                                                            v-model="dealer_brand.models"
                                                                            :class="[{'invalid is-invalid': (formErrors.has('models'))}]"
                                                                    />
                                                                    <div class="invalid-feedback">{{formErrors.first('models')}}</div>
                                                                </div><!-- /.form-group -->
                                                            </b-col><!--/b-col-->
                                                            <b-col lg="2" md="2" sm="12">
                                                                <div class="form-group">
                                                                    <label class="d-block"><pre> </pre></label>
                                                                    <b-button variant="info" type="button" size="sm" class="btn-block" @click="handleAddUpdateDealerBrandClick()">
                                                                        {{$t('button.assignBrand')}}
                                                                    </b-button><!--/b-button-->
                                                                </div><!-- /.form-group -->
                                                            </b-col><!--/b-col-->
                                                            <b-col lg="2" md="2" sm="12">
                                                                <div class="form-group">
                                                                    <label class="d-block"><pre> </pre></label>
                                                                    <b-button variant="warning" type="button" size="sm" class="btn-block" @click="resetDealerBrand()">
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
                                                                        <th>{{$t('column.brand')}}</th>
                                                                        <th>{{$t('column.models')}}</th>
                                                                        <th>{{$t('column.action')}}</th>
                                                                    </tr>
                                                                    </thead>
                                                                    <tbody>
                                                                    <tr v-for="(ct, index) in dealer_brands"  :class="[{'table-primary': ct.token === dealer_brand.token}]">
                                                                        <td>{{index + 1}}</td>
                                                                        <td>{{ [_.find(dropdowns.brands, {id: ct.brand_id})].map((item) => item.label).join(', ') }}</td>
                                                                        <td>
                                                                        <span class="white-space-pre">
                                                                            {{ getModelsLabel(ct) }}
                                                                        </span><!-- /.while-space-pre -->
                                                                        </td>
                                                                        <td>
                                                                            <a @click="handleEditDealerBrandClick(ct.token)"
                                                                               :title="$t('button.title.editItem')"
                                                                               v-b-tooltip.hover>
                                                                                <i class="fe fe-edit"></i>
                                                                            </a>
                                                                            <a-popconfirm title="Are you sure？" @confirm="handleDeleteDealerBrandClick(ct.token)">
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
                                                                    <tfoot v-show="dealer_brands.length <= 0">
                                                                    <tr>
                                                                        <th colspan="5">{{$t('title.noDataAvailable')}}</th>
                                                                    </tr>
                                                                    </tfoot>
                                                                </table><!-- /.table table-bordered -->
                                                            </b-col><!--/b-col-->
                                                        </b-row><!--/b-row-->
                                                    </div><!-- /.bg-light -->
                                                </b-collapse><!-- /#dealer-brands -->
                                            </b-card><!--/b-card-->
                                        </b-col><!--/b-col-->

                                        <!--# Start dealer documents #-->
                                        <b-col lg="12" md="12" sm="12" class="mt-3 mb-5">
                                            <b-card class="mb-0">
                                                <b-card-header v-b-toggle.dealer-documents class="p-0">
                                                    <h4 class="mb-0">
														<span class="badge badge-primary">
															{{$t('title.dealerAttachments')}}
														</span>
                                                        <small v-show="dealer_documents.length > 0">
                                                            - {{dealer_documents.length}} Item/s
                                                        </small>
                                                    </h4>
                                                </b-card-header><!-- /.p-0-->

                                                <b-collapse id="dealer-documents">
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
                                                                            v-model="dealer_document.title"
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
                                                                    <upload v-model="dealer_document.document"
                                                                            :disabled="dealer_document.document"
                                                                            :title="(dealer_document.document ? $t('msc.uploadedFile') : $t('msc.uploadFile'))"
                                                                            css-class="mt-0 btn-sm"></upload>
                                                                    <b-button :title="$t('msc.removeUpload')"
                                                                              variant="outline-primary"
                                                                              v-b-tooltip.hover
                                                                              class="ml-2 ml-2 btn-sm"
                                                                              @click="() => {dealer_document.document = null; dealer_document.document = null}"
                                                                              :disabled="!dealer_document.document"
                                                                              v-if="dealer_document.document">
                                                                        <i class="fa fa-close"></i>
                                                                    </b-button>
                                                                    <b-button :title="$t('button.download')"
                                                                              v-b-tooltip.hover
                                                                              variant="outline-primary"
                                                                              class="ml-2 ml-2 btn-sm"
                                                                              v-if="dealer_document.document && dealer_document.document.download_url"
                                                                              :disabled="!(dealer_document.document && dealer_document.document.download_url)"
                                                                              :href="(dealer_document.document ? dealer_document.document.download_url : '')"
                                                                              target="_blank">
                                                                        <i class="fa fa-cloud-download"></i>
                                                                    </b-button>
                                                                </b-form-group>
                                                                <div class="invalid-feedback d-block">{{formErrors.first('document')}}</div>
                                                            </b-col><!--/b-col-->
                                                            <b-col lg="2" md="2" sm="12">
                                                                <div class="form-group">
                                                                    <label class="d-block"><pre> </pre></label>
                                                                    <b-button variant="info" type="button" size="sm" class="btn-block" @click="handleAddUpdateDealerDocumentClick()">
                                                                        <i class="fa fa-plus"></i>
                                                                    </b-button><!--/b-button-->
                                                                </div><!-- /.form-group -->
                                                            </b-col><!--/b-col-->
                                                            <b-col lg="2" md="2" sm="12">
                                                                <div class="form-group">
                                                                    <label class="d-block"><pre> </pre></label>
                                                                    <b-button variant="warning" type="button" size="sm" class="btn-block" @click="resetDealerDocument()">
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
                                                                    <tr v-for="(ct, index) in dealer_documents" :class="[{'table-primary': ct.token === dealer_contact.token}]">
                                                                        <td>{{index + 1}}</td>
                                                                        <td>
                                                                            {{ct.title}}
                                                                        </td>
                                                                        <td>
                                                                            <a @click="handleEditDealerDocumentClick(ct.token)"
                                                                               :title="$t('button.title.editItem')"
                                                                               v-b-tooltip.hover>
                                                                                <i class="fe fe-edit"></i>
                                                                            </a>
                                                                            <a :title="$t('button.download')" class=" ml-1"
                                                                               :href="ct.document.download_url"
                                                                               target="_blank" v-b-tooltip.hover>
                                                                                <i class="fa fa-cloud-download"></i>
                                                                            </a>
                                                                            <a-popconfirm title="Are you sure？" @confirm="handleDeleteDealerDocumentClick(ct.token)">
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
                                                                    <tfoot v-show="dealer_documents.length <= 0">
                                                                    <tr>
                                                                        <th colspan="3">{{$t('title.noDataAvailable')}}</th>
                                                                    </tr>
                                                                    </tfoot>
                                                                </table><!-- /.table table-bordered -->
                                                            </b-col><!--/b-col-->
                                                        </b-row><!--/b-row-->
                                                    </div><!-- /.bg-light -->
                                                </b-collapse><!-- /#dealer-documents-->
                                            </b-card><!-- /b-card -->
                                        </b-col><!--/b-col-->
                                    </b-row><!--/b-row-->
                                </b-col><!--/b-col-->
                                <div class="drawer-footer">
                                    <b-row>
                                        <b-col md="6" lg="6" sm="12" class="text-left">
                                            <b-form-group class="mb-0">
                                                <b-form-checkbox size="lg" v-model="formFields.is_active" name="check-button">{{$t('input.is_active')}}</b-form-checkbox>
                                            </b-form-group>
                                        </b-col><!-- /b-col -->
                                        <b-col md="6" lg="6" sm="12">
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
                                        </b-col><!-- /b-col -->
                                    </b-row>
                                </div>
                            </b-row><!--/b-row-->
                        </form><!--/form-->
                    </a-drawer>
                </div><!--/.dealers-operation-->
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
    import DealerContactMixin from "./DealerContactMixin"
    import _ from 'lodash'
    import DealerBrandMixin from "./DealerBrandMixin";
    import DealerAdditionalLocationMixin from "./DealerAdditionalLocationMixin";
    import DealerDocumentMixin from "./DealerDocumentMixin";
    import QuickContactForm from "./../contacts/QuickContactForm";
    import QuickLocationForm from "./../locations/QuickLocationForm";
    import QuickLocationTypeForm from "./../locationTypes/QuickLocationTypeForm";
    import QuickBrandForm from "./../brands/QuickBrandForm";
    import QuickModelForm from "./../models/QuickModelForm";

    const FORM_STATE = {
        dealer_id: null,
        name: null,
        email: null,
        phone: null,
        fax: null,
        comment: null,
        is_active: true,
        _method: 'post',
        dealer_contacts: [],
        dealer_brands: [],
        dealer_additional_locations: [],
        dealer_documents: [],
    };

    const FILTER_STATE = {
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
            label: self.$t('column.dealerId'),
            key: 'dealer_id',
            sortable: false,
        },
        {
            label: self.$t('column.name'),
            key: 'name',
            sortable: false,
        },
        {
            label: self.$t('column.phone'),
            key: 'phone',
            sortable: false,
            sortKey: 'title',
        },
        {
            label: self.$t('column.email'),
            key: 'email',
            sortable: false,
        },
        {
            label: self.$t('column.status'),
            key: 'is_active',
            sortable: true,
            sortKey: 'is_active',
        },
        (self.$global.hasAnyPermission(['dealersupdate', 'dealersdestroy'])
        ? {
            label: self.$t('column.action'),
            class: 'text-right',
            key: 'action',
            width: 150,
        } : {}),
    ];

    export default {
        mixins: [ListingMixin, DealerContactMixin, DealerBrandMixin, DealerAdditionalLocationMixin, DealerDocumentMixin],
        components: {
            Datepicker,
            Treeselect,
            QuickContactForm,
            QuickLocationForm,
            QuickLocationTypeForm,
            QuickBrandForm,
            QuickModelForm
        },
        data() {
            return {
                operationTitle: 'title.dealers',
                formFields: {...FORM_STATE},
                filters: {...FILTER_STATE},
                listUrl: 'dealers',
                columns: COLUMN_DEFINATION(this),
                dropdowns: {
                    dealers: [],
                    locations: [],
                    location_types: [],
                    contacts: [],
                    brands: [],
                    models: [],
                },
                show: true,
            }
        },
        mounted() {
            this.getDealers();
            this.getLocations();
            this.getLocationTypes();
            this.getContacts();
            this.getBrands();
            this.getModels();

            if (this.$route.query && this.$route.query.operation && this.$route.query.oToken) {
                this.handleEditClick(this.$route.query.oToken)
            }
        },
        methods: {
            setOperation(operation = 'add', operationToken = null) {
                this.operationTitle = (operation === 'add' ? 'title.addDealer' : 'title.editDealer')
                this.resetDealerContact()
                this.resetDealerBrand()
                this.resetDealerAdditionalLocation()
                this.resetDealerDocument();
                this.dealer_documents.length = 0;

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
                        url: this.formFields.id ? 'dealers/update' : 'dealers/create',
                        method: 'post',
                        data: {...this.formFields, dealer_contacts: this.dealer_contacts, dealer_brands: this.dealer_brands, dealer_additional_locations: this.dealer_additional_locations, dealer_documents: this.dealer_documents},
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
                        url: '/dealers/delete',
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
                        url: `/dealers/detail/${id}`,
                    })
                    this.operationTitle = this.$t('title.editDealer')
                    const {data} = response
                    const {dealer_contacts, dealer_brands, dealer_additional_locations, dealer_documents, main_location} = data
                    delete data.dealer_contacts; delete data.dealer_brands; delete data.main_location; delete data.dealer_additional_locations; delete data.dealer_documents;


                    this.formFields = {
                        ...this.formFields,
                        ...data,
                        main_location_id: (main_location ? main_location.id : null),
                        is_active: (data.is_active > 0),
                    }

                    this.dealer_contacts = dealer_contacts.map((item) => {
                        return {
                            id: item.id,
                            token: item.token,
                            contact_id: item.contact.id,
                            locations: item.locations.map(entity => entity.id),
                        }
                    })

                    this.dealer_brands = dealer_brands.map((item) => {
                        return {
                            id: item.id,
                            token: item.token,
                            brand_id: item.brand.id,
                            models: item.models.map(entity => entity.id)
                        }
                    })

                    this.dealer_additional_locations = dealer_additional_locations.map((item) => {
                        return {
                            id: item.id,
                            token: item.token,
                            location_id: item.location.id,
                            location_type_id: ((item.locationType && item.locationType.id) ? item.locationType.id : null),
                        }
                    })

                    this.dealer_documents = dealer_documents.map((item) => {
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
            async getDealers() {
                try {
                    const response = await request({
                        url: '/dropdowns/dealers',
                        method: "post"
                    })

                    const {data} = response
                    this.dropdowns.dealers = data

                } catch (e) {
                    this.dropdowns.dealers = []
                }
            },
            async getLocations() {
                try {
                    const response = await request({
                        url: '/dropdowns/locations',
                        method: "post"
                    })

                    const {data} = response
                    this.dropdowns.locations = data

                } catch (e) {
                    this.dropdowns.locations = []
                }
            },
            async getLocationTypes() {
                try {
                    const response = await request({
                        url: '/dropdowns/location/types',
                        method: "post"
                    })

                    const {data} = response
                    this.dropdowns.location_types = data

                } catch (e) {
                    this.dropdowns.location_types = []
                }
            },
            async getContacts() {
                try {
                    const response = await request({
                        url: '/dropdowns/contacts',
                        method: "post"
                    })

                    const {data} = response
                    this.dropdowns.contacts = data

                } catch (e) {
                    this.dropdowns.contacts = []
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
            handleAfterQuickContactCreated(inputs) {
                const {id, name} = inputs
                this.dropdowns.contacts.push({id: id, label: name})
                this.dealer_contact.contact_id = id;
            },
            handleAfterQuickContactUpdated(inputs) {
                const index = _.findIndex(this.dropdowns.contacts, {id: inputs.id})
                this.$set(this.dropdowns.contacts[index], 'label', inputs.name);
            },
            handleAfterQuickBrandCreated(inputs) {
                const {id, title} = inputs
                this.dropdowns.brands.push({id: id, label: inputs.title})
                this.dealer_brand.brand_id = id;
            },
            handleAfterQuickBrandUpdated(inputs) {
                const index = _.findIndex(this.dropdowns.brands, {id: inputs.id})
                this.$set(this.dropdowns.brands[index], 'label', inputs.title)
            },
            handleAfterQuickModelCreated(inputs) {
                const models = [...this.dropdowns.models, {id: inputs.id, label: inputs.title, is_active: inputs.is_active, brand_id: this.dealer_brand.brand_id}];
                this.$set(this.dropdowns, 'models', models)
                this.dealer_brand.models.push(inputs.id)
            },
            handleAfterQuickMainLocationCreated(inputs) {
                this.dropdowns.locations.push({id: inputs.id, label: `${inputs.street} ${inputs.street_no} ${inputs.city} ${inputs.zip} ${inputs.country}`})
                this.formFields.main_location_id = inputs.id;
            },
            handleAfterQuickMainLocationUpdated(inputs) {
                const index = _.findIndex(this.dropdowns.locations, {id: inputs.id})
                this.$set(this.dropdowns.locations[index], 'label', `${inputs.street} ${inputs.street_no} ${inputs.city} ${inputs.zip} ${inputs.country}`);
            },
            handleAfterQuickLocationTypeCreated(inputs) {
                this.dropdowns.location_types.push({id: inputs.id, label: inputs.title})
                this.dealer_contact.location_type_id = inputs.id;
            },
            handleAfterQuickLocationTypeUpdated(inputs) {
                const index = _.findIndex(this.dropdowns.locations, {id: inputs.id})
                this.$set(this.dropdowns.location_types[index], 'label', inputs.title);
            },
            hasListAccess() {
                return this.$global.hasPermission('dealersview')
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
                this.dealer_contacts.length = 0
                this.dealer_brands.length = 0
                this.dealer_additional_locations.length = 0
            }
        },
        computed: {
            ...mapState([
                'global',
                'settings'
            ]),
            mainLocations: {
                cache: false,
                get() {
                    return this.dropdowns.locations.map((item) => {
                        let locations = []

                        _.map(this.dealer_contacts, (contacts) => {
                            contacts.locations.map(location => locations.push(location))
                        })

                        return {
                            ...item,
                            isDisabled: (_.includes( locations , item.id))
                        }
                    });
                }
            },
            mainLocationDisable: {
                cache: false,
                get() {
                    let locations = []

                    _.map(this.dealer_contacts, (contacts) => {
                        contacts.locations.map(location => locations.push(location))
                    })

                    return _.includes(locations, this.formFields.main_location_id)
                }
            }
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

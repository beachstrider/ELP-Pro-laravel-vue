<template>
	<div v-if="show">
		<div class="card" v-if="operation !== 'detail'">
			<div class="card-header card-header-flex pb-2">
				<div class="w-100 mt-2">
					<div class="row">
						<div class="col-8">
							<h5 class="mt-3 ml-0 mr-3 mb-2">
								<strong>
									<template v-if="operation === null">{{$t('title.clients')}}</template>
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
											          v-if="$global.hasPermission('clientsstore')" v-b-tooltip.hover>
												<i class="fe fe-plus"></i> {{$t('button.addNew')}}
											</b-button>
											<b-button size="sm" :title="$t('button.title.filterRecords')"
											          variant="outline-info"
											          @click="filters.visible = !filters.visible" v-b-tooltip.hover
											          v-if="$global.hasPermission('clientsview')">
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
				<div class="clients-table">
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
                            <a @click="setOperation('detail', record.item.id)"
                               :title="$t('button.title.itemDetails')" v-if="$global.hasPermission('clientsview')"
                               v-b-tooltip.hover>
                                <i class="fe fe-eye"></i>
                            </a>
							<a class="ml-1" @click="setOperation('edit', record.item.id)"
							   :title="$t('button.title.editItem')" v-if="$global.hasPermission('clientsupdate')"
							   v-b-tooltip.hover>
								<i class="fe fe-edit"></i>
							</a>
							<a-popconfirm title="Are you sure？" @confirm="handleDeleteClick(record.item.id)"
							              v-if="$global.hasPermission('clientsdestroy')">
								<i slot="icon" class="fe fe-trash"></i>
								<a class="ml-1"
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
				</div><!-- /.clients-table -->
				<div class="clients-operation">
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
										<b-col lg="3" md="3" sm="12">
											<b-form-group
													:label="$t('input.companyName')+' *'"
													label-for="company_name"
													:invalid-feedback="formErrors.first('company_name')"
											>
												<b-form-input
														id="company_name"
														v-model="formFields.company_name"
														type="text"
														:state="((formErrors.has('company_name') ? false : null))"
														@focus="$event.target.select()"
												></b-form-input>
											</b-form-group>
										</b-col><!--/b-col-->
										<b-col lg="3" md="3" sm="12">
											<b-form-group
													:label="$t('input.phone')+' *'"
													label-for="phone"
													:invalid-feedback="formErrors.first('phone')"
											>
												<b-form-input
														id="phone"
														v-model="formFields.phone"
														type="number"
														pattern="^[0-9-+()]*"
														title="+(XXX) XXX"
														:state="((formErrors.has('phone') ? false : null))"
														@focus="$event.target.select()"
												></b-form-input>
											</b-form-group>
										</b-col><!--/b-col-->
										<b-col lg="3" md="3" sm="12">
											<b-form-group
													:label="$t('input.email')+' *'"
													label-for="email"
													:invalid-feedback="formErrors.first('email')"
											>
												<b-form-input
														id="email"
														v-model="formFields.email"
														type="text"
														:state="((formErrors.has('email') ? false : null))"
														@focus="$event.target.select()"
												></b-form-input>
											</b-form-group>
										</b-col><!--/b-col-->
										<b-col lg="3" md="3" sm="12">
											<b-form-group
													:label="$t('input.fax')"
													label-for="fax"
													:invalid-feedback="formErrors.first('fax')"
											>
												<b-form-input
														id="fax"
														v-model="formFields.fax"
														type="text"
														:state="((formErrors.has('fax') ? false : null))"
														@focus="$event.target.select()"
												></b-form-input>
											</b-form-group>
										</b-col><!--/b-col-->

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
													:label="$t('input.dealers')"
													label-for="client_dealers"
													:invalid-feedback="formErrors.first('client_dealers')"
											>
												<treeselect
														:multiple="true"
														:options="dropdowns.dealers"
														placeholder=""
														v-model="formFields.client_dealers"
														:class="[{'invalid is-invalid': (formErrors.has('client_dealers'))}]"
												/>
											</b-form-group>
										</b-col><!--/b-col-->
										<b-col lg="4" md="4" sm="12">
											<b-form-group
                                                    :label="$t('input.identificationNumber')+' *'"
													label-for="identification_number"
													:invalid-feedback="formErrors.first('identification_number')"
											>
                                                <b-form-input
                                                    id="identification_number"
                                                    v-model="formFields.identification_number"
                                                    type="text"
                                                    :state="((formErrors.has('identification_number') ? false : null))"
                                                    @focus="$event.target.select()"
                                                ></b-form-input>
											</b-form-group>
										</b-col><!--/b-col-->
										<b-col lg="12" md="12" sm="12">
											<b-form-group
													:label="$t('input.comment')+' '"
													label-for="comment"
													:invalid-feedback="formErrors.first('comment')"
											>
												<b-form-input
														id="comment"
														v-model="formFields.comment"
														:state="((formErrors.has('comment') ? false : null))"
														@focus="$event.target.select()"
												></b-form-input>
											</b-form-group>
										</b-col><!--/b-col-->
									</b-row><!--/b-row-->
									<b-row>
										<!--# Start client additional location #-->
										<b-col lg="12" md="12" sm="12">
											<b-card class="mb-0">
												<b-card-header v-b-toggle.client-location class="p-0">
													<h4 class="mb-0">
														<span class="badge badge-primary">
															{{$t('title.clientAdditionalLocations')}}
														</span>
														<small v-show="client_locations.length > 0">
															- {{client_locations.length}} Item/s
														</small>
													</h4>
												</b-card-header><!-- /.p-0-->

												<b-collapse id="client-location">
													<div class="bg-light p-3">
														<b-row>
															<b-col lg="4" md="4" sm="12">
																<div class="form-group">
																	<div class="clearfix">
															<span class="pull-left">
																<label class="d-block">{{$t('input.location')}} *</label>
															</span><!-- /.pull-left -->
															<span class="pull-right">
																<quick-location-form
																	allow-update="1"
																	allow-create="1"
																	:id="client_location.location_id"
																	:after-create="handleAfterQuickClientLocationCreated"
																	:after-update="handleAfterQuickClientLocationUpdated">
																</quick-location-form>
															</span><!-- /.pull-right -->
																	</div><!-- /.clearfix -->
																	<treeselect
                                                                        :multiple="false"
                                                                        :options="dropdowns.locations"
                                                                        placeholder=""
                                                                        v-model="client_location.location_id"
                                                                        :class="[{'invalid is-invalid': (formErrors.has('additional_location_id'))}]"
																	/>
																	<div class="invalid-feedback">{{formErrors.first('additional_location_id')}}</div>
																</div><!-- /.form-group -->
															</b-col><!--/b-col-->
															<b-col lg="4" md="4" sm="12">
																<div class="form-group">
																	<div class="clearfix">
                                                                        <span class="pull-left">
                                                                            <label class="d-block">{{$t('input.locationType')}} *</label>
                                                                        </span><!-- /.pull-left -->
                                                                        <span class="pull-right">
                                                                            <quick-location-type-form
                                                                                    allow-update="1"
                                                                                    allow-create="1"
                                                                                    :id="client_location.location_type_id"
                                                                                    :after-create="handleAfterQuickClientLocationTypeCreated"
                                                                                    :after-update="handleAfterQuickClientLocationTypeUpdated">
                                                                            </quick-location-type-form>
                                                                        </span><!-- /.pull-right -->
																	</div><!-- /.clearfix -->
																	<treeselect
																			:multiple="false"
																			:options="dropdowns.location_types"
																			placeholder=""
																			v-model="client_location.location_type_id"
																			:class="[{'invalid is-invalid': (formErrors.has('location_type_id'))}]"
																	/>
																	<div class="invalid-feedback">{{formErrors.first('location_type_id')}}</div>
																</div><!-- /.form-group -->
															</b-col><!--/b-col-->
                                                            <b-col lg="2" md="2" sm="12">
                                                                <div class="form-group">
                                                                    <label class="d-block"><pre> </pre></label>
                                                                    <b-button variant="info" type="button" size="sm" class="btn-block" @click="handleAddUpdateClientLocationClick()">
                                                                        {{$t('button.assignLocation')}}
                                                                    </b-button><!--/b-button-->
                                                                </div><!-- /.form-group -->
                                                            </b-col><!--/b-col-->
                                                            <b-col lg="2" md="2" sm="12">
                                                                <div class="form-group">
                                                                    <label class="d-block"><pre> </pre></label>
                                                                    <b-button variant="warning" type="button" size="sm" class="btn-block" @click="resetClientLocation()">
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
																		<th width="180">{{$t('column.location')}}</th>
																		<th width="180">{{$t('column.locationType')}}</th>
																		<th width="60">{{$t('column.action')}}</th>
																	</tr>
																	</thead>
																	<tbody>
																	<tr v-for="(cl, index) in client_locations" :class="[{'table-primary': cl.token === client_location.token}]">
																		<td>{{index + 1}}</td>
																		<td>{{ [_.find(dropdowns.locations, {id: cl.location_id})].map((item) => item.label).join(', ') }}</td>
																		<td>{{ [_.find(dropdowns.location_types, {id: cl.location_type_id})].map((item) => item.label).join(', ') }}</td>
																		<td>
																			<a @click="handleEditClientLocationClick(cl.token)"
																			   v-if="(!_.includes(client_contacts.map(({locations}) => locations.map(l => l)).reduce((a, b) => a.concat(b), []), cl.location_id)) || (cl.location_id === formFields.main_location_id)"
																			   :title="$t('button.title.editItem')"
																			   v-b-tooltip.hover>
																				<i class="fe fe-edit"></i>
																			</a>
																			<a-popconfirm title="Are you sure？" @confirm="handleDeleteClientLocationClick(cl.token)">
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
																	<tfoot v-show="client_locations.length <= 0">
																	<tr>
																		<th colspan="5">{{$t('title.noDataAvailable')}}</th>
																	</tr>
																	</tfoot>
																</table><!-- /.table table-bordered -->
															</b-col><!--/b-col-->
														</b-row><!--/b-row-->
													</div><!-- /.bg-light -->
												</b-collapse><!-- /#client-location -->
											</b-card><!-- /b-card -->
										</b-col><!--/b-col-->

										<!--# Start client contacts #-->
										<b-col lg="12" md="12" sm="12" class="mt-3">
											<b-card class="mb-0">
												<b-card-header v-b-toggle.client-contacts class="p-0">
													<h4 class="mb-0">
														<span class="badge badge-primary">
															{{$t('title.clientContacts')}}
														</span>
														<small v-show="client_contacts.length > 0">
															- {{client_contacts.length}} Item/s
														</small>
													</h4>
												</b-card-header><!-- /.p-0-->

												<b-collapse id="client-contacts">
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
																					:id="client_contact.contact_id"
																					:after-create="handleAfterQuickContactCreated"
																					:after-update="handleAfterQuickContactUpdated">
																			</quick-contact-form>
																		</span><!-- /.pull-right -->
																	</div><!-- /.clearfix -->
																	<treeselect
																			:multiple="false"
																			:options="dropdowns.contacts"
																			placeholder=""
																			v-model="client_contact.contact_id"
																			:class="[{'invalid is-invalid': (formErrors.has('contact_id'))}]"
																	/>
																	<div class="invalid-feedback">{{formErrors.first('contact_id')}}</div>
																</div><!-- /.form-group -->
															</b-col><!--/b-col-->
															<b-col lg="4" md="4" sm="12">
																<div class="form-group">
																	<div class="clearfix">
																<span class="pull-left">
																	<label class="d-block">{{$t('input.locations')}} </label>
																</span><!-- /.pull-left -->
																	</div><!-- /.clearfix -->
																	<treeselect
																			:multiple="true"
																			:options="dropdowns.locations.filter(item => (_.includes(client_locations.map(i => i.location_id), item.id) || formFields.main_location_id === item.id ))"
																			placeholder=""
																			v-model="client_contact.locations"
																			:class="[{'invalid is-invalid': (formErrors.has('locations'))}]"
																	/>
																	<div class="invalid-feedback">{{formErrors.first('locations')}}</div>
																</div><!-- /.form-group -->
															</b-col><!--/b-col-->
															<b-col lg="2" md="2" sm="12">
                                                                <div class="form-group">
                                                                    <label class="d-block"><pre> </pre></label>
                                                                    <b-button variant="info" type="button" size="sm" class="btn-block" @click="handleAddUpdateClientContactClick()">
                                                                        {{$t('button.assignContact')}}
                                                                    </b-button><!--/b-button-->
                                                                </div><!-- /.form-group -->
															</b-col><!--/b-col-->
                                                            <b-col lg="2" md="2" sm="12">
                                                                <div class="form-group">
                                                                    <label class="d-block"><pre> </pre></label>
                                                                    <b-button variant="warning" type="button" size="sm" class="btn-block" @click="resetClientContact()">
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
																		<tr v-for="(ct, index) in client_contacts" :class="[{'table-primary': ct.token === client_contact.token}]">
																			<td>{{index + 1}}</td>
																			<td>
																				<div><strong>{{$t('column.contact')}}:</strong> {{ [_.find(dropdowns.contacts, {id: ct.contact_id})].map((item) => item.label).join(', ') }}</div>
																				<div><strong>{{$t('column.function')}}:</strong> {{ [_.find(dropdowns.contacts, {id: ct.contact_id})].map((item) => item.functions).join(', ') }}</div>
																				<div><strong>{{$t('column.phone')}}:</strong> {{ [_.find(dropdowns.contacts, {id: ct.contact_id})].map((item) => item.phone).join(', ') }}</div>
																				<div><strong>{{$t('column.email')}}:</strong> {{ [_.find(dropdowns.contacts, {id: ct.contact_id})].map((item) => item.email).join(', ') }}</div>
																			</td>
																			<td>
																			<span class="white-space-pre">
																				{{ getLocationsLabel(ct, formFields.client_locations[0]) }}
																			</span><!-- /.while-space-pre -->
																			</td>
																			<td>
																				<a @click="handleEditClientContactClick(ct.token)"
																				   :title="$t('button.title.editItem')"
																				   v-b-tooltip.hover>
																					<i class="fe fe-edit"></i>
																				</a>
																				<a-popconfirm title="Are you sure？" @confirm="handleDeleteClientContactClick(ct.token)">
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
																	<tfoot v-show="client_contacts.length <= 0">
																		<tr>
																			<th colspan="5">{{$t('title.noDataAvailable')}}</th>
																		</tr>
																	</tfoot>
																</table><!-- /.table table-bordered -->
															</b-col><!--/b-col-->
														</b-row><!--/b-row-->
													</div><!-- /.bg-light -->
												</b-collapse><!-- /#client-contacts-->
											</b-card><!-- /b-card -->
										</b-col><!--/b-col-->

										<!--# Start client brands #-->
										<b-col lg="12" md="12" sm="12" class="mt-3">
											<b-card class="mb-0">
												<b-card-header v-b-toggle.client-brands class="p-0">
													<h4 class="mb-0">
														<span class="badge badge-primary">
															{{$t('title.clientBrands')}}
														</span>
														<small v-show="client_brands.length > 0">
															- {{client_brands.length}} Item/s
														</small>
													</h4>
												</b-card-header><!-- /.p-0-->

												<b-collapse id="client-brands">
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
																			:id="client_brand.brand_id"
																			:after-create="handleAfterQuickBrandCreated"
																			:after-update="handleAfterQuickBrandUpdated">
																	</quick-brand-form>
																</span><!-- /.pull-right -->
																	</div><!-- /.clearfix -->
																	<treeselect
																			:multiple="false"
																			:options="dropdowns.brands"
																			placeholder=""
																			v-model="client_brand.brand_id"
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
																			:allow-create="(client_brand.brand_id ? 1 : 0)"
																			:depend-brand-id="client_brand.brand_id"
																			:after-create="handleAfterQuickModelCreated">
																	</quick-model-form>
																</span><!-- /.pull-right -->
																	</div><!-- /.clearfix -->
																	<treeselect
																			:multiple="true"
																			:options="_.filter(dropdowns.models, (item) => item.brand_id === client_brand.brand_id)"
																			placeholder=""
																			v-model="client_brand.models"
																			:class="[{'invalid is-invalid': (formErrors.has('models'))}]"
																	/>
																	<div class="invalid-feedback">{{formErrors.first('models')}}</div>
																</div><!-- /.form-group -->
															</b-col><!--/b-col-->
															<b-col lg="2" md="2" sm="12">
                                                                <div class="form-group">
																	<label class="d-block"><pre> </pre></label>
                                                                    <b-button variant="info" type="button" size="sm" class="btn-block" @click="handleAddUpdateClientBrandClick()">
                                                                        {{$t('button.assignBrand')}}
                                                                    </b-button><!--/b-button-->
                                                                </div><!-- /.form-group -->
															</b-col><!--/b-col-->
                                                            <b-col lg="2" md="2" sm="12">
                                                                <div class="form-group">
                                                                    <label class="d-block"><pre> </pre></label>
                                                                    <b-button variant="warning" type="button" size="sm" class="btn-block" @click="resetClientBrand()">
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
																	<tr v-for="(ct, index) in client_brands"  :class="[{'table-primary': ct.token === client_brand.token}]">
																		<td>{{index + 1}}</td>
																		<td>{{ [_.find(dropdowns.brands, {id: ct.brand_id})].map((item) => item.label).join(', ') }}</td>
																		<td>
	                                                                    <span class="white-space-pre">
	                                                                        {{ getModelsLabel(ct) }}
	                                                                    </span><!-- /.while-space-pre -->
																		</td>
																		<td>
																			<a @click="handleEditClientBrandClick(ct.token)"
																			   :title="$t('button.title.editItem')"
																			   v-b-tooltip.hover>
																				<i class="fe fe-edit"></i>
																			</a>
																			<a-popconfirm title="Are you sure？" @confirm="handleDeleteClientBrandClick(ct.token)">
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
																	<tfoot v-show="client_brands.length <= 0">
																	<tr>
																		<th colspan="5">{{$t('title.noDataAvailable')}}</th>
																	</tr>
																	</tfoot>
																</table><!-- /.table table-bordered -->
															</b-col><!--/b-col-->
														</b-row><!--/b-row-->
													</div><!-- /.bg-light -->
												</b-collapse><!-- /#client-brands-->
											</b-card><!-- /b-card -->
										</b-col><!--/b-col-->

                                        <!--# Start client documents #-->
                                        <b-col lg="12" md="12" sm="12" class="mt-3 mb-5">
                                            <b-card class="mb-0">
                                                <b-card-header v-b-toggle.client-documents class="p-0">
                                                    <h4 class="mb-0">
														<span class="badge badge-primary">
															{{$t('title.clientAttachments')}}
														</span>
                                                        <small v-show="client_documents.length > 0">
                                                            - {{client_documents.length}} Item/s
                                                        </small>
                                                    </h4>
                                                </b-card-header><!-- /.p-0-->

                                                <b-collapse id="client-documents">
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
                                                                            v-model="client_document.title"
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
                                                                    <upload v-model="client_document.document"
                                                                            :disabled="client_document.document"
                                                                            :title="(client_document.document ? $t('msc.uploadedFile') : $t('msc.uploadFile'))"
                                                                            css-class="mt-0 btn-sm"></upload>
                                                                    <b-button :title="$t('msc.removeUpload')"
                                                                              variant="outline-primary"
                                                                              v-b-tooltip.hover
                                                                              class="ml-2 ml-2 btn-sm"
                                                                              @click="() => {client_document.document = null; client_document.document = null}"
                                                                              :disabled="!client_document.document"
                                                                              v-if="client_document.document">
                                                                        <i class="fa fa-close"></i>
                                                                    </b-button>
                                                                    <b-button :title="$t('button.download')"
                                                                              v-b-tooltip.hover
                                                                              variant="outline-primary"
                                                                              class="ml-2 ml-2 btn-sm"
                                                                              v-if="client_document.document && client_document.document.download_url"
                                                                              :disabled="!(client_document.document && client_document.document.download_url)"
                                                                              :href="(client_document.document ? client_document.document.download_url : '')"
                                                                              target="_blank">
                                                                        <i class="fa fa-cloud-download"></i>
                                                                    </b-button>
                                                                </b-form-group>
                                                                <div class="invalid-feedback d-block">{{formErrors.first('document')}}</div>
                                                            </b-col><!--/b-col-->
                                                            <b-col lg="2" md="2" sm="12">
                                                                <div class="form-group">
                                                                    <label class="d-block"><pre> </pre></label>
                                                                    <b-button variant="info" type="button" size="sm" class="btn-block" @click="handleAddUpdateClientDocumentClick()">
                                                                        <i class="fa fa-plus"></i>
                                                                    </b-button><!--/b-button-->
                                                                </div><!-- /.form-group -->
                                                            </b-col><!--/b-col-->
                                                            <b-col lg="2" md="2" sm="12">
                                                                <div class="form-group">
                                                                    <label class="d-block"><pre> </pre></label>
                                                                    <b-button variant="warning" type="button" size="sm" class="btn-block" @click="resetClientDocument()">
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
                                                                    <tr v-for="(ct, index) in client_documents" :class="[{'table-primary': ct.token === client_contact.token}]">
                                                                        <td>{{index + 1}}</td>
                                                                        <td>
                                                                            {{ct.title}}
                                                                        </td>
                                                                        <td>
                                                                            <a @click="handleEditClientDocumentClick(ct.token)"
                                                                               :title="$t('button.title.editItem')"
                                                                               v-b-tooltip.hover>
                                                                                <i class="fe fe-edit"></i>
                                                                            </a>
                                                                            <a :title="$t('button.download')" class=" ml-1"
                                                                               :href="ct.document.download_url"
                                                                               target="_blank" v-b-tooltip.hover>
                                                                                <i class="fa fa-cloud-download"></i>
                                                                            </a>
                                                                            <a-popconfirm title="Are you sure？" @confirm="handleDeleteClientDocumentClick(ct.token)">
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
                                                                    <tfoot v-show="client_documents.length <= 0">
                                                                    <tr>
                                                                        <th colspan="3">{{$t('title.noDataAvailable')}}</th>
                                                                    </tr>
                                                                    </tfoot>
                                                                </table><!-- /.table table-bordered -->
                                                            </b-col><!--/b-col-->
                                                        </b-row><!--/b-row-->
                                                    </div><!-- /.bg-light -->
                                                </b-collapse><!-- /#client-documents-->
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
				</div><!--/.clients-detail-operation-->
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

        <div class="clients-detail-operation" v-if="operation !== null && operation === 'detail'">
            <detail :id="listQueryParams.oToken" :handle-back="handleOperationClose"></detail>
        </div><!--/.clients-details-operation-->
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
    import ClientContactMixin from "./ClientContactMixin"
    import _ from 'lodash'
    import ClientBrandMixin from "./ClientBrandMixin";
    import QuickContactForm from "./../contacts/QuickContactForm";
    import QuickLocationForm from "./../locations/QuickLocationForm";
    import QuickLocationTypeForm from "./../locationTypes/QuickLocationTypeForm";
    import QuickBrandForm from "./../brands/QuickBrandForm";
    import QuickModelForm from "./../models/QuickModelForm";
    import ClientLocationMixin from "./ClientLocationMixin";
    import ClientDocumentMixin from "./ClientDocumentMixin";
    import Detail from "./detail";

    const FORM_STATE = {
        is_active: true,
        company_name: null,
        main_location_id: null,
        phone: null,
        email: null,
        fax: null,
        identification_number: null,
        comment: null,
        client_dealers: [],
        client_locations: [],
        client_contacts: [],
        client_brands: [],
        client_documents: [],
        _method: 'post',
    };

    const FILTER_STATE = {
        visible: false,
        from_date: null,
        to_date: null,
    };

    const COLUMN_DEFINITION = (self) => [
        {
            label: '#',
            key: 'id',
            sortable: true,
            sortKey: 'id',
        },
        {
            label: self.$t('column.companyName'),
            key: 'company_name',
            sortable: true,
            sortKey: 'company_name',
        },
        {
            label: self.$t('column.identificationNumber'),
            key: 'identification_number',
            sortable: true,
            sortKey: 'identification_number',
        },
        {
            label: self.$t('column.phone'),
            key: 'phone',
            sortable: true,
            sortKey: 'phone',
        },
        {
            label: self.$t('column.email'),
            key: 'email',
            sortable: true,
            sortKey: 'email',
        },
        {
            label: self.$t('column.fax'),
            key: 'fax',
            sortable: true,
            sortKey: 'fax',
        },
        (self.$global.hasAnyPermission(['clientsupdate', 'clientsdestroy'])
            ? {
                label: self.$t('column.action'),
                class: 'text-right',
                key: 'action',
                width: 150,
            } : {}),
    ];

    export default {
        mixins: [ListingMixin, ClientContactMixin, ClientBrandMixin, ClientLocationMixin, ClientDocumentMixin],
        components: {
            Datepicker,
            Treeselect,
            QuickContactForm,
            QuickLocationForm,
            QuickLocationTypeForm,
            QuickBrandForm,
            QuickModelForm,
            Detail
        },
        data() {
            return {
                operationTitle: 'title.clients',
                formFields: {...FORM_STATE},
                filters: {...FILTER_STATE},
                listUrl: 'clients',
                client_dealers: [],
                columns: COLUMN_DEFINITION(this),
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
                this.handleEditClick(this.$route.query.oToken);
            }
        },
        methods: {
            setOperation(operation = 'add', operationToken = null) {
                switch (operation) {
                    case "add":
                        this.operationTitle = 'title.addClient';
                        break;
                    case "edit":
                        this.operationTitle = 'title.editClient';
                        break;
                }

                this.resetClientContact();
                this.resetClientBrand();
                this.resetClientLocation();
                this.resetClientDocument();
                this.client_documents.length = 0;

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
                        url: this.formFields.id ? 'clients/update' : 'clients/create',
                        method: 'post',
                        data: {...this.formFields, client_contacts: this.client_contacts, client_brands: this.client_brands, client_locations: this.client_locations, client_documents: this.client_documents},
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
                        url: '/clients/delete',
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
                        url: `/clients/detail/${id}`,
                    })

                    const {data} = response
                    const {dealers, client_contacts, client_brands, client_locations, client_documents, main_location} = data
                    delete data.locations; delete data.dealers; delete data.client_contacts; delete data.client_brands; delete data.main_location; delete data.client_locations; delete data.client_documents;

                    this.formFields = {
                        ...this.formFields,
                        ...data,
                        main_location_id: (main_location ? main_location.id : null),
                        main_location_name: (main_location ? (main_location.street + ' ' + main_location.street_no + ' ' +main_location.city + ' ' + main_location.city + ' ' + main_location.country) : null),
                        client_dealers: dealers.map(item => item.id),
                        is_active: (data.is_active > 0)
                    }

                    this.client_dealers = dealers.map((item) => {
                        return {
                            id: item.id,
                            token: item.token,
                            name: item.name,
                        }
                    })

                    this.client_contacts = client_contacts.map((item) => {
                        return {
                            id: item.id,
                            token: item.token,
                            contact_id: item.contact.id,
                            locations: item.locations.map(entity => entity.id),
                        }
                    })

                    this.client_locations = client_locations.map((item) => {
                        return {
                            id: item.id,
                            token: item.token,
                            location_id: item.location.id,
                            location_type_id: (item.location_type ? item.location_type.id : null)
                        }
                    })

                    this.client_brands = client_brands.map((item) => {
                        return {
                            id: item.id,
                            token: item.token,
                            brand_id: item.brand.id,
                            models: item.models.map(entity => entity.id)
                        }
                    })

                    this.client_documents = client_documents.map((item) => {
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
                this.client_contact.contact_id = id;
            },
            handleAfterQuickContactUpdated(inputs) {
                const index = _.findIndex(this.dropdowns.contacts, {id: inputs.id})
                this.$set(this.dropdowns.contacts[index], 'label', inputs.name);
            },
            handleAfterQuickBrandCreated(inputs) {
                const {id, title} = inputs
                this.dropdowns.brands.push({id: id, label: title})
                this.client_brand.brand_id = id;
            },
            handleAfterQuickBrandUpdated(inputs) {
                const index = _.findIndex(this.dropdowns.brands, {id: inputs.id})
                this.$set(this.dropdowns.brands[index], 'label', inputs.title)
            },
            handleAfterQuickModelCreated(inputs) {
                const models = [...this.dropdowns.models, {id: inputs.id, label: inputs.title, is_active: inputs.is_active, brand_id: this.client_brand.brand_id}];
                this.$set(this.dropdowns, 'models', models)
                this.client_brand.models.push(inputs.id)
            },
            handleAfterQuickMainLocationCreated(inputs) {
                this.dropdowns.locations.push({id: inputs.id, label: `${inputs.street} ${inputs.street_no} ${inputs.city} ${inputs.zip} ${inputs.country}`})
                this.formFields.main_location_id = inputs.id;
            },
            handleAfterQuickMainLocationUpdated(inputs) {
                const index = _.findIndex(this.dropdowns.locations, {id: inputs.id})
                this.$set(this.dropdowns.locations[index], 'label', `${inputs.street} ${inputs.street_no} ${inputs.city} ${inputs.zip} ${inputs.country}`);
            },
            handleAfterQuickAdditionalLocationCreated(inputs) {
                this.dropdowns.locations.push({id: inputs.id, label: `${inputs.street} ${inputs.street_no} ${inputs.city} ${inputs.zip} ${inputs.country}`});
                this.formFields.client_locations.push(inputs.id);
            },
            handleAfterQuickClientLocationCreated(inputs) {
                this.dropdowns.locations.push({id: inputs.id, label: `${inputs.street} ${inputs.street_no} ${inputs.city} ${inputs.zip} ${inputs.country}`});
                this.client_location.location_id = inputs.id;
            },
            handleAfterQuickClientLocationUpdated(inputs) {
                const index = _.findIndex(this.dropdowns.locations, {id: inputs.id})
                this.$set(this.dropdowns.locations[index], 'label', `${inputs.street} ${inputs.street_no} ${inputs.city} ${inputs.zip} ${inputs.country}`);
            },
            handleAfterQuickClientLocationTypeCreated(inputs) {
                this.dropdowns.location_types.push({id: inputs.id, label: inputs.title})
                this.client_location.location_type_id = inputs.id;
            },
            handleAfterQuickClientLocationTypeUpdated(inputs) {
                const index = _.findIndex(this.dropdowns.location_types, {id: inputs.id})
                this.$set(this.dropdowns.location_types[index], 'label', inputs.title);
            },
            hasListAccess() {
                return this.$global.hasPermission('clientsview')
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
                this.formFields = {...FORM_STATE};
                this.client_contacts.length = 0;
                this.client_locations.length = 0;
                this.client_brands.length = 0;
                this.client_dealers.length = 0;
            },
            getDealersLabel(dealers) {
                return dealers.map(item => {
                    return {
                        id: item.id,
                        label: item.name
                    }
                })
                    .map((item, index) => `${index+1}. ${item.label}`)
                    .join("\r\n")
            },
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

                        _.map(this.client_contacts, (contacts) => {
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

                    _.map(this.client_contacts, (contacts) => {
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


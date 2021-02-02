<template>
	<div v-if="show">
		<div class="card">
			<div class="card-header card-header-flex pb-2">
				<div class="w-100 mt-2">
					<div class="row">
						<div class="col-8">
							<h5 class="mt-3 ml-0 mr-3 mb-2">
								<strong>
									<template v-if="operation === null">{{$t('title.suppliers')}}</template>
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
											          v-if="$global.hasPermission('suppliersstore')" v-b-tooltip.hover>
												<i class="fe fe-plus"></i> {{$t('button.addNew')}}
											</b-button>
											<b-button size="sm" :title="$t('button.title.filterRecords')"
											          variant="outline-info"
											          @click="filters.visible = !filters.visible" v-b-tooltip.hover
											          v-if="$global.hasPermission('suppliersview')">
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
				<div class="suppliers-table">
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
						<template v-slot:cell(supplier_user_types)="record">
							{{record.item.supplier_user_types.map(item => item.title).join(', ')}}
						</template>
						<template v-slot:cell(supplier_logistic_types)="record">
                            {{record.item.supplier_logistic_types.map(item => item.title).join(', ')}}
						</template>
						<template v-slot:cell(action)="record">
							<a @click="setOperation('edit', record.item.id)"
							   :title="$t('button.title.editItem')" v-if="$global.hasPermission('suppliersupdate')"
							   v-b-tooltip.hover>
								<i class="fe fe-edit"></i>
							</a>
							<a-popconfirm title="Are you sure？" @confirm="handleDeleteClick(record.item.id)"
							              v-if="$global.hasPermission('suppliersdestroy')">
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
				</div><!-- /.suppliers-table -->
				<div class="suppliers-operation">
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
													:label="$t('input.name')+' *'"
													label-for="name"
													:invalid-feedback="formErrors.first('name')"
											>
												<b-form-input
													id="name"
													v-model="formFields.name"
													type="text"
													:state="((formErrors.has('name') ? false : null))"
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
														pattern="^[0-9-+()]*"
														title="+(XXX) XXX"
														type="number"
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
										<b-col lg="3" md="3" sm="12">
											<b-form-group
													:label="$t('input.fax')+ ' *'"
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

										<b-col lg="9" md="9" sm="12">
                                            <div class="form-group">
                                                <div class="clearfix">
													<span class="pull-left">
														<label for="mainLocation" class="d-block">{{$t('input.mainLocation')}} *</label>
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
										<b-col lg="4" md="4" sm="12">
											<div class="form-group">
												<label class="d-block">{{$t('input.supplierTypes')}} </label>
												<treeselect
													:multiple="true"
													:options="dropdowns.supplier_user_types"
													placeholder=""
													v-model="formFields.supplier_user_types"
													:class="[{'invalid is-invalid': (formErrors.has('supplier_user_types'))}]"
												/>
												<div class="invalid-feedback">{{formErrors.first('supplier_user_types')}}</div>
											</div><!-- /.form-group -->
										</b-col><!--/b-col-->
										<b-col lg="4" md="4" sm="12" v-show="_.includes(formFields.supplier_user_types, 1)">
											<div class="form-group">
												<div class="clearfix">
													<span class="pull-left">
														<label class="d-block">{{$t('input.logisticTypes')}} *</label>
													</span><!-- /.pull-left -->
													<span class="pull-right">

													</span><!-- /.pull-right -->
												</div><!-- /.clearfix -->
												<treeselect
													:multiple="true"
													:options="dropdowns.logistic_types"
													placeholder=""
													v-model="formFields.supplier_logistic_types"
													:class="[{'invalid is-invalid': (formErrors.has('supplier_logistic_types'))}]"
												/>
												<div class="invalid-feedback">{{formErrors.first('supplier_logistic_types')}}</div>
											</div><!-- /.form-group -->
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
										<!--# Start supplier additional location #-->
										<b-col lg="12" md="12" sm="12">
											<b-card class="mb-0">
												<b-card-header v-b-toggle.supplier-location class="p-0">
													<h4 class="mb-0">
														<span class="badge badge-primary">
															{{$t('title.supplierAdditionalLocations')}}
														</span>
														<small v-show="supplier_locations.length > 0">
															- {{supplier_locations.length}} Item/s
														</small>
													</h4>
												</b-card-header><!-- /.p-0-->

												<b-collapse id="supplier-location">
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
                                                                            :id="supplier_location.location_id"
                                                                            :after-create="handleAfterQuickSupplierLocationCreated"
                                                                            :after-update="handleAfterQuickSupplierLocationUpdated">
																		</quick-location-form>
																	</span><!-- /.pull-right -->
																	</div><!-- /.clearfix -->
																	<treeselect
																		:multiple="false"
																		:options="dropdowns.locations"
																		placeholder=""
																		v-model="supplier_location.location_id"
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
																					:id="supplier_location.location_type_id"
																					:after-create="handleAfterQuickSupplierLocationTypeCreated"
																					:after-update="handleAfterQuickSupplierLocationTypeUpdated">
																			</quick-location-type-form>
																		</span><!-- /.pull-right -->
																	</div><!-- /.clearfix -->
																	<treeselect
																			:multiple="false"
																			:options="dropdowns.location_types"
																			placeholder=""
																			v-model="supplier_location.location_type_id"
																			:class="[{'invalid is-invalid': (formErrors.has('location_type_id'))}]"
																	/>
																	<div class="invalid-feedback">{{formErrors.first('location_type_id')}}</div>
																</div><!-- /.form-group -->
															</b-col><!--/b-col-->
															<b-col lg="2" md="2" sm="12">
                                                                <div class="form-group">
                                                                    <label class="d-block"><pre> </pre></label>
                                                                    <b-button variant="info" type="button" size="sm" class="btn-block" @click="handleAddUpdateSupplierLocationClick()">
                                                                        {{$t('button.assignLocation')}}
                                                                    </b-button><!--/b-button-->
                                                                </div><!-- /.form-group -->
															</b-col><!--/b-col-->
                                                            <b-col lg="2" md="2" sm="12">
                                                                <div class="form-group">
                                                                    <label class="d-block"><pre> </pre></label>
                                                                    <b-button variant="warning" type="button" size="sm" class="btn-block" @click="resetSupplierLocation()">
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
																		<tr v-for="(cl, index) in supplier_locations" :class="[{'table-primary': cl.token === supplier_location.token}]">
																			<td>{{index + 1}}</td>
																			<td>{{ [_.find(dropdowns.locations, {id: cl.location_id})].map((item) => item.label).join(', ') }}</td>
																			<td>{{ [_.find(dropdowns.location_types, {id: cl.location_type_id})].map((item) => item.label).join(', ') }}</td>
																			<td>
																				<a @click="handleEditSupplierLocationClick(cl.token)"
																				   v-if="(!_.includes(supplier_contacts.map(({locations}) => locations.map(l => l)).reduce((a, b) => a.concat(b), []), cl.location_id)) || (cl.location_id === formFields.main_location_id)"
																				   :title="$t('button.title.editItem')"
																				   v-b-tooltip.hover>
																					<i class="fe fe-edit"></i>
																				</a>
																				<a-popconfirm title="Are you sure？" @confirm="handleDeleteSupplierLocationClick(cl.token)">
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
																	<tfoot v-show="supplier_locations.length <= 0">
																		<tr>
																			<th colspan="5">{{$t('title.noDataAvailable')}}</th>
																		</tr>
																	</tfoot>
																</table><!-- /.table table-bordered -->
															</b-col><!--/b-col-->
														</b-row><!--/b-row-->
													</div><!-- /.bg-light -->
												</b-collapse><!-- /#supplier-location -->
											</b-card><!-- /b-card -->
										</b-col><!--/b-col-->

										<!--# Start supplier contacts #-->
										<b-col lg="12" md="12" sm="12" class="mt-3">
											<b-card class="mb-0">
												<b-card-header v-b-toggle.supplier-contacts class="p-0">
													<h4 class="mb-0">
														<span class="badge badge-primary">
															{{$t('title.supplierContacts')}}
														</span>
														<small v-show="supplier_contacts.length > 0">
															- {{supplier_contacts.length}} Item/s
														</small>
													</h4>
												</b-card-header><!-- /.p-0-->

												<b-collapse id="supplier-contacts">
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
																					:id="supplier_contact.contact_id"
																					:after-create="handleAfterQuickContactCreated"
																					:after-update="handleAfterQuickContactUpdated">
																			</quick-contact-form>
																		</span><!-- /.pull-right -->
																	</div><!-- /.clearfix -->
																	<treeselect
																		:multiple="false"
																		:options="dropdowns.contacts"
																		placeholder=""
																		v-model="supplier_contact.contact_id"
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
																		:options="dropdowns.locations.filter(item => (_.includes(supplier_locations.map(i => i.location_id), item.id) || formFields.main_location_id === item.id ))"
																		placeholder=""
																		v-model="supplier_contact.locations"
																		:class="[{'invalid is-invalid': (formErrors.has('locations'))}]"
																	/>
																	<div class="invalid-feedback">{{formErrors.first('locations')}}</div>
																</div><!-- /.form-group -->
															</b-col><!--/b-col-->
                                                            <b-col lg="2" md="2" sm="12">
                                                                <div class="form-group">
                                                                    <label class="d-block"><pre> </pre></label>
                                                                    <b-button variant="info" type="button" size="sm" class="btn-block" @click="handleAddUpdateSupplierContactClick()">
                                                                        {{$t('button.assignContact')}}
                                                                    </b-button><!--/b-button-->
                                                                </div><!-- /.form-group -->
                                                            </b-col><!--/b-col-->
                                                            <b-col lg="2" md="2" sm="12">
                                                                <div class="form-group">
                                                                    <label class="d-block"><pre> </pre></label>
                                                                    <b-button variant="warning" type="button" size="sm" class="btn-block" @click="resetSupplierContact()">
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
																		<tr v-for="(ct, index) in supplier_contacts" :class="[{'table-primary': ct.token === supplier_contact.token}]">
																			<td>{{index + 1}}</td>
																			<td>
																				<div><strong>{{$t('column.contact')}}:</strong> {{ [_.find(dropdowns.contacts, {id: ct.contact_id})].map((item) => item.label).join(', ') }}</div>
																				<div><strong>{{$t('column.function')}}:</strong> {{ [_.find(dropdowns.contacts, {id: ct.contact_id})].map((item) => item.functions).join(', ') }}</div>
																				<div><strong>{{$t('column.phone')}}:</strong> {{ [_.find(dropdowns.contacts, {id: ct.contact_id})].map((item) => item.phone).join(', ') }}</div>
																				<div><strong>{{$t('column.email')}}:</strong> {{ [_.find(dropdowns.contacts, {id: ct.contact_id})].map((item) => item.email).join(', ') }}</div>
																			</td>
																			<td>
																			<span class="white-space-pre">
																				{{ getLocationsLabel(ct, formFields.supplier_locations[0]) }}
																			</span><!-- /.while-space-pre -->
																			</td>
																			<td>
																				<a @click="handleEditSupplierContactClick(ct.token)"
																				   :title="$t('button.title.editItem')"
																				   v-b-tooltip.hover>
																					<i class="fe fe-edit"></i>
																				</a>
																				<a-popconfirm title="Are you sure？" @confirm="handleDeleteSupplierContactClick(ct.token)">
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
																	<tfoot v-show="supplier_contacts.length <= 0">
																		<tr>
																			<th colspan="5">{{$t('title.noDataAvailable')}}</th>
																		</tr>
																	</tfoot>
																</table><!-- /.table table-bordered -->
															</b-col><!--/b-col-->
														</b-row><!--/b-row-->
													</div><!-- /.bg-light -->
												</b-collapse><!-- /#supplier-contacts-->
											</b-card><!-- /b-card -->
										</b-col><!--/b-col-->

                                        <!--# Start supplier documents #-->
                                        <b-col lg="12" md="12" sm="12" class="mt-3">
                                            <b-card class="mb-0">
                                                <b-card-header v-b-toggle.supplier-documents class="p-0">
                                                    <h4 class="mb-0">
														<span class="badge badge-primary">
															{{$t('title.supplierAttachments')}}
														</span>
                                                        <small v-show="supplier_documents.length > 0">
                                                            - {{supplier_documents.length}} Item/s
                                                        </small>
                                                    </h4>
                                                </b-card-header><!-- /.p-0-->

                                                <b-collapse id="supplier-documents">
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
                                                                            v-model="supplier_document.title"
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
                                                                    <upload v-model="supplier_document.document"
                                                                            :disabled="supplier_document.document"
                                                                            :title="(supplier_document.document ? $t('msc.uploadedFile') : $t('msc.uploadFile'))"
                                                                            css-class="mt-0 btn-sm"></upload>
                                                                    <b-button :title="$t('msc.removeUpload')"
                                                                              variant="outline-primary"
                                                                              v-b-tooltip.hover
                                                                              class="ml-2 ml-2 btn-sm"
                                                                              @click="() => {supplier_document.document = null; supplier_document.document = null}"
                                                                              :disabled="!supplier_document.document"
                                                                              v-if="supplier_document.document">
                                                                        <i class="fa fa-close"></i>
                                                                    </b-button>
                                                                    <b-button :title="$t('button.download')"
                                                                              v-b-tooltip.hover
                                                                              variant="outline-primary"
                                                                              class="ml-2 ml-2 btn-sm"
                                                                              v-if="supplier_document.document && supplier_document.document.download_url"
                                                                              :disabled="!(supplier_document.document && supplier_document.document.download_url)"
                                                                              :href="(supplier_document.document ? supplier_document.document.download_url : '')"
                                                                              target="_blank">
                                                                        <i class="fa fa-cloud-download"></i>
                                                                    </b-button>
                                                                </b-form-group>
                                                                <div class="invalid-feedback d-block">{{formErrors.first('document')}}</div>
                                                            </b-col><!--/b-col-->
                                                            <b-col lg="2" md="2" sm="12">
                                                                <div class="form-group">
                                                                    <label class="d-block"><pre> </pre></label>
                                                                    <b-button variant="info" type="button" size="sm" class="btn-block" @click="handleAddUpdateSupplierDocumentClick()">
                                                                        <i class="fa fa-plus"></i>
                                                                    </b-button><!--/b-button-->
                                                                </div><!-- /.form-group -->
                                                            </b-col><!--/b-col-->
                                                            <b-col lg="2" md="2" sm="12">
                                                                <div class="form-group">
                                                                    <label class="d-block"><pre> </pre></label>
                                                                    <b-button variant="warning" type="button" size="sm" class="btn-block" @click="resetSupplierDocument()">
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
                                                                        <tr v-for="(ct, index) in supplier_documents" :class="[{'table-primary': ct.token === supplier_contact.token}]">
                                                                            <td>{{index + 1}}</td>
                                                                            <td>
                                                                                {{ct.title}}
                                                                            </td>
                                                                            <td>
                                                                                <a @click="handleEditSupplierDocumentClick(ct.token)"
                                                                                   :title="$t('button.title.editItem')"
                                                                                   v-b-tooltip.hover>
                                                                                    <i class="fe fe-edit"></i>
                                                                                </a>
                                                                                <a :title="$t('button.download')" class=" ml-1"
                                                                                      :href="ct.document.download_url"
                                                                                      target="_blank" v-b-tooltip.hover>
                                                                                    <i class="fa fa-cloud-download"></i>
                                                                                </a>
                                                                                <a-popconfirm title="Are you sure？" @confirm="handleDeleteSupplierDocumentClick(ct.token)">
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
                                                                    <tfoot v-show="supplier_documents.length <= 0">
                                                                        <tr>
                                                                            <th colspan="5">{{$t('title.noDataAvailable')}}</th>
                                                                        </tr>
                                                                    </tfoot>
                                                                </table><!-- /.table table-bordered -->
                                                            </b-col><!--/b-col-->
                                                        </b-row><!--/b-row-->
                                                    </div><!-- /.bg-light -->
                                                </b-collapse><!-- /#supplier-documents-->
                                            </b-card><!-- /b-card -->
                                        </b-col><!--/b-col-->

                                        <!--# Start supplier contracts #-->
                                        <b-col lg="12" md="12" sm="12" class="mt-3 mb-5">
                                            <b-card class="mb-0">
                                                <b-card-header v-b-toggle.supplier-contracts class="p-0">
                                                    <h4 class="mb-0">
														<span class="badge badge-primary">
															{{$t('title.supplierContracts')}}
														</span>
                                                        <small v-show="formFields.supplier_contracts.length > 0">
                                                            - {{formFields.supplier_contracts.length}} Item/s
                                                        </small>
                                                    </h4>
                                                </b-card-header><!-- /.p-0-->

                                                <b-collapse id="supplier-contracts">
                                                    <div class="bg-light p-3">
                                                        <b-row>
                                                            <b-col cols="12">
                                                                <table class="table table-bordered bg-white">
                                                                    <thead>
                                                                    <tr>
                                                                        <th width="50">#</th>
                                                                        <th width="180">{{$t('column.title')}}</th>
                                                                        <th width="50">{{$t('column.duration')}}</th>
                                                                        <th width="100">{{$t('column.start_date')}}</th>
                                                                        <th width="100">{{$t('column.end_date')}}</th>
                                                                        <th width="180">{{$t('column.description')}}</th>
                                                                    </tr>
                                                                    </thead>
                                                                    <tbody>
                                                                    <tr v-for="(ct, index) in formFields.supplier_contracts" :class="[{'table-primary': ct.token === supplier_contact.token}]">
                                                                        <td>{{index + 1}}</td>
                                                                        <td>{{ct.title}}</td>
                                                                        <td>{{ct.duration}}</td>
                                                                        <td>{{ct.start_date}}</td>
                                                                        <td>{{ct.end_date}}</td>
                                                                        <td>{{ct.description}}</td>
                                                                    </tr>
                                                                    </tbody>
                                                                    <tfoot v-show="formFields.supplier_contracts.length <= 0">
                                                                    <tr>
                                                                        <th colspan="6">{{$t('title.noDataAvailable')}}</th>
                                                                    </tr>
                                                                    </tfoot>
                                                                </table><!-- /.table table-bordered -->
                                                            </b-col><!--/b-col-->
                                                        </b-row><!--/b-row-->
                                                    </div><!-- /.bg-light -->
                                                </b-collapse><!-- /#supplier-contracts-->
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
				</div><!--/.suppliers-operation-->
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
    import SupplierContactMixin from "./SupplierContactMixin"
    import _ from 'lodash'
    import QuickContactForm from "./../contacts/QuickContactForm"
    import QuickLocationForm from "./../locations/QuickLocationForm"
    import QuickLocationTypeForm from "./../locationTypes/QuickLocationTypeForm"
    import SupplierLocationMixin from "./SupplierLocationMixin"
    import SupplierDocumentMixin from "./SupplierDocumentMixin"

    const FORM_STATE = {
		is_active: true,
		name: null,
		main_location_id: null,
		phone: null,
		email: null,
		password: null,
		fax: null,
        identification_number: null,
		comment: null,
		supplier_user_types: [],
		supplier_locations: [],
		supplier_contacts: [],
		supplier_contracts: [],
		supplier_documents: [],
		supplier_logistic_types: [],
		_method: 'post',
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
            label: self.$t('column.name'),
            key: 'name',
            sortable: true,
            sortKey: 'name',
        },
        {
            label: self.$t('column.supplierTypes'),
            key: 'supplier_user_types',
            sortable: false,
            sortKey: 'supplier_user_types',
        },
        {
            label: self.$t('column.logisticTypes'),
            key: 'supplier_logistic_types',
            sortable: false,
            sortKey: 'supplier_logistic_types',
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
        (self.$global.hasAnyPermission(['suppliersupdate', 'suppliersdestroy'])
            ? {
                label: self.$t('column.action'),
                class: 'text-right',
                key: 'action',
                width: 150,
            } : {}),
    ];

    export default {
        mixins: [ListingMixin, SupplierLocationMixin, SupplierContactMixin, SupplierDocumentMixin],
        components: {
            Datepicker,
            Treeselect,
            QuickContactForm,
            QuickLocationForm,
            QuickLocationTypeForm,
        },
        data() {
            return {
                operationTitle: 'title.suppliers',
                formFields: {...FORM_STATE},
                filters: {...FILTER_STATE},
                listUrl: 'suppliers',
                columns: COLUMN_DEFINATION(this),
                dropdowns: {
                    locations: [],
                    location_types: [],
                    supplier_user_types: [],
                    logistic_types: [],
                    contacts: [],
                },
                show: true,
            }
        },
        mounted() {
            this.getLocations();
            this.getLocationTypes();
            this.getLogisticTypes();
            this.getContacts();
            this.getSupplierTypes();

            if (this.$route.query && this.$route.query.operation && this.$route.query.oToken) {
                this.handleEditClick(this.$route.query.oToken)
            }
        },
        methods: {
            setOperation(operation = 'add', operationToken = null) {
                this.operationTitle = (operation === 'add' ? 'title.addSupplier' : 'title.editSupplier')
                this.resetSupplierContact()
                this.resetSupplierLocation()
                this.resetSupplierDocument()
                this.supplier_locations.length = 0
                this.supplier_contacts.length = 0
                this.supplier_documents.length = 0

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
                        url: this.formFields.id ? 'suppliers/update' : 'suppliers/create',
                        method: 'post',
                        data: {...this.formFields, supplier_contacts: this.supplier_contacts, supplier_locations: this.supplier_locations, supplier_documents: this.supplier_documents},
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
                        url: '/suppliers/delete',
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
                        url: `/suppliers/detail/${id}`,
                    })
                    this.operationTitle = this.$t('title.editSupplier')
                    const {data} = response
                    const {supplier_contacts, supplier_locations, supplier_documents, main_location} = data;
                    delete data.supplier_contacts; delete data.main_location; delete data.supplier_locations; delete data.supplier_documents;

                    this.formFields = {
                        ...this.formFields,
                        ...data,
                        main_location_id: (main_location ? main_location.id : null),
                        is_active: (data.is_active > 0),
                        supplier_logistic_types: data.supplier_logistic_types.map(item => item.id),
                        supplier_user_types: data.supplier_user_types.map(item => item.id),
                        supplier_contracts: data.supplier_contracts.map((item) => {
                            return {
                                id: item.id,
                                title: item.title,
                                duration: item.duration,
                                description: item.description,
                                start_date: item.start_date,
                                end_date: item.end_date
                            }
                        })
                    }

                    this.supplier_contacts = supplier_contacts.map((item) => {
                        return {
                            id: item.id,
                            token: item.token,
                            contact_id: item.contact.id,
                            locations: item.locations.map(entity => entity.id),
                        }
                    })

                    this.supplier_locations = supplier_locations.map((item) => {
                        return {
                            id: item.id,
                            token: item.token,
                            location_id: item.location.id,
                            location_type_id: (item.location_type ? item.location_type.id : null)
                        }
                    })

                    this.supplier_documents = supplier_documents.map((item) => {
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
            async getSupplierTypes() {
                try {
                    const response = await request({
                        url: '/dropdowns/supplier/types',
                        method: "post"
                    })

                    const {data} = response
                    this.dropdowns.supplier_user_types = data

                } catch (e) {
                    this.dropdowns.supplier_user_types = []
                }
            },
            handleAfterQuickContactCreated(inputs) {
                const {id, name} = inputs
                this.dropdowns.contacts.push({id: id, label: name})
                this.supplier_contact.contact_id = id;
            },
            handleAfterQuickContactUpdated(inputs) {
                const index = _.findIndex(this.dropdowns.contacts, {id: inputs.id})
                this.$set(this.dropdowns.contacts[index], 'label', inputs.name);
            },
            handleAfterQuickBrandCreated(inputs) {
                const {id, title} = inputs
                this.dropdowns.brands.push({id: id, label: title})
                this.supplier_brand.brand_id = id;
            },
            handleAfterQuickBrandUpdated(inputs) {
                const index = _.findIndex(this.dropdowns.brands, {id: inputs.id})
                this.$set(this.dropdowns.brands[index], 'label', inputs.title)
            },
            handleAfterQuickModelCreated(inputs) {
                const models = [...this.dropdowns.models, {id: inputs.id, label: inputs.title, is_active: inputs.is_active, brand_id: this.supplier_brand.brand_id}];
                this.$set(this.dropdowns, 'models', models)
                this.supplier_brand.models.push(inputs.id)
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
                this.dropdowns.locations.push({id: inputs.id, label: `${inputs.street} ${inputs.street_no} ${inputs.city} ${inputs.zip} ${inputs.country}`})
                this.formFields.supplier_locations.push(inputs.id);
            },
            handleAfterQuickSupplierLocationCreated(inputs) {
                this.dropdowns.locations.push({id: inputs.id, label: title})
                this.supplier_location.location_id = inputs.id;
            },
            handleAfterQuickSupplierLocationUpdated(inputs) {
                const index = _.findIndex(this.dropdowns.locations, {id: inputs.id})
                this.$set(this.dropdowns.locations[index], 'label', `${inputs.street} ${inputs.street_no} ${inputs.city} ${inputs.zip} ${inputs.country}`);
            },
            handleAfterQuickSupplierLocationTypeCreated(inputs) {
                this.dropdowns.location_types.push({id: inputs.id, label: inputs.title})
                this.supplier_location.location_type_id = inputs.id;
            },
            handleAfterQuickSupplierLocationTypeUpdated(inputs) {
                const index = _.findIndex(this.dropdowns.location_types, {id: inputs.id})
                this.$set(this.dropdowns.location_types[index], 'label', inputs.title);
            },
            hasListAccess() {
                return this.$global.hasPermission('suppliersview')
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
                this.supplier_locations.length = 0;
                this.supplier_contacts.length = 0
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

                        _.map(this.supplier_contacts, (contacts) => {
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

                    _.map(this.supplier_contacts, (contacts) => {
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

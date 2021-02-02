<template>
    <div v-if="show">
        <div class="card">
            <div class="card-header card-header-flex pb-2">
                <div class="w-100 mt-2">
                    <div class="row">
                        <div class="col-8">
                            <h5 class="mt-3 ml-0 mr-3 mb-2">
                                <strong>
                                    <template>{{$t('title.detailClient')}}</template>
                                </strong>
                            </h5>
                        </div>
                        <div class="col-4 text-right">
                            <b-button variant="warning" size="sm" class="mt-3"
                                      @click="handleBack()"
                                      v-b-tooltip.hover :title="$t('button.cancel')">
                                <i class="fa fa-arrow-left"></i> {{$t('button.cancel')}}
                            </b-button>
                        </div>
                    </div><!-- /.row -->
                </div><!-- /.w-100 -->
            </div><!-- /.card-header -->
            <div class="card-body" v-if="entity">
                <b-row>
                    <b-col lg="4" md="4" sm="12">
                        <table class="table table-striped">
                            <tbody>
                                <tr>
                                    <th>{{$t('input.companyName')}}</th>
                                    <td>{{entity.company_name}} | {{entity.identification_number}}</td>
                                </tr>
                                <tr>
                                    <th>{{$t('input.email')}}</th>
                                    <td>{{entity.email}}</td>
                                </tr>
                                <tr>
                                    <th>{{$t('input.phone')}}</th>
                                    <td>{{entity.phone}}</td>
                                </tr>
                            </tbody>
                        </table>
                    </b-col><!-- /.col -->
                    <b-col lg="4" md="4" sm="12">
                        <table class="table table-striped">
                            <tbody>
                                <tr>
                                    <th>{{$t('input.mainLocation')}}</th>
                                    <td>
                                        {{entity.main_location.street}},
                                        {{entity.main_location.street_no}},
                                        {{entity.main_location.zip}},
                                        {{entity.main_location.city}},
                                        {{entity.main_location.country}}
                                    </td>
                                </tr>
                                <tr>
                                    <th>{{$t('input.dealers')}}</th>
                                    <td>{{entity.dealers.map((item) => item.name).join(', ') }}</td>
                                </tr>
                                <tr>
                                    <th>{{$t('input.comment')}}</th>
                                    <td>
                                        {{entity.comment}},
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </b-col><!-- /.col -->
                    <b-col lg="4" md="4" sm="12">
                        <span class="font-size-18 font-weight-bold text-uppercase mb-2">{{$t('title.clientAttachments')}}</span>
                        <table class="table table-striped table-sm">
                            <tbody>
                                <tr v-for="(cd, index) in entity.client_documents">
                                    <td width="50">
                                        <b-button :title="$t('button.download')"
                                                  v-b-tooltip.hover
                                                  variant="outline-primary"
                                                  class="ml-2 ml-2 btn-sm"
                                                  v-if="cd.document && cd.document.download_url"
                                                  :href="cd.document.download_url"
                                                  target="_blank">
                                            <i class="fa fa-cloud-download"></i>
                                        </b-button>
                                    </td>
                                    <th>{{cd.title}}</th>
                                </tr>
                            </tbody>
                        </table>
                    </b-col><!-- /.col -->
                </b-row><!-- /.row -->
                <b-row>
                    <b-col sm="12"> <hr> </b-col>
                    <b-col lg="6" sm="12">
                        <span class="font-size-18 font-weight-bold text-uppercase mb-4">
                            {{$t('title.clientAdditionalLocations')}}
                        </span>
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>{{$t('input.location')}}</th>
                                    <th>{{$t('input.locationType')}}</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="(cl, index) in entity.client_locations">
                                    <td>{{index + 1}}</td>
                                    <td>
                                        {{cl.location.street}},
                                        {{cl.location.street_no}},
                                        {{cl.location.zip}},
                                        {{cl.location.city}},
                                        {{cl.location.country}}
                                    </td>
                                    <td>{{cl.location_type.title}}</td>
                                </tr>
                            </tbody>
                        </table>
                    </b-col><!-- /.col -->
                    <b-col lg="6" sm="12">
                        <span class="font-size-18 font-weight-bold text-uppercase mb-4">
                            {{$t('title.clientContacts')}}
                        </span>
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>{{$t('input.contact')}}</th>
                                    <th>{{$t('input.locations')}}</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="(cc, index) in entity.client_contacts">
                                    <td>{{index + 1}}</td>
                                    <td>
                                        <div>{{cc.contact.name}}</div>
                                        <div>{{cc.contact.email}}</div>
                                        <div>{{cc.contact.mobile}}</div>
                                        <div>{{cc.contact.functions}}</div>
                                    </td>
                                    <td>
                                        <div v-for="(cl, i) in cc.locations">
                                            {{i + 1}}. {{cl.street}}, {{cl.street_no}}, {{cl.zip}}, {{cl.city}}, {{cl.country}}
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </b-col><!-- /.col -->
                    <b-col lg="6" sm="12">
                        <span class="font-size-18 font-weight-bold text-uppercase mb-4">
                            {{$t('title.clientBrands')}}
                        </span>
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>{{$t('input.brand')}}</th>
                                    <th>{{$t('input.models')}}</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="(cb, index) in entity.client_brands">
                                    <td>{{index + 1}}</td>
                                    <td>{{cb.brand.title}}</td>
                                    <td>{{cb.models.map(item => item.title).join(', ')}}</td>
                                </tr>
                            </tbody>
                        </table>
                    </b-col><!-- /.col -->
                </b-row><!-- /.row -->
            </div><!-- /.card-body-->
        </div><!-- /.card -->
    </div>
</template>
<script>
    import Error from '../../util/Error'
    import {request} from '../../util/Request'
    import {handleServerError} from '../../util/Utils'

    export default {
        props: ['id', 'handleBack'],
        data() {
            return {
                entity: null,
                show: true
            }
        },
        mounted() {
            this.getDetails()
        },
        methods: {
            async getDetails() {
                try {
                    const response = await request({
                        method: 'get',
                        url: `/clients/detail/${this.id}`,
                    })

                    const {data} = response
                    this.entity = data;
                } catch (error) {
                    handleServerError(error, this.$notification)
                }
            }
        }
    }
</script>

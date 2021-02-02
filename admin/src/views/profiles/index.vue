<template>
    <div>
        <div class="card">
            <div class="card-header card-header-flex pb-2">
                <div class="w-100">
                    <div class="row">
                        <div class="col-6">
                            <h5 class="mt-3 ml-0 mr-3 mb-2 text-uppercase">
                                <span>{{ $t('title.userProfile') }}</span>
                            </h5>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <div class="profile-operation">
                    <b-container fluid>
                        <form @submit.prevent="handleSubmit" autocomplete="off">
                            <b-row class="p-0">
                                <b-col cols="12" md="6" lg="6" sm="12" class="p-0">
                                    <b-row>
                                        <b-col sm="6">
                                            <b-form-group
                                                :label="$t('input.firstName')+' *'"
                                                label-for="first_name"
                                                :invalid-feedback="formErrors.first('first_name')"
                                            >
                                                <b-form-input
                                                    id="first_name"
                                                    v-model="formFields.first_name"
                                                    type="text"
                                                    :state="((formErrors.has('first_name') ? false : null))"
                                                    ref="first_name"
                                                    @focus="$event.target.select()"
                                                ></b-form-input>
                                            </b-form-group>
                                        </b-col><!--/b-col-->
                                        <b-col sm="6">
                                            <b-form-group
                                                :label="$t('input.lastName')+' *'"
                                                label-for="last_name"
                                                :invalid-feedback="formErrors.first('last_name')"
                                            >
                                                <b-form-input
                                                    id="last_name"
                                                    v-model="formFields.last_name"
                                                    type="text"
                                                    :state="((formErrors.has('last_name') ? false : null))"
                                                    ref="last_name"
                                                    @focus="$event.target.select()"
                                                ></b-form-input>
                                            </b-form-group>
                                        </b-col><!--/b-col-->
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
                                                label-for="password_confirmation">
                                                <b-form-input
                                                    type="password"
                                                    id="password_confirmation"
                                                    v-model="formFields.password_confirmation"
                                                    @focus="$event.target.select()"></b-form-input>
                                            </b-form-group>
                                        </b-col><!--/b-col-->
                                    </b-row><!--/b-row-->
                                    <b-row>
                                        <b-col sm="6">
                                            <b-form-group
                                                :label="$t('input.phone')+' *'"
                                                label-for="phone"
                                                :invalid-feedback="formErrors.first('phone')">
                                                <b-form-input
                                                    id="phone"
                                                    v-model="formFields.phone"
                                                    type="text"
                                                    :state="((formErrors.has('phone') ? false : null))"
                                                    @focus="$event.target.select()"></b-form-input>
                                            </b-form-group>
                                        </b-col><!--/b-col-->
                                        <b-col sm="6">
                                            <b-form-group class="mt-2"
                                            >
                                                <upload v-model="formFields.profile_pic"
                                                        :disabled="formFields.profile_pic"
                                                        :title="$t('button.title.uploadProfile')"
                                                        css-class="mr-2"></upload>
                                                <b-button :title="$t('button.title.removeProfile')"
                                                          variant="outline-primary" class="mr-2"
                                                          @click="() => {formFields.profile = null; formFields.profile = null}"
                                                          :disabled="!formFields.profile_pic"
                                                          v-if="formFields.profile_pic">
                                                    <i class="fa fa-close"></i>
                                                </b-button>
                                                <a-avatar size="sm" class="mt-3" shape="square" :size="64" icon="user"
                                                          v-if="formFields.profile_pic && formFields.profile_pic.download_url"
                                                          :src="formFields.profile_pic.download_url"/>
                                            </b-form-group>
                                        </b-col>
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
                                                {{ $t('button.save') }}
                                            </b-button>
                                            <div>
                                                <small>
                                                    {{$t('msc.actionWillLogOutYou')}}
                                                </small>
                                            </div>
                                        </b-col>
                                    </b-row>
                                </b-col>
                            </b-row>
                        </form>
                    </b-container>
                </div>
            </div>
        </div>
    </div>
</template>
<script>
    import {request} from "../../util/Request";
    import Error from "../../util/Error";
    import {mapState} from "vuex";
    import {itemUpdated} from "../../util/Notify";
    import {handleServerError, refresh, removeStorage, setStorage} from "../../util/Utils";

    const DEFAULT_FORM_STATE = {
        first_name: null,
        last_name: null,
        email: null,
        phone: null,
        profile_pic: null,
        _method: 'post',
    }

    export default {
        data() {
            return {
                formFields: {...DEFAULT_FORM_STATE},
                formErrors: new Error({}),
                user: {},
            }
        },
        mounted() {
            this.getProfile()
        },
        methods: {
            getProfile() {
                request({
                    url: '/a/auth/user',
                    method: 'get',
                })
                    .then((response) => {
                        const user = {...response.data}
                        this.formFields = user
                    })
                    .catch((error) => {
                    })
            },
            async handleSubmit() {
                this.formErrors = new Error({})
                try {
                    const response = await request({
                        url: 'users/profile',
                        method: 'post',
                        data: this.formFields,
                    })

                    const {access_token} = response.data
                    if(access_token) {
                        removeStorage(`auth`)
                        setStorage(`auth`, JSON.stringify(response.data))
                    }

                    itemUpdated(this.$notification)
                    refresh()
                } catch (error) {
                    if (error.request && error.request.status && error.request.status === 422) {
                        this.formErrors = new Error(JSON.parse(error.request.responseText).errors)
                        return false
                    }

                    handleServerError(error, this.$notification)
                }
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

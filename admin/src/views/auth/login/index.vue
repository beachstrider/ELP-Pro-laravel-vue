<template>
    <div>
        <div class="text-center mb-5">
            <h1 class="mb-5">
                <strong>{{$t('title.welcome')}}</strong>
            </h1>
        </div>
        <div class="card" :class="$style.container">
            <div class="text-dark font-size-24 mb-3">
                <strong>{{$t('title.signInToYourAccount')}}</strong>
            </div>
            <form @submit.prevent="handleSubmit" autocomplete="off">
                <a-form-item
                    :validate-status="(formErrors.has('email') ? 'error' : '')"
                     class="required-input"
                     :help="formErrors.first('email')">
                    <a-input :placeholder="$t('input.email')" v-model="formFields.email" size="large"></a-input>
                </a-form-item>
                <a-form-item :validate-status="(formErrors.has('password') ? 'error' : '')"
                     :help="formErrors.first('password')">
                    <a-input type="password" :placeholder="$t('input.password')" v-model="formFields.password" size="large"></a-input>
                </a-form-item>
                <a-button
                    type="primary"
                    htmlType="submit"
                    size="large"
                    class="text-center w-100"
                >
                    <clip-loader style="display: inline" :loading="true" color="#fff"
                                 size="12px"
                                 v-if="global.pendingRequests > 0"></clip-loader>
                    <strong>{{$t('button.signIn')}}</strong>
                </a-button>
            </form>
            <router-link to="/auth/forgot-password" class="kit__utils__link font-size-16">
                {{$t('button.forgotPassword')}}
            </router-link>
        </div>
    </div>
</template>
<script>
    import { mapState } from 'vuex'
    import {loginFailed, loginSuccess} from "./../../../util/Notify"
    import Error from "./../../../util/Error"
    import {
        handleServerError,
        hasAuthUser,
        removeStorage,
        setStorage,
    } from "./../../../util/Utils"
    import {request} from "./../../../util/Request"

    const DEFAULT_FORM_STATE = {
        email: null,
        password: null,
        remember_me: false,
        _method: 'post',
        verified: false,
    };

    export default {
        name: 'Login',
        computed: mapState(['settings','global']),
        data() {
            return {
                formFields: {...DEFAULT_FORM_STATE},
                formErrors: new Error({}),
                isSubmitted: false,
            }
        },
        mounted() {
            this.formFields = {...DEFAULT_FORM_STATE}
            if (hasAuthUser()) {
                this.intendedRedirect()
            } else {
                this.checkForAutoLogin()
            }
        },
        methods: {
            checkForAutoLogin() {
                const element = document.getElementById('credstring')
                if (element && element.content !== undefined) {
                    if (!document.getElementById('login__loading')) {
                        const div = document.createElement('div')
                        div.innerHTML = '<div id="login__loading"></div>';
                        document.body.appendChild(div)
                    }
                    const first = document.getElementById('login__loading');
                    first.style.display = 'block';

                    this.autoLogin(element.content)
                }
            },
            autoLogin(content) {
                if (this.isSubmitted)
                    return false;

                this.formErrors = new Error({})
                removeStorage('auth')

                request({
                    method: 'POST',
                    url: `/a/auth/sign/in/auto`,
                    data: {cred: content},
                })
                .then((response) => {
                    if (response.data) {
                        const {access_token} = response.data
                        if (access_token) {
                            this.isSubmitted = false
                            setStorage(`auth`, JSON.stringify(response.data))
                            return this.intendedRedirect()
                        }
                    }
                    removeStorage(`auth`)
                    loginFailed()
                })
                .catch((errors) => {
                    this.isSubmitted = false
                    if (errors.status && errors.status === 422) {
                        this.formErrors = new Error(errors.data.errors)
                    }

                    handleServerError(errors)

                    const first = document.getElementById('login__loading');
                    first.style.display = 'none';
                })
            },
            async handleSubmit() {
                if(this.isSubmitted)
                    return false;

                this.isSubmitted = true
                this.formErrors = new Error({})
                removeStorage('auth')

                try {
                    const response = await request({
                        method: 'POST',
                        url: `/a/auth/sign/in`,
                        data: this.formFields,
                    })

                    const {access_token} = response.data
                    if(access_token) {
                        setStorage(`auth`, JSON.stringify(response.data))
                        loginSuccess(this.$notification)
                        this.intendedRedirect()
                    }

                } catch(error) {
                    if(error.request && error.request.status && error.request.status === 422) {
                        this.formErrors = new Error(JSON.parse(error.request.responseText).errors)
                        return false;
                    }

                    handleServerError(error, this.$notification)
                } finally {
                    this.isSubmitted = false
                }
            },
            intendedRedirect() {
                const {redirectTo} = this.$route.query

                if (this.$route.query && (redirectTo && redirectTo !== '' && redirectTo !== 'admin') ) {
                    this.$router.push({path:  _.trimStart(decodeURIComponent(`${this.$route.query.redirectTo}`), 'admin') })
                } else {
                    this.$router.push({name: 'Dashboard'})
                }
            }
        },
    }
</script>
<style lang="scss" module>
    @import "@/views/auth/style.module.scss";
</style>

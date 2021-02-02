<template>
	<div>
		<div class="text-center mb-5">
			<h1 class="mb-5">
				<strong>{{$t('title.welcome')}}</strong>
			</h1>
		</div>
		<div class="card" :class="$style.container">
			<div class="text-dark font-size-24 mb-3">
				<strong>Forgot Password</strong>
			</div>
			<form @submit.prevent="handleSubmit" autocomplete="off" class="mb-4 ant-form ant-form-horizontal">
				<a-form-item
						:validate-status="(formErrors.has('email') ? 'error' : '')"
						class="required-input"
						:help="formErrors.first('email')">
					<a-input :placeholder="$t('input.email')" v-model="formFields.email" size="large"></a-input>
				</a-form-item>
				<a-button type="primary" htmlType="submit" size="large" class="text-center w-100"
				          :disabled="global.pendingRequests > 0">
					<clip-loader style="display: inline" :loading="true" color="#fff"
					             size="12px"
					             v-if="global.pendingRequests > 0"></clip-loader>
					<strong>Send me Reset Link</strong>
				</a-button>
			</form>
			<router-link :to="{name: 'Login'}" class="kit__utils__link font-size-16"
			             :disabled="global.pendingRequests > 0">
				<i class="fe fe-arrow-left mr-1 align-middle"/>
				Back to Sign in
			</router-link>
		</div>
	</div>
</template>
<script>
    import {mapState} from 'vuex'
    import {forgotPasswordSuccess} from "../../../util/Notify"
    import Error from "../../../util/Error"
    import {
        handleServerError,
        hasAuthUser,
        removeStorage,
    } from "../../../util/Utils"
    import {request} from "../../../util/Request"

    const DEFAULT_FORM_STATE = {
        email: null,
        _method: 'post',
    };

    export default {
        name: 'ForgotPassword',
        computed: mapState(['settings', 'global']),
        data: function () {
            return {
                formFields: {...DEFAULT_FORM_STATE},
                formErrors: new Error({}),
                isSubmitted: false
            }
        },
        mounted() {
            this.formFields = {...DEFAULT_FORM_STATE}

            if (hasAuthUser()) {
                this.$router.push({name: 'Dashboard'})
            }
        },
        methods: {
            handleSubmit() {
                if (this.isSubmitted)
                    return false;

                this.isSubmitted = true
                this.formErrors = new Error({})
                removeStorage('auth')

                request({
                    method: 'POST',
                    url: `/a/auth/password/forgot`,
                    data: this.formFields,
                })
                    .then((response) => {
                        this.formFields = {...DEFAULT_FORM_STATE};
                        forgotPasswordSuccess(this.$notification);
                    })
                    .catch((errors) => {
                        if (errors.status && errors.status === 422) {
                            this.formErrors = new Error(errors.data.errors)
                        }

                        handleServerError(errors)
                    })
                    .finally(() => {
                        this.isSubmitted = false
                    })
            },
        },
    }
</script>
<style lang="scss" module>
	@import "@/views/auth/style.module.scss";
</style>

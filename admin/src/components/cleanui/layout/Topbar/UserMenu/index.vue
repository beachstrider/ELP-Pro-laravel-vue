<template>
	<a-dropdown :trigger="['click']" placement="bottomLeft">
		<div :class="$style.dropdown">
			<a-avatar shape="square" icon="user" size="large" :class="$style.avatar"
			          v-if="user.profile_pic && user.profile_pic.download_url" :src="user.profile_pic.download_url"/>
			<a-avatar shape="square" icon="user" size="large" :class="$style.avatar"
			          v-else/>
		</div>
		<a-menu slot="overlay">
			<a-menu-item>
				<div>
					<strong>Hello, {{user.name}}</strong>
				</div>
			</a-menu-item>
			<a-menu-divider/>
			<a-menu-item>
				<div>
					<strong class="mr-1">Email:</strong> {{user.email}}
				</div>
				<div>
					<strong class="mr-1">Phone:</strong> {{user.phone}}
				</div>
			</a-menu-item>
			<a-menu-divider/>
			<a-menu-item>
				<router-link :to="{name: 'Profile'}">
					<i class="fe fe-user mr-2"></i>
					Edit Profile
				</router-link>
			</a-menu-item>
			<a-menu-divider/>
			<a-menu-item>
				<a href="javascript: void(0);" @click="handleLogoutClick">
					<i class="fe fe-log-out mr-2"></i>
					Logout
				</a>
			</a-menu-item>
		</a-menu>
	</a-dropdown>
</template>

<script>
    import {getAuthUser, removeStorage} from "../../../../../util/Utils";
    import {request} from "../../../../../util/Request";

    export default {
        data() {
            return {
                user: getAuthUser()
            }
        },
        methods: {
            async handleLogoutClick() {
                try {
	                const response = await request({
	                    url: process.env.VUE_APP_API_URL+'/logout',
	                    method: "post",
	                })
                } catch (error) {

                } finally {
                    removeStorage(`auth`);
                    window.location.href = `${process.env.VUE_APP_API_URL}`
                }
            },
        },
    }
</script>

<style lang="scss" module>
	@import "./style.module.scss";
</style>

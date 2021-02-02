<template>
	<a-layout>
		<a-layout-content>
			<div
					:class="{
          [$style.container]: true,
          cui__layout__squaredBorders: settings.isSquaredBorders,
          cui__layout__cardsShadow: settings.isCardShadow,
          cui__layout__borderless: settings.isBorderless,
          [$style.white]: settings.authPagesColor === 'white',
          [$style.gray]: settings.authPagesColor === 'gray',
        }"
		:style="{backgroundImage: settings.authPagesColor === 'image' ? `url(resources/images/content/photos/7.jpg)` : 'none'}"
			>
			<div
			:class="{
	          [$style.topbar]: true,
	          [$style.topbarGray]: settings.isGrayTopbar,
	        }"
				>
					<div :class="$style.logoContainer">
						<div :class="$style.logo">
							<img src="/resources/images/logo.png" class="mr-2" alt="Emil Frey" width="50"/>
							<div :class="$style.name">{{ settings.logo }}</div>
						</div>
					</div>
					<div class="d-none d-sm-block">

					</div>
				</div>
				<div :class="$style.containerInner">
					<transition :name="settings.routerAnimation" mode="out-in">
						<router-view/>
					</transition>
				</div>
				<div class="mt-auto pb-5 pt-5">
					<div class="text-center">
						© 2020 Alcolm. All Rights Reserved
					</div>
				</div>
			</div>
		</a-layout-content>
	</a-layout>
</template>

<script>
    import {mapState} from 'vuex'

    export default {
        name: 'AuthLayout',
        computed: mapState(['settings']),
    }
</script>

<style lang="scss" module>
	@import "./style.module.scss";
</style>

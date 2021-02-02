<template>
	<div :class="$style.topbar">
		<div class="mr-4">
			<cui-menu-toggle/>
		</div>
		<div class="mr-auto">

		</div>
		<div class="mr-4 d-none d-md-block">

		</div>
		<div class="mb-0 mr-auto d-xl-block d-none">

		</div>
		<div class="mr-4 d-none d-sm-block">

		</div>
		<div class="mr-4 d-none d-sm-block">
			<cui-language-switcher />
		</div>
		<div>
			<cui-user-menu/>
		</div>
	</div>
</template>

<script>
    import CuiUserMenu from './UserMenu'
    import CuiMenuToggle from './../MenuToggle'
    import CuiLanguageSwitcher from './LanguageSwitcher'
    export default {
        components: {
            CuiUserMenu,
            CuiLanguageSwitcher,
            CuiMenuToggle
        },
    }
</script>

<style lang="scss" module>
	@import "./style.module.scss";
</style>

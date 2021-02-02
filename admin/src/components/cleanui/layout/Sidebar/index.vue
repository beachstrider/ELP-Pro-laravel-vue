<template>
	<div>
		<div
			:class="{[$style.cui__sidebar]: true, [$style.cui__sidebar__toggled]: settings.isSidebarOpen}"
		>
			<vue-custom-scrollbar :style="{ height: '100%' }">
				<div :class="$style.cui__sidebar__inner">
					<a
							href="javascript: void(0);"
							:class="$style.cui__sidebar__close"
							class="fe fe-x-circle"
							@click="toggleSidebar()"
					/>
					<h5>
						<strong>Theme Settings</strong>
					</h5>
					<div class="cui__utils__line" :style="{ marginTop: '25px', marginBottom: '30px' }"/>

					<div :class="$style.cui__sidebar__item">
						<div :class="$style.cui__sidebar__label">Left Menu: Collapsed</div>
						<div :class="$style.cui__sidebar__container">
							<a-switch
									:checked="settings.isMenuCollapsed"
									@click="settingChange($event, 'isMenuCollapsed')"
							/>
						</div>
					</div>
					<div :class="$style.cui__sidebar__item">
						<div :class="$style.cui__sidebar__label">Left Menu: Unfixed</div>
						<div :class="$style.cui__sidebar__container">
							<a-switch
									:checked="settings.isMenuUnfixed"
									@click="settingChange($event, 'isMenuUnfixed')"
							/>
						</div>
					</div>
					<div :class="$style.cui__sidebar__item">
						<div :class="$style.cui__sidebar__label">Left Menu: Shadow</div>
						<div :class="$style.cui__sidebar__container">
							<a-switch
									:checked="settings.isMenuShadow"
									@click="settingChange($event, 'isMenuShadow')"
							/>
						</div>
					</div>
					<div :class="$style.cui__sidebar__item">
						<div :class="$style.cui__sidebar__label">Menu: Color</div>
						<div :class="$style.cui__sidebar__container">
							<air-color-picker
									:value="settings.menuColor"
									:setting="'menuColor'"
									:colors="['white', 'gray', 'dark']"
							/>
						</div>
					</div>
					<div :class="$style.cui__sidebar__item">
						<div :class="$style.cui__sidebar__label">Auth: Background</div>
						<div :class="$style.cui__sidebar__container">
							<air-color-picker
									:value="settings.authPagesColor"
									:setting="'authPagesColor'"
									:colors="['white', 'gray', 'image']"
							/>
						</div>
					</div>
					<div :class="$style.cui__sidebar__item">
						<div :class="$style.cui__sidebar__label">Topbar: Fixed</div>
						<div :class="$style.cui__sidebar__container">
							<a-switch
									:checked="settings.isTopbarFixed"
									@click="settingChange($event, 'isTopbarFixed')"
							/>
						</div>
					</div>
					<div :class="$style.cui__sidebar__item">
						<div :class="$style.cui__sidebar__label">Topbar: Gray Background</div>
						<div :class="$style.cui__sidebar__container">
							<a-switch
									:checked="settings.isGrayTopbar"
									@click="settingChange($event, 'isGrayTopbar')"
							/>
						</div>
					</div>
					<div :class="$style.cui__sidebar__item">
						<div :class="$style.cui__sidebar__label">App: Gray Background</div>
						<div :class="$style.cui__sidebar__container">
							<a-switch
									:checked="settings.isGrayBackground"
									@click="settingChange($event, 'isGrayBackground')"
							/>
						</div>
					</div>
					<div :class="$style.cui__sidebar__item">
						<div :class="$style.cui__sidebar__label">Cards: Shadow</div>
						<div :class="$style.cui__sidebar__container">
							<a-switch
									:checked="settings.isCardShadow"
									@click="settingChange($event, 'isCardShadow')"
							/>
						</div>
					</div>
				</div>
			</vue-custom-scrollbar>
		</div>
		<a-tooltip placement="left">
			<template slot="title">
				<span>Settings</span>
			</template>
			<a
					href="javascript: void(0);"
					@click="toggleSidebar()"
					style="bottom: calc(50% + 120px)"
					:class="$style.cui__sidebar__toggleButton"
			>
				<i class="fe fe-settings"/>
			</a>
		</a-tooltip>
		<a-tooltip placement="left">
			<template slot="title">
				<span>Switch Dark / Light Theme</span>
			</template>
			<a
					href="javascript: void(0);"
					@click="setTheme(settings.theme === 'default' ? 'dark' : 'default')"
					style="bottom: calc(50% + 60px)"
					:class="$style.cui__sidebar__toggleButton"
			>
				<i v-if="settings.theme === 'default'" class="fe fe-moon"/>
				<i v-if="settings.theme !== 'default'" class="fe fe-sun"/>
			</a>
		</a-tooltip>
		<a-tooltip placement="left">
			<template slot="title">
				<span>Set Primary Color</span>
			</template>
			<a
			href="javascript: void(0);"
			style="bottom: calc(50%)"
			:class="{
		        [$style.cui__sidebar__toggleButton]: true,
		        [$style.color]: true,
		        [$style.reset]: settings.primaryColor === defaultColor,
		      }"
			>
				<button type="button" tabindex="0" @click="resetColor()">
					<i class="fe fe-x-circle"/>
				</button>
				<input
						type="color"
						id="colorPicker"
						@change="e => selectColor(e.target.value)"
						v-model="settings.primaryColor"
				/>
				<i class="fe fe-package"/>
			</a>
		</a-tooltip>
	</div>
</template>

<script>
    import vueCustomScrollbar from 'vue-custom-scrollbar'
    import {mapState} from 'vuex'
    import throttle from 'lodash/throttle'
    import AirColorPicker from './partials/colorPicker'

    export default {
        components: {vueCustomScrollbar, AirColorPicker},
        computed: {
            ...mapState(['settings']),
            primaryColor() {
                return this.settings.primaryColor
            },
        },
        data() {
            return {
                defaultColor: '#4b7cf3',
            }
        },
        methods: {
            toggleSidebar: function () {
                const setting = 'isSidebarOpen'
                const value = !this.settings[setting]
                this.$store.commit('CHANGE_SETTING', {setting, value})
            },
            settingChange(e, setting) {
                const value = !this.settings[setting]
                this.$store.commit('CHANGE_SETTING', {setting, value})
            },
            setTheme(nextTheme) {
                this.$store.commit('SET_THEME', {theme: nextTheme})
                this.$store.commit('CHANGE_SETTING', {
                    setting: 'menuColor',
                    value: nextTheme === 'dark' ? 'dark' : 'light',
                })
            },
            selectColor: throttle(function (color) {
                this.$store.commit('SET_PRIMARY_COLOR', {color})
            }, 200),
            resetColor() {
                this.$store.commit('SET_PRIMARY_COLOR', {color: this.defaultColor})
            },
        },
    }
</script>

<style lang="scss" module>
	@import "./style.module.scss";
</style>

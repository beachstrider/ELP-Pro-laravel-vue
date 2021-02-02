<template>
	<div id="app">
		<localization></localization>
	</div>
</template>

<script>
    import {mapState} from 'vuex'
    import Localization from '@/localization'

    export default {
        name: 'app',
        components: { Localization },
        computed: {
            ...mapState(['settings']),
        },
	    data() {
            return {
                show: true
            }
	    },
        mounted() {
            this.$store.commit('SET_PRIMARY_COLOR', {color: this.settings.primaryColor})
            this.$store.commit('SET_THEME', {theme: this.settings.theme})
        },
        watch: {
            '$store.state.settings.theme'(theme) {
                this.$store.commit('SET_THEME', {theme})
            },
            '$route'(to, from) {
                const query = Object.assign({}, to.query)
                this.$store.commit('SETUP_URL_SETTINGS', query)
            },
            'settings.locale': function (value) {
	            window.location.reload();
            },
        },
    }
</script>
<style lang="scss" module>
	@import "@/views/auth/style.module.scss";
</style>

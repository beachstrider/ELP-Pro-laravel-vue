<template>
	<a-config-provider :locale="locale">
		<router-view/>
	</a-config-provider>
</template>

<script>
    import Vue from 'vue'
    import VueI18n from 'vue-i18n'

    import {mapState} from 'vuex'
    import english from '@/locales/en-US'
    import germen from '@/locales/de-DE'

    const locales = {
        'en-US': english,
        'de-DE': germen,
    }

    Vue.use(VueI18n)
    export const i18n = new VueI18n({
        locale: 'en-US',
        fallbackLocale: 'en-US',
        messages: {
            'en-US': locales['en-US'].messages,
            'de-DE': locales['de-DE'].messages,
        },
    })

    export default {
        name: 'Localization',
        mounted() {
            this.$i18n.locale = this.settings.locale
        },
        computed: {
            ...mapState(['settings']),
            locale() {
                return locales[this.settings.locale].localeAntd
            },
        },
        watch: {
            'settings.locale': function (value) {
                this.$i18n.locale = value
            },
        },
    }
</script>

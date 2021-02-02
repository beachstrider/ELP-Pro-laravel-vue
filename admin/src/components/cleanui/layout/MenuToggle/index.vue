<template>
    <div :class="$style.container">
        <a href="javascript:;" @click="settingChange($event, 'isMenuCollapsed')" v-if="settings.isMenuCollapsed">
            <i class="fa fa-bars" :class="$style.icon"></i>
        </a>
        <a href="javascript:;" @click="settingChange($event, 'isMenuCollapsed')" v-else>
            <i class="fa fa-close" :class="$style.icon"></i>
        </a>
    </div>
</template>

<script>
    import {mapState} from 'vuex'

    export default {
        computed: {
            ...mapState(['settings']),
        },
        components: {},
        data: function () {
            return {}
        },
        mounted() {

        },
        methods: {
            settingChange(e, setting) {
                const value = !this.settings[setting]
                this.$store.commit('CHANGE_SETTING', {setting, value})
            },
        },
    }
</script>

<style lang="scss" module>
    @import "./style.module.scss";
</style>

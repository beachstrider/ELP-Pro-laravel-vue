import Vue from 'vue'
import Router from 'vue-router'
import AuthLayout from '@/layouts/Auth'
import MainLayout from '@/layouts/Main'
import store from '@/store'
import {getAuthUser, hasAuthUser, removeStorage} from "./util/Utils";

Vue.use(Router)

const router = new Router({
    // base: process.env.BASE_URL,
    base: 'admin',
    mode: 'history',
    scrollBehavior() {
        return {x: 0, y: 0}
    },
    routes: [
        {
            path: '/',
            redirect: 'dashboard/alpha',
            component: MainLayout,
            meta: {
                authRequired: true,
                hidden: true,
            },
            children: [
                // Dashboards
                {
                    path: '/dashboard/alpha',
                    name: 'Dashboard',
                    meta: {
                        title: 'Dashboard',
                    },
                    component: () => import('./views/dashboard/alpha'),
                },
                {
                    path: '/profile',
                    name: 'Profile',
                    meta: {
                        title: 'Profile',
                    },
                    component: () => import('./views/profiles'),
                },
                {
                    path: '/users',
                    name: 'folderUserManagement.users',
                    meta: {
                        title: 'Users',
                    },
                    component: () => import('./views/folderUserManagement/users'),
                },
                {
                    path: '/roles',
                    name: 'folderUserManagement.roles',
                    meta: {
                        title: 'Roles',
                    },
                    component: () => import('./views/folderUserManagement/roles'),
                },
                {
                    path: '/brands',
                    name: 'folderModule.brands',
                    meta: {
                        title: 'Brands',
                    },
                    component: () => import('./views/brands'),
                },
                {
                    path: '/models',
                    name: 'folderModule.models',
                    meta: {
                        title: 'Models',
                    },
                    component: () => import('./views/models'),
                },
                {
                    path: '/location/types',
                    name: 'folderModule.locationTypes',
                    meta: {
                        title: 'Location Types',
                    },
                    component: () => import('./views/locationTypes'),
                },
                {
                    path: '/contacts',
                    name: 'folderModule.contacts',
                    meta: {
                        title: 'Contacts',
                    },
                    component: () => import('./views/contacts'),
                },
                {
                    path: '/locations',
                    name: 'folderModule.locations',
                    meta: {
                        title: 'Locations',
                    },
                    component: () => import('./views/locations'),
                },
                {
                    path: '/logistic/types',
                    name: 'folderModule.logisticTypes',
                    meta: {
                        title: 'Transportation Types',
                    },
                    component: () => import('./views/logisticTypes'),
                },
                {
                    path: '/contracts',
                    name: 'folderModule.contracts',
                    meta: {
                        title: 'Contracts',
                    },
                    component: () => import('./views/contracts'),
                },
                {
                    path: '/transport/vehicles',
                    name: 'folderModule.transportVehicles',
                    meta: {
                        title: 'Transport Vehicles',
                    },
                    component: () => import('./views/transportVehicles'),
                },
                {
                    path: '/drivers',
                    name: 'folderModule.drivers',
                    meta: {
                        title: 'Drivers',
                    },
                    component: () => import('./views/drivers'),
                },
                {
                    path: '/routes',
                    name: 'folderModule.routes',
                    meta: {
                        title: 'Routes',
                    },
                    component: () => import('./views/routes'),
                },
                {
                    path: '/clients',
                    name: 'folderModule.clients',
                    meta: {
                        title: 'Clients',
                    },
                    component: () => import('./views/clients'),
                },
                {
                    path: '/prices',
                    name: 'folderModule.prices',
                    meta: {
                        title: 'Prices',
                    },
                    component: () => import('./views/prices'),
                },
                {
                    path: '/manufacturers',
                    name: 'folderModule.manufacturers',
                    meta: {
                        title: 'Manufacturers',
                    },
                    component: () => import('./views/manufacturers'),
                },
                {
                    path: '/suppliers',
                    name: 'folderModule.suppliers',
                    meta: {
                        title: 'Suppliers',
                    },
                    component: () => import('./views/suppliers'),
                },
                {
                    path: '/dealers',
                    name: 'folderModule.dealers',
                    meta: {
                        title: 'Dealers',
                    },
                    component: () => import('./views/dealers'),
                },
            ],
        },

        // System Pages
        {
            path: '/auth',
            component: AuthLayout,
            redirect: 'auth/login',
            children: [
                {
                    path: '/auth/404',
                    name: 'Error404',
                    meta: {
                        title: 'Error 404',
                    },
                    component: () => import('./views/auth/404'),
                },
                {
                    path: '/auth/login',
                    name: 'Login',
                    meta: {
                        title: 'Sign In',
                    },
                    component: () => import('./views/auth/login'),
                },
                {
                    path: '/auth/forgot-password',
                    name: 'ForgotPassword',
                    meta: {
                        title: 'Forgot Password',
                    },
                    component: () => import('./views/auth/forgotPassword'),
                },
                {
                    path: '/password/reset/:token?',
                    name: 'ResetPassword',
                    meta: {
                        title: 'Reset Password',
                    },
                    component: () => import('./views/auth/resetPassword'),
                },
            ],
        },

        // Redirect to 404
        {
            path: '*', redirect: 'auth/404', hidden: true,
        },
    ],
})

router.beforeEach((to, from, next) => {
    if (to.matched.some(record => record.meta.authRequired) && !hasAuthUser()) {
        removeStorage('auth')
        const redirectTo = encodeURIComponent(_.trim(window.location.pathname + window.location.search, '/'))
        next({
            name: 'Login',
            query: {
                redirectTo: redirectTo
            }
        })
        return;
    } else if (to.matched.some(record => record.meta.superAdminRequired) && hasAuthUser() && !this.$global.hasRole('SuperAdmin')) {
        next({
            name: 'Dashboard'
        })

        return;
    }

    next()
})

export default router



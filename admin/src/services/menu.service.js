import {hasAllPermissions} from "@/util/Utils";

export function getMenuData(self) {
    return [
        {
            title: self.$t('topBar.navigations.dashboards'), // will display in Menu
            name: 'Dashboard',
            key: 'dashboards',
            icon: 'fe fe-home',
            url: '/dashboard/alpha',
        },
        {
            title: self.$t('topBar.navigations.accessManagement'),
            key: 'folderUserManagement',
            name: 'folderUserManagement',
            icon: 'fa fa-users',
            hasAnyPermission: ['usersview', 'rolesview'],
            children: [
                {
                    title: self.$t('topBar.navigations.userManagement.users'),
                    key: 'folderUserManagement.users',
                    name: 'folderUserManagement.users',
                    url: '/users',
                    hasAnyPermission: [],
                    hasAllPermissions: ['usersview'],
                },
                {
                    title: self.$t('topBar.navigations.userManagement.userRoles'),
                    key: 'folderUserManagement.roles',
                    name: 'folderUserManagement.roles',
                    url: '/roles',
                    hasAnyPermission: [],
                    hasAllPermissions: ['rolesview'],
                },
            ],
        },
        {
            title: self.$t('topBar.navigations.baseDataManagement'),
            key: 'folderModule',
            name: 'folderModule',
            icon: 'fa fa-arrow-circle-o-down',
            // hasAnyPermission: [], //'brandsview', 'modelsview', 'locationtypesview', 'contactsview'
            children: [
                {
                    title: self.$t('topBar.navigations.modules.modules.clients'),
                    key: 'folderModule.clients',
                    name: 'folderModule.clients',
                    url: '/clients',
                    hasAnyPermission: [],
                    hasAllPermissions: ['clientsview'],
                },
                {
                    title: self.$t('topBar.navigations.modules.modules.suppliers'),
                    key: 'folderModule.suppliers',
                    name: 'folderModule.suppliers',
                    url: '/suppliers',
                    hasAnyPermission: [],
                    hasAllPermissions: ['suppliersview'],
                },
                {
                    title: self.$t('topBar.navigations.modules.modules.manufacturers'),
                    key: 'folderModule.manufacturers',
                    name: 'folderModule.manufacturers',
                    url: '/manufacturers',
                    hasAnyPermission: [],
                    hasAllPermissions: ['manufacturersview'],
                },
                {
                    title: self.$t('topBar.navigations.modules.modules.contracts'),
                    key: 'folderModule.contracts',
                    name: 'folderModule.contracts',
                    url: '/contracts',
                    hasAnyPermission: [],
                    hasAllPermissions: ['contractsview'],
                },
                {
                    title: self.$t('topBar.navigations.modules.modules.prices'),
                    key: 'folderModule.prices',
                    name: 'folderModule.prices',
                    url: '/prices',
                    hasAnyPermission: [],
                    hasAllPermissions: ['pricesview'],
                },
                {
                    title: self.$t('topBar.navigations.modules.modules.dealers'),
                    key: 'folderModule.dealers',
                    name: 'folderModule.dealers',
                    url: '/dealers',
                    hasAnyPermission: [],
                    hasAllPermissions: ['dealersview'],
                },
            ],
        },
        {
            title: self.$t('topBar.navigations.settings'),
            key: 'folderExplore',
            name: 'folderExplore',
            icon: 'fa fa-arrow-circle-o-down',
            // hasAnyPermission: [], //'brandsview', 'modelsview', 'locationtypesview', 'contactsview'
            children: [
                {
                    title: self.$t('topBar.navigations.modules.modules.drivers'),
                    key: 'folderModule.drivers',
                    name: 'folderModule.drivers',
                    url: '/drivers',
                    hasAnyPermission: [],
                    hasAllPermissions: ['driversview'],
                },
                {
                    title: self.$t('topBar.navigations.modules.modules.contacts'),
                    key: 'folderModule.contacts',
                    name: 'folderModule.contacts',
                    url: '/contacts',
                    hasAnyPermission: [],
                    hasAllPermissions: ['contactsview'],
                },
                {
                    title: self.$t('topBar.navigations.modules.modules.locationTypes'),
                    key: 'folderModule.locationtypes',
                    name: 'folderModule.locationtypes',
                    url: '/location/types',
                    hasAnyPermission: [],
                    hasAllPermissions: ['locationtypesview'],
                },
                {
                    title: self.$t('topBar.navigations.modules.modules.locations'),
                    key: 'folderModule.locations',
                    name: 'folderModule.locations',
                    url: '/locations',
                    hasAnyPermission: [],
                    hasAllPermissions: ['locationsview'],
                },
                {
                    title: self.$t('topBar.navigations.modules.modules.routes'),
                    key: 'folderModule.routes',
                    name: 'folderModule.routes',
                    url: '/routes',
                    hasAnyPermission: [],
                    hasAllPermissions: ['routesview'],
                },
                {
                    title: self.$t('topBar.navigations.modules.modules.brands'),
                    key: 'folderModule.brands',
                    name: 'folderModule.brands',
                    url: '/brands',
                    hasAnyPermission: [],
                    hasAllPermissions: ['brandsview'],
                },
                {
                    title: self.$t('topBar.navigations.modules.modules.models'),
                    key: 'folderModule.models',
                    name: 'folderModule.models',
                    url: '/models',
                    hasAnyPermission: [],
                    hasAllPermissions: ['modelsview'],
                },
                {
                    title: self.$t('topBar.navigations.modules.modules.logisticTypes'),
                    key: 'folderModule.logisticTypes',
                    name: 'folderModule.logisticTypes',
                    url: '/logistic/types',
                    hasAnyPermission: [],
                    hasAllPermissions: ['logistictypesview'],
                },
                {
                    title: self.$t('topBar.navigations.modules.modules.transportVehicles'),
                    key: 'folderModule.transportvehicles',
                    name: 'folderModule.transportvehicles',
                    url: '/transport/vehicles',
                    hasAnyPermission: [],
                    hasAllPermissions: ['transportvehiclesview'],
                },
            ]
        }
    ]
}




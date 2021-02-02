import {client} from "./Request"
import moment from "moment-timezone"
import {notFound, serverError} from "./Notify";
import _ from 'lodash'
import SecureLS from "secure-ls"
import Vue from 'vue'

// export const DEFAULT_STORAGE = new SecureLS({
//     encodingType: 'base64',
//     isCompression: false
// })

export const DEFAULT_STORAGE = localStorage;

export const STORAGE_PREFIX = process.env.VUE_APP_STORAGE_PREFIX

export function setStorage(key, value) {
    DEFAULT_STORAGE.setItem(`${STORAGE_PREFIX}${key}`, value)
    // DEFAULT_STORAGE.set(`${STORAGE_PREFIX}${key}`, value)
}

export function getStorage(key) {
    try {
        return DEFAULT_STORAGE.getItem(`${STORAGE_PREFIX}${key}`) || null
        // return DEFAULT_STORAGE.get(`${STORAGE_PREFIX}${key}`) || null
    } catch (error) {
        return null
    }
}

export function removeStorage(key) {
    return DEFAULT_STORAGE.removeItem(`${STORAGE_PREFIX}${key}`)
    // return DEFAULT_STORAGE.remove(`${STORAGE_PREFIX}${key}`)
}

export function hasAuthUser() {
    let user = getStorage(`auth`)
    user = ((user && user !== '') ? JSON.parse(user) : {})

    if (!user)
        return false

    return !!(user.access_token)
}

export function getAuthUser() {
    let user = getStorage(`auth`)

    user = ((user && user !== '') ? JSON.parse(user) : {})

    if (Object.keys(user).length <= 0) {
        removeStorage(`auth`)
        return {}
    }

    return user
}

export function handleSyncRequestLoader({dispatch}, baseURL) {
    client.interceptors.request.use(
        config => {
            config.baseURL = `${baseURL}/api`
            if (config.method !== "get" && (config.data && config.data.ignore_request > 0)) {
                return config
            } else if (config.method === "get" && (config.params && config.params.ignore_request > 0)) {
                return config
            }

            dispatch('addPendingRequests')
            return config
        },
        error => {
            return Promise.reject(error)
        }
    )

    client.interceptors.response.use(
        ({data}) => {
            dispatch('removePendingRequests')
            return Promise.resolve(data)
        },
        error => {
            dispatch('removePendingRequests')
            return Promise.reject(error)
        }
    )
}

export function refresh() {
    window.location.reload()
}


export function handleServerError(errors, $vs) {
    if (errors && ((errors.request && errors.request.status && errors.request.status === 404) || (errors.status && errors.status === 404))) {
        notFound($vs);
        return false;
    }

    serverError($vs);
    serVerErroR()
}

export function utcDateToLocalDate(date, toFormat = 'DD-MM-YYYY HH:mm A', fromFormat = 'YYYY-MM-DD HH:mm:ss') {
    if (!date) {
        return null
    }

    const utcDate = moment.utc(date, fromFormat)
    return utcDate.clone().tz(moment.tz.guess()).format(toFormat)
}

export function dateToUtcDate(date, fromFormat = 'YYYY-MM-DD[T]HH:mm:ss', toFormat = 'YYYY-MM-DD[T]HH:mm:ss') {
    if (!date) {
        return null
    }

    return moment(date, fromFormat).clone().tz(moment.tz.guess()).utc().format(toFormat)
}

export function hasRole(role) {
    const user = getAuthUser()
    const roles = (user && user.roles) ? user.roles : {}
    const index = _.findIndex(roles, {name: role})
    return (index !== -1)
}

export function hasPermission(permission) {
    const user = getAuthUser()
    const roles = (user && user.roles) ? user.roles : []

    if (roles.length > 0) {
        const permissions = (roles[0] && roles[0].permissions !== undefined ? roles[0].permissions : [])
        return _.findIndex(permissions, {permission: permission}) !== -1
    }

    return false
}

export function hasAnyPermission(permissions) {
    const user = getAuthUser()
    const roles = (user && user.roles) ? user.roles : []
    let counter = 0

    if (roles.length > 0) {
        const permissionCollection = (roles[0] && roles[0].permissions !== undefined ? roles[0].permissions : [])

        permissions.map((permission) => {
            const hasIndex = _.findIndex(permissionCollection, {permission: permission})
            if (hasIndex >= 0) {
                counter++;
            }
        });

    }

    return (counter > 0)
}

export function hasAllPermissions(permissions) {
    const user = getAuthUser()
    const roles = (user && user.roles) ? user.roles : []
    let counter = 0;
    let counterToBeHave = (permissions ? permissions.length : -1)

    if (roles.length > 0) {
        const permissionCollection = (roles[0] && roles[0].permissions !== undefined ? roles[0].permissions : [])

        permissions.map((permission) => {
            const hasIndex = _.findIndex(permissionCollection, {permission: permission});
            if (hasIndex >= 0) {
                counter++;
            }
        });
    }

    return (counter === counterToBeHave)
}

export function logout() {
    removeStorage(`auth`);
    refresh();
}

export function formatDate(date, format = 'DD-MM-YYYY') {
    return moment(date).format(format)
}

export function slugify(text, ampersand = 'and') {
    const a = 'àáäâèéëêìíïîòóöôùúüûñçßÿỳýœæŕśńṕẃǵǹḿǘẍźḧ'
    const b = 'aaaaeeeeiiiioooouuuuncsyyyoarsnpwgnmuxzh'
    const p = new RegExp(a.split('').join('|'), 'g')

    return text.toString().toLowerCase()
        .replace(/[\s_]+/g, '-')
        .replace(p, c =>
            b.charAt(a.indexOf(c)))
        .replace(/&/g, `-${ampersand}-`)
        .replace(/[^\w-]+/g, '')
        .replace(/--+/g, '-')
        .replace(/^-+|-+$/g, '')
}

export function extractKeys( array, newarray ){
    if( !newarray ) newarray = [] ;
    if( array ) for( var i = 0 ; i < array.length ; ++i )
    {
        if( array[i].constructor.name === "Array" ) extract( array[i], newarray ) ;
        else newarray.push( array[i] ) ;
    }
    return newarray ;
}

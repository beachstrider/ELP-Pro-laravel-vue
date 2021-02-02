import axios from 'axios';
import {getAuthUser, getStorage, hasAuthUser, refresh, removeStorage} from "./Utils";

/**
 * Create an Axios Client with defaults
 */
let axiosInstance = axios.create();

axiosInstance.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
axiosInstance.defaults.headers.common['Content-Type'] = 'application/json';
axiosInstance.defaults.headers.common['Content-Language'] = localStorage.getItem('alcolmLps.settings.locale') || 'en-US';

export const client = axiosInstance;

/**
 * Request Wrapper with default success/error actions
 */
export const request = function (options) {

    if(hasAuthUser() && getAuthUser()) {
        const user = getAuthUser();
        axiosInstance.defaults.headers.common['Authorization'] = `Bearer ${user.access_token}`;
    }

    const onSuccess = function (response) {
        return response;
    }

    const onError = function (error) {
        if (error.response) {
            if(error.response.status === 401) {
                setTimeout(() => {
                    removeStorage('auth');
                    refresh();
                }, 2000);

                return false;
            }
        } else {
            // Something else happened while setting up the request
            // triggered the error
            // console.error('Error Message:', error.message);
        }

        return Promise.reject(error.response || error.message);
    }

    return axiosInstance(options)
        .then(onSuccess)
        .catch(onError);
}

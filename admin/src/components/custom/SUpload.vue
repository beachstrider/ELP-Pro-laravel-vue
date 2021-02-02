<template>
    <b-button type="button" :variant="(color ? color : 'primary')" :disabled="loader || bDisabled" :class="cssClass" :title="htmlTitle">
        <template v-if="randomId">
            <vue-dropzone
                :ref="randomId"
                :id="randomId"
                :options="dropOptions"
                :include-styling="false"
                :useCustomSlot="true"
                @vdropzone-success="dropZoneSuccess"
                @vdropzone-canceled="dropZoneCancelled"
                @vdropzone-sending="dropZoneSending"
                @vdropzone-error="dropZoneError"
                @vdropzone-processing="dropZoneProccessing"
                @vdropzone-total-upload-progress="dropZoneTotalUploadProccessing"

                @vdropzone-max-files-exceeded="dropZoneFileUploadSizeLimit"
                @vdropzone-max-files-reached="dropZoneFileUploadSizeLimit"

                @vdropzone-complete="dropZoneComplete">
                <clip-loader style="display: inline" :loading="true" color="#fff" size="12px"
                             v-if="loader"></clip-loader>
                <template v-if="html">
                    <template v-if="!loader">
                        <span v-html="title"></span>
                    </template>
                    <template v-else>
                        <i class="fa fa-spin fa-spinner"></i>
                    </template>
                </template>
                <template v-else>
                    <template v-if="!loader">
                        {{title ? title : 'Upload' }}
                    </template>
                    <template v-else>
                        Uploading
                    </template>
                </template>
            </vue-dropzone>
        </template>
    </b-button>
</template>
<script>
    import vueDropzone from "vue2-dropzone"
    import ClipLoader from 'vue-spinner/src/ClipLoader'
    import 'vue2-dropzone/dist/vue2Dropzone.min.css'
    import {getAuthUser} from "./../../util/Utils";
    import {fileSizeLimitError, serverError} from "./../../util/Notify";

    export default {
        props: ['title', 'value', 'disabled', 'uploadType', 'color', 'cssClass', 'uploader', 'html', 'thum', 'htmlTitle'],
        components: {
            ClipLoader,
            vueDropzone
        },
        data() {
            const user = ((this.uploader && this.uploader !== undefined) ? {id: this.uploader} : getAuthUser())
            let acceptedFiles = 'application/msword, application/vnd.ms-excel, application/vnd.ms-powerpoint, text/plain, application/pdf, image/*, .xlsx, .xls, .csv, .doc, .docx, .ppt, .pptx, .zip, .rar, .7zip, zip,application/octet-stream, application/zip, application/x-zip, application/x-zip-compressed'
            if (this.uploadType === 'image') {
                acceptedFiles = 'image/*'
            } else if(this.uploadType == 'excel') {
                acceptedFiles = 'application/vnd.ms-excel, .xlsx, .xls, .csv'
            }

            const URL = this.$global.baseUrl;

            return {
                bDisabled: false,
                loader: false,
                randomId: Math.random().toString(36).substring(2, 15) + Math.random().toString(36).substring(2, 15) + '-ref',
                dropOptions: {
                    url: `${URL}/api/upload`,
                    chunking: true,
                    fileParameterName: 'file',
                    chunkSize: 2048576,
                    uploadMethod: 'post',
                    maxFilesize: 1024,
                    createImageThumbnails: false,
                    thumbnailWidth: 0,
                    thumbnailHeight: 0,
                    headers: {
                        Authorization: `Bearer ${user.access_token}`,
                        'X-Content-Token': user.id
                    },
                    previewTemplate: this.template(),
                    acceptedFiles: acceptedFiles,
                },
            }
        },
        mounted() {
            // this.$emit('input', {...this.uploads, nothing: 'much'});
        },
        watch: {
            disabled: {
                immediate: true,
                handler(newVal) {
                    this.bDisabled = false

                    if (newVal) {
                        this.bDisabled = true
                    }
                }
            }
        },
        methods: {
            template: function () {
                return `<div></div>`;
            },
            dropZoneSending(file, xhr, formData) {
                if(this.thum && this.thum != false) {
                    formData.append('thum', this.thum);
                }
            },
            dropZoneSuccess(file, response) {
                // this.$emit('input', response.data)
            },
            dropZoneComplete(response) { // Finally
                if (response.status === 'success') {
                    const {xhr} = response
                    const jsoned = JSON.parse(xhr.response)
                    const {data} = jsoned

                    if (data !== undefined) {
                        this.$emit('input', data)
                    } else {
                        serverError(this.$vs)
                    }
                }
                this.$store.dispatch('removePendingRequests')
                this.loader = false;
            },
            dropZoneCancelled(response) {

            },
            dropZoneError(file, message, xhr) { // Finally
                if (xhr === undefined) {
                    if (message.startsWith('File is too big')) {
                        fileSizeLimitError(this.$notification)
                        return false;
                    } else {
                        if(this.uploadType) {
                            message = `${message} Only ${this.uploadType} support.`
                        }

                        this.$notification.error({position: 'top-right', message: 'Whoops!', description: message})
                    }
                } else {
                    serverError(this.$vs)
                }

                this.$emit('input', null)
            },
            dropZoneProccessing(file) {
                this.loader = true
                this.$store.dispatch('addPendingRequests')
            },
            dropZoneTotalUploadProccessing(totaluploadprogress, totalBytes, totalBytesSent) {
                this.loader = true
            },
            dropZoneFileUploadSizeLimit(file) {
                this.fileSizeLimitError(this.$notification)
            },
        }
    }
</script>
<style>

</style>
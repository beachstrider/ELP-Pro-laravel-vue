<template>
    <div :class="cssClass">
        <template v-if="randomId">
            <vue-dropzone
                :ref="randomId"
                :id="randomId"
                :options="dropOptions"
                :include-styling="true"
                @vdropzone-success="dropZoneSuccess"
                @vdropzone-canceled="dropZoneCancelled"
                @vdropzone-error="dropZoneError"
                @vdropzone-sending="dropZoneSending"
                @vdropzone-processing="dropZoneProccessing"
                @vdropzone-total-upload-progress="dropZoneTotalUploadProccessing"

                @vdropzone-max-files-exceeded="dropZoneFileUploadSizeLimit"
                @vdropzone-max-files-reached="dropZoneFileUploadSizeLimit"
                @vdropzone-removed-file="dropZoneRemovedFile"

                @vdropzone-complete="dropZoneComplete">
            </vue-dropzone>
        </template>
    </div>
</template>
<script>
    import vueDropzone from "vue2-dropzone"
    import ClipLoader from 'vue-spinner/src/ClipLoader'
    import 'vue2-dropzone/dist/vue2Dropzone.min.css'
    import {getAuthUser} from "./../../util/Utils";
    import {fileSizeLimitError, serverError} from "./../../util/Notify";

    export default {
        props: ['title', 'value', 'uploadType', 'cssClass', 'uploader'],
        components: {
            ClipLoader,
            vueDropzone
        },
        data() {
            const user = ((this.uploader && this.uploader !== undefined) ? {id: this.uploader} : getAuthUser())
            let acceptedFiles = 'application/msword, application/vnd.ms-excel, application/vnd.ms-powerpoint, text/plain, application/pdf, image/*, .xlsx, .xls, .doc, .docx, .ppt, .pptx, .zip, .rar, .7zip, zip,application/octet-stream, application/zip, application/x-zip, application/x-zip-compressed'
            if (this.uploadType === 'image') {
                acceptedFiles = 'image/*'
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
                    createImageThumbnails: true,
                    addRemoveLinks: true,
                    thumbnailWidth: 150,
                    thumbnailHeight: 150,
                    dictDefaultMessage: `<i class='fa fa-cloud-upload'></i> Upload Other Product Images`,
                    headers: {
                        Authorization: `Bearer ${user.access_token}`,
                        'X-Content-Token': user.id
                    },
                    acceptedFiles: acceptedFiles,
                },
                files: []
            }
        },
        methods: {
            manuallyAdd(newVal) {
                this.$refs[this.randomId].removeAllFiles(true)
                for (let i = 0; i < newVal.length; i++) {
                    if(newVal[i] !== undefined
                    && newVal[i] !== null
                    && newVal[i]['url'] !== undefined) {
                        this.$refs[this.randomId].manuallyAddFile(newVal[i], newVal[i]['url'])
                    }
                }
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
                        const values = this.value
                        values.push(data)
                        // const uniques = Object.values(
                        //     values.reduce((a, c) => {
                        //     a[c.id] = c;
                        //     return a
                        // }, {}))

                        this.$emit('input', values)
                    } else {
                        serverError(this.$notification)
                    }
                }

                this.$store.dispatch('removePendingRequests')
                this.loader = false
            },
            dropZoneCancelled(response) {

            },
            dropZoneError(file, message, xhr) { // Finally
                if (xhr === undefined) {
                    if (message.startsWith('File is too big')) {
                        fileSizeLimitError(this.$notification)
                        return false;
                    } else {
                        this.$notification.error({
                            position: 'top-right',
                            message: 'Whoops!',
                            description: message
                        })
                    }
                } else {
                    serverError(this.$notification)
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
            dropZoneRemovedFile(fileObject, error, xhr) {
                const values = this.value
                const index = _.findIndex(values, {id: fileObject.id})

                if (index >= 0) {
                    values[index] = null
                    this.$emit('input', values)
                }
            }
        }
    }
</script>
<style lang="scss">
    .dz-preview.dz-file-preview.dz-processing {
        max-width: 150px;
        max-height: 150px;
    }

    .dz-image {
        overflow-y: hidden;
        max-width: 150px;
        max-height: 150px;
    }
</style>
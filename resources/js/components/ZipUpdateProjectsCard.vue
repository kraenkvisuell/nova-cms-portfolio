<template>
    <card class="flex flex-col h-20">
        <label 
            class="
                block p-8 h-full
                text-center cursor-pointer 
                uppercase text-sm font-bold
            "
            :class="{
                'pointer-events-none' : uploading
            }"
        >
            <input
                id="zip_update_projects"
                class="form-file-input"
                type="file"
                accept=".zip"
                @change="selectFiles"
            />
            <template v-if="!uploading">
                {{ card.text }}
            </template>

            <template v-if="uploading">
                <em>{{ __('Uploading') }} ({{ progress }}%)...</em>
            </template>
        </label>


        <portal to="modals">
            <transition name="fade">
                <zip-update-projects-modal
                    v-if="modalOpen"
                    @confirm="confirmModal"
                    @close="closeModal"
                />
            </transition>
        </portal>
    </card>
</template>

<script>
import ZipUpdateProjectsModal from './ZipUpdateProjectsModal.vue';

export default {
    data: function () {
        return {
            uploading: false,
            uploadFinished: false,
            progress: 0,
            modalOpen: false,
        }
    },
    
    props: [
        'card', 'artistId'
    ],

    components: {
        ZipUpdateProjectsModal
    },

    methods: {
        selectFiles(input) {
            if ( !input.target.files.length ) return;
            
            this.uploadFile(input.target.files[0]);
            
            document.getElementById('zip_update_projects').value = null;
        },
        uploadFile(file) {
       
            this.uploading = true;
            
            let config = { 
                headers: { 'Content-Type': 'multipart/form-data' },
                onUploadProgress: (progressEvent) => {
                    this.progress = Math.round((progressEvent.loaded * 100) / progressEvent.total)
                }
            };

            let data = new FormData();
            data.append('file', file);

            Nova.request().post('/nova-vendor/nova-cms-portfolio/artists/projects-from-zip-file/'+this.card.artistId, data, config).then(r => {
                this.openModal()
                this.uploading = false
                
            }).catch(e => {
                console.log(e)
            });
        },
        openModal() {
            this.modalOpen = true;
        },
        confirmModal() {
            this.modalOpen = false;
        },
        closeModal() {
            this.modalOpen = false;
        }
    }
}
</script>

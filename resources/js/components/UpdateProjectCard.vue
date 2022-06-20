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
                <em>{{ __('Uploading') }}...</em>
            </template>
        </label>
    </card>
</template>

<script>

export default {
    data: function () {
        return {
            files: [],
            uploading: false,
            uploadFinished: false,
            count: 0,
        }
    },
    
    props: [
        'card', 'artistId'
    ],

    methods: {
        selectFiles(input) {
            if ( !input.target.files.length ) return;
            
            this.files = Object.assign({}, input.target.files);
            this.uploadFiles();
            
            document.getElementById('zip_update_projects').value = null;
        },
        uploadFile(file) {
        },
        uploadFiles() {

            this.uploading = true;
            _.forEach(this.files, function(file) {
                let config = { headers: { 'Content-Type': 'multipart/form-data' } };
                let data = new FormData();
                data.append('file', file);
                    Nova.request().post('/nova-vendor/nova-cms-portfolio/artists/projects-from-zip-file/'+this.card.artistId, data, config).then(r => {
                        this.count++;
                        if (this.count >= Object.keys(this.files).length) {
                            window.location.reload();

                        }
                    }).catch(e => {
                        this.count++;
                    });

            }.bind(this));
        }
    }
}
</script>

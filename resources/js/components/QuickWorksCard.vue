<template>
    <card class="flex flex-col h-20">
        <label 
            class="block p-8 h-full text-center cursor-pointer"
            :class="{
                'pointer-events-none' : uploading
            }"
        >
            <input
                id="quick_upload"
                class="form-file-input"
                type="file"
                multiple
                accept=".jpg,.jpeg,.png,.gif,.svg,.mp4"
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
            count: 0,
        }
    },
    
    props: [
        'card', 'slideshowId'
    ],

    methods: {
        selectFiles(input) {
            if ( !input.target.files.length ) return;
            
            this.files = Object.assign({}, input.target.files);
            this.uploadFiles();
            
            document.getElementById('quick_upload').value = null;
        },
        uploadFile(file) {
        },
        uploadFiles() {

            this.uploading = true;
            _.forEach(this.files, function(file) {
                let config = { headers: { 'Content-Type': 'multipart/form-data' } };
                let data = new FormData();
                data.append('file', file);
                data.append('folder', null);
                
                Nova.request().post('/nova-vendor/nova-cms-media/upload', data, config).then(r => {
                    Nova.request().post('/nova-vendor/nova-cms-portfolio/works/create-from-file/'+this.card.slideshowId+'/'+r.data.id+'/', data, config).then(r => {
                        this.count++;
                        if (this.count >= Object.keys(this.files).length) {
                            Nova.request().post('/nova-vendor/nova-cms-portfolio/slideshows/reorder-works/'+this.card.slideshowId);
                            window.location.reload();

                        }
                    }).catch(e => {
                        this.count++;
                    });
                    
                }).catch(e => {
                    this.count++;
                });
            }.bind(this));
        }
    }
}
</script>

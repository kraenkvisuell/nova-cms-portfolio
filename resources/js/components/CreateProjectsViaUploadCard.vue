<template>
    <card class="flex flex-col h-auto">
        <div class="p-3">
            <p class="uppercase text-80 text-sm font-bold" v-html="card.headline" />

            <p class="text-80 mt-4" v-html="card.intro" />

            <div class="flex mt-6">
                <label 
                    class="
                        block px-8 py-1 h-full
                        text-center 
                        uppercase font-bold
                        btn btn-default btn-primary
                    "
                    :class="{
                        'pointer-events-none cursor-not-allowed': uploading,
                        'cursor-pointer ': !uploading,
                    }"
                >
                    <input
                        id="upload_input"
                        class="form-file-input"
                        type="file"
                        webkitdirectory mozdirectory msdirectory odirectory directory multiple
                        @change="create"
                    />
                    <template v-if="!uploading">
                        Select folder
                    </template>

                    <template v-if="uploading">
                        uploading...
                    </template>
                </label>
            </div>

            <div 
                v-if="results.length"
                class="
                    mt-4 py-2 px-3 overflow-auto 
                    border border-dotted border-60 rounded-lg
                "
                style="max-height: 100px"
            >
                <div 
                    v-for="result in results" :key="result"
                    class="mb-3"
                    :class="{
                        'text-success': result.status == 'success',
                        'text-red-400': result.status != 'success' && result.reason != 'already exists',
                        'text-yellow-500': result.status != 'success' && result.reason == 'already exists',
                    }"
                >
                    <div class="text-sm">
                        <span v-if="result.status == 'success'" v-text="'uploaded'" /><span v-else v-text="result.reason" />:
                    </div>

                    <div>
                        <span v-text="result.filename" />
                    </div>

                    <div class="text-sm">
                        category: <span v-text="result.category" /> //
                        project: <span v-text="result.slideshow" />
                    </div>
                </div>
            </div>
        </div>
    </card>
</template>

<script>
window.axios = require('axios');

import { v4 as uuidv4 } from 'uuid';

export default {
    data: function () {
        return {
           uploading: false,
           results: [],
        }
    },
    
    props: [
        'card'
    ],
    methods: {
        create(e) {
            let files = e.target.files;

            let sortedFiles = Array.from(files);
            sortedFiles.sort((a, b) => a.webkitRelativePath.localeCompare(b.webkitRelativePath, navigator.languages[0] || navigator.language, {numeric: true, ignorePunctuation: true}))
            
            this.uploading = true;
            
            
            let uuid = uuidv4();
            let count = 0;
            
            const postFile = (file) => {
                let config = { headers: { 'Content-Type': 'multipart/form-data' } };
                let formData = new FormData();
                formData.append('file', file);
                formData.append('originalPath', file.webkitRelativePath);
                formData.append('size', file.size);
                formData.append('uuid', uuid);
                
                Nova.request()
                    .post('/nova-vendor/nova-cms-portfolio/create-project-via-upload/'+this.card.artistId, formData, config)
                    .then(response => {
                        count++;
                        
                        if (response.data.reason != 'hidden file') {
                            this.results.unshift(response.data);
                        }
                        if (count < files.length - 1) {
                            postFile(sortedFiles[count]);
                        } else {
                            window.location.reload();
                            // this.uploading = false;
                            // document.getElementById('upload_input').value = null;
                        }
                        
                    }).catch(e => {
        
                    });
            };

          
            postFile(sortedFiles[0]);
        }
    },
}
</script>

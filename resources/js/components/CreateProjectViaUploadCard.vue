<template>
    <card class="flex flex-col h-auto">
        <div class="p-3">
            <p class="uppercase text-80 text-sm font-bold" v-html="card.headline" />

            <p class="text-80 mt-4" v-html="card.intro" />

            <div class="
                mt-8
                grid grid-cols-2 gap-4
            ">
                <div>
                    <label 
                        class="text-90 block mb-1"
                        for="categoryId" v-text="card.categoryIdTitle+'...'"
                    >
                    </label>
                    <select 
                        id="categoryId"
                        v-model="categoryId"
                        class="block w-full form-control form-select"
                    >
                        <option 
                            :value="0" 
                        >
                            Select a category
                        </option>
                        <option 
                            v-for="(categoryName, categoryId) in card.categories" 
                            :value="categoryId" 
                            :key="'monday_'+categoryId"
                        >
                            {{ categoryName }}
                        </option>
                    </select>
                </div>

                <div>
                    <label 
                        class="text-90 block mb-1"
                        for="newCategory"
                        v-text="card.newCategoryTitle"
                    >
                    </label>

                    <input 
                        id="newCategory" 
                        v-model="newCategory"
                        class="w-full form-control form-input form-input-bordered"
                    />
                </div>
            </div>

            <div class="flex mt-12">
                <label 
                    class="
                        block px-8 py-1 h-full
                        text-center cursor-pointer 
                        uppercase font-bold
                        btn btn-default btn-primary
                    "
                    :class="{
                        'pointer-events-none opacity-25' : !okToSelect
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
                        Uploading...
                    </template>
                </label>
            </div>
        </div>
    </card>
</template>

<script>
window.axios = require('axios');

export default {
    data: function () {
        return {
           slideshowName: '',
           newCategory: '',
           categoryId: 0,
           uploading: false,
        }
    },
    
    props: [
        'card'
    ],
    computed: {
        okToSelect() {
            return this.categoryId || this.newCategory
        },
    },
    methods: {
        create(e) {
            var files = e.target.files;
            var relativePath = files[0].webkitRelativePath;
            var folderName = relativePath.split("/");
            //alert(folder[0]);

            this.uploading = true;
            
            console.log(files);
        }
    },
    watch: {
        categoryId: function (val, oldVal) {
            if (val) {
                this.newCategory = '';
            }
        },
        newCategory: function (val, oldVal) {
            if (val) {
                this.categoryId = 0;
            }
        },
    }
}
</script>

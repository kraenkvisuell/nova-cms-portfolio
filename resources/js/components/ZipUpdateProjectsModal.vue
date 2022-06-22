<template>
    <modal @modal-close="handleClose">
        <form
            @submit.prevent="handleConfirm"
            slot-scope="props"
            class="bg-white rounded-lg shadow-lg overflow-hidden"
            style="width: 460px"
        >
            <slot :uppercaseMode="uppercaseMode" :mode="mode">
                <div class="p-8">
                    <p class="text-80 leading-normal">
                        The file is now processed - 
                        depending on the number of folders and files this might take up to 15 minutes.<br>
                        When the upload is finished, the page will reload.<br>
                        <strong>Please leave this page open.</strong>
                    </p>
                </div>
            </slot>
        </form>
    </modal>
</template>

<script>
    export default {
        name: "ZipUpdateProjectsModal",
        methods: {
            handleClose() {
                this.$emit('close')
            },
            handleConfirm() {
                this.$emit('confirm')
            },
        },
        /**
         * Mount the component.
         */
        mounted() {
            this.$refs.confirmButton.focus()
        },
    }
</script>

<style scoped>
</style>

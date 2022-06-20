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
                        The file is processing in the background.<br>
                        Depending on the number of folders and files this might some time.<br>
                    </p>
                </div>
            </slot>

            <div class="bg-30 px-6 py-3 flex">
                <div class="ml-auto">
                    <button id="confirm-button" ref="confirmButton" data-testid="confirm-button" type="submit" class="btn btn-default btn-primary">OK</button>
                </div>
            </div>
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

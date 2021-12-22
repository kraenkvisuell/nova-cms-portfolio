<template>
    <card class="flex flex-col flex flex-col h-20">
        <a 
            class="
                block p-8 h-full text-center
                cursor-pointer no-underline
                uppercase text-sm font-bold text-90
            "
            :href="card.url"
        >
          {{ card.text }}
        </a>
    </card>
</template>

<script>

export default {
    data: function () {
        return {}
    },
    
    props: [
        'card'
    ]
}
</script>

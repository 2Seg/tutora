<script lang="ts">
import MainHeader from '@/components/shared/MainHeader.vue';

export default {
    name: "Index",
    components: { MainHeader },
}
</script>

<template>
    <section class="col-span-full">
        <MainHeader
            title="Your students"
            description="A simple list of students learning French with you."/>
    </section>

    <section class="col-span-2 bg-white">
        Section
    </section>

    <aside class="bg-amber-100">
        Aside
    </aside>
</template>

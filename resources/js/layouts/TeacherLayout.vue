<script lang="ts">
import TeacherNavigationMenu from '@/components/teacher/TeacherNavigationMenu.vue';

export default {
    name: 'TeacherLayout',
    components: { TeacherNavigationMenu },
};
</script>

<template>
    <header class="flex justify-between min-w-screen px-4 py-2">
        <div>
            <h1 style="">tutora</h1>
        </div>
        <div>Profile</div>
    </header>

    <div class="px-4 py-2">
        <TeacherNavigationMenu></TeacherNavigationMenu>
    </div>

    <main class="flex block-screen justify-between bg-amber-300">
        <slot/>
    </main>
</template>

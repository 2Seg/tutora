<script lang="ts">
import TeacherNavigationMenu from '@/components/teacher/TeacherNavigationMenu.vue';
import AppLogo from '@/components/shared/AppLogo.vue';
import ProfileMenu from '@/components/shared/ProfileMenu.vue';

export default {
    name: 'TeacherLayout',
    components: { ProfileMenu, AppLogo, TeacherNavigationMenu },
};
</script>

<template>
    <header class="flex justify-between min-w-screen px-6 pt-3 pb-1">
        <AppLogo></AppLogo>
        <ProfileMenu></ProfileMenu>
    </header>

    <div class="px-6 pt-1 pb-2">
        <TeacherNavigationMenu></TeacherNavigationMenu>
    </div>

    <main class="grid grid-cols-3 px-2 py-3 gap-6">
        <slot/>
    </main>
</template>

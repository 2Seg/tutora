<script lang="ts">
import TeacherNavigationMenu from '@/components/teacher/TeacherNavigationMenu.vue';
import AppLogo from '@/components/shared/AppLogo.vue';
import ProfileMenu from '@/components/shared/ProfileMenu.vue';
import MainHeader from '@/components/shared/MainHeader.vue';
import { useTeacherNavigation } from '@/composables/useTeacherNavigation';

export default {
    name: 'TeacherLayout',
    components: { MainHeader, ProfileMenu, AppLogo, TeacherNavigationMenu },
    setup() {
        const { current } = useTeacherNavigation();

        return { current };
    },
};
</script>

<template>
    <div class="flex h-dvh flex-col">
        <header class="flex justify-between min-w-screen px-6 pt-3 pb-1">
            <AppLogo></AppLogo>
            <ProfileMenu></ProfileMenu>
        </header>

        <div class="px-6 py-2">
            <TeacherNavigationMenu></TeacherNavigationMenu>
        </div>

        <section class="px-2 py-2" v-if="current">
            <MainHeader
                :title="current.title"
                :description="current.description"
            />
        </section>

        <main class="grid flex-1 grid-cols-3 gap-6 px-6 pb-6">
            <slot/>
        </main>
    </div>
</template>

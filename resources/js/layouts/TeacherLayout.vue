<script lang="ts" setup>
import UserNavigationMenu from '@/components/user/UserNavigationMenu.vue';
import AppLogo from '@/components/shared/AppLogo.vue';
import ProfileMenu from '@/components/user/UserMenu.vue';
import MainHeader from '@/components/shared/MainHeader.vue';
import { useUserNavigation } from '@/composables/navigation/useUserNavigation';
import { Toaster } from '@/components/ui/sonner';

const { current } = useUserNavigation();
</script>

<template>
    <div class="flex h-dvh flex-col">
        <header class="flex justify-between min-w-screen px-6 pt-3 pb-1">
            <AppLogo></AppLogo>
            <ProfileMenu></ProfileMenu>
        </header>

        <div class="px-6 py-2">
            <UserNavigationMenu></UserNavigationMenu>
        </div>

        <section v-if="current" class="px-2 py-2">
            <MainHeader :description="current.description" :title="current.title" />
        </section>

        <main class="flex min-h-0 flex-1 flex-col overflow-y-auto px-6 pb-6">
            <slot />
        </main>

        <Toaster close-button close-button-position="top-right" position="top-center" />
    </div>
</template>

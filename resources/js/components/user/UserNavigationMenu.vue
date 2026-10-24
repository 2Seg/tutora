<script lang="ts" setup>
import {
    NavigationMenu,
    NavigationMenuItem,
    NavigationMenuLink,
    NavigationMenuList,
} from '@/components/ui/navigation-menu';
import { Link } from '@inertiajs/vue3';
import { useTeacherNavigation } from '@/composables/useTeacherNavigation';

const { items, isActive } = useTeacherNavigation();
</script>

<template>
    <NavigationMenu>
        <NavigationMenuList>
            <NavigationMenuItem v-for="item in items" :key="item.href">
                <NavigationMenuLink :active="isActive(item)" as-child>
                    <Link
                        :href="item.href"
                        class="flex flex-row items-center gap-2"
                    >
                        <component :is="item.icon" />
                        {{ item.label }}
                    </Link>
                </NavigationMenuLink>
            </NavigationMenuItem>
        </NavigationMenuList>
    </NavigationMenu>
</template>

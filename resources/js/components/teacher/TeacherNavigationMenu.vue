<script>
import {
    NavigationMenu,
    NavigationMenuItem,
    NavigationMenuLink,
    NavigationMenuList,
} from '@/components/ui/navigation-menu';
import { Link } from '@inertiajs/vue3';
import { useTeacherNavigation } from '@/composables/useTeacherNavigation';

export default {
    name: 'TeacherNavigationMenu',
    components: {
        Link,
        NavigationMenuList,
        NavigationMenuLink,
        NavigationMenuItem,
        NavigationMenu,
    },
    setup() {
        const { items, isActive } = useTeacherNavigation();

        return { items, isActive };
    },
};
</script>

<template>
    <NavigationMenu>
        <NavigationMenuList>
            <NavigationMenuItem v-for="item in items" :key="item.href">
                <NavigationMenuLink as-child :active="isActive(item)">
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

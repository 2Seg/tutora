<script lang="ts" setup>
import {
    NavigationMenu,
    NavigationMenuItem,
    NavigationMenuLink,
    NavigationMenuList,
    NavigationMenuTrigger,
    NavigationMenuContent,
} from '@/components/ui/navigation-menu';
import { Separator } from '@/components/ui/separator';
import UserAvatar from '@/components/user/UserAvatar.vue';
import { computed } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';
import { useUserMenu } from '@/composables/navigation/useUserMenu';

const page = usePage();
const user = computed(() => page.props.auth.user);
const items = useUserMenu();
</script>

<template>
    <NavigationMenu>
        <NavigationMenuList>
            <NavigationMenuItem>
                <NavigationMenuTrigger class="flex gap-3 h-10">
                    <UserAvatar :user="user" />
                    <span class="font-bold">{{ user.fullName }}</span>
                </NavigationMenuTrigger>
                <NavigationMenuContent>
                    <ul class="flex flex-col justify-evenly w-43 h-30">
                        <template v-for="(item, index) in items" :key="item.key">
                            <Separator v-if="index === items.length - 1" />
                            <NavigationMenuLink as-child>
                                <Link :href="item.href" class="flex items-center gap-2 w-full cursor-pointer">
                                    <component :is="item.icon" />
                                    {{ item.label }}
                                </Link>
                            </NavigationMenuLink>
                        </template>
                    </ul>
                </NavigationMenuContent>
            </NavigationMenuItem>
        </NavigationMenuList>
    </NavigationMenu>
</template>

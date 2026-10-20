<script setup lang="ts">
import {
    NavigationMenu,
    NavigationMenuItem,
    NavigationMenuLink,
    NavigationMenuList,
    NavigationMenuTrigger,
    NavigationMenuContent,
} from '@/components/ui/navigation-menu';
import { Settings, User as UserIcon, LogOut } from '@lucide/vue';
import { Separator } from '@/components/ui/separator';
import UserAvatar from '@/components/users/UserAvatar.vue';
import { User } from '@/types/User';
import { computed } from 'vue';
import { getFullName } from '@/utils/user';
import { Link } from '@inertiajs/vue3';

const user: User = {
    id: 1,
    firstName: 'Naelle',
    lastName: 'Furlan',
    email: 'test@gmail.com',
};
const fullName = computed(() => getFullName(user));
</script>

<template>
    <NavigationMenu>
        <NavigationMenuList>
            <NavigationMenuItem>
                <NavigationMenuTrigger class="flex gap-3 h-10">
                    <UserAvatar :user="user" />
                    <span class="font-bold">{{ fullName }}</span>
                </NavigationMenuTrigger>
                <NavigationMenuContent>
                    <ul class="flex flex-col justify-evenly w-43 h-30">
                        <NavigationMenuLink>
                            <UserIcon />
                            My profile
                        </NavigationMenuLink>
                        <NavigationMenuLink>
                            <Settings />
                            Parameters
                        </NavigationMenuLink>
                        <Separator />
                        <NavigationMenuLink as-child>
                            <Link
                                href="/logout"
                                method="post"
                                as="button"
                                class="flex items-center gap-2 w-full cursor-pointer"
                            >
                                <LogOut/>
                                Log out
                            </Link>
                        </NavigationMenuLink>
                    </ul>
                </NavigationMenuContent>
            </NavigationMenuItem>
        </NavigationMenuList>
    </NavigationMenu>
</template>

<script lang="ts" setup>
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
import UserAvatar from '@/components/user/UserAvatar.vue';
import { computed } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';

const page = usePage();
const user = computed(() => page.props.auth.user);
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
                                as="button"
                                class="flex items-center gap-2 w-full cursor-pointer"
                                href="/logout"
                                method="post"
                            >
                                <LogOut />
                                Log out
                            </Link>
                        </NavigationMenuLink>
                    </ul>
                </NavigationMenuContent>
            </NavigationMenuItem>
        </NavigationMenuList>
    </NavigationMenu>
</template>

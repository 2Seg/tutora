<script lang="ts">
import {
    NavigationMenu,
    NavigationMenuItem,
    NavigationMenuLink,
    NavigationMenuList,
    NavigationMenuTrigger,
    NavigationMenuContent,
} from '@/components/ui/navigation-menu';
import { Settings, User, LogOut } from '@lucide/vue';
import { Separator } from '@/components/ui/separator';
import UserAvatar from '@/components/shared/UserAvatar.vue';

export default {
    name: 'ProfileMenu',
    components: {
        LogOut,
        NavigationMenu,
        NavigationMenuContent,
        NavigationMenuItem,
        NavigationMenuLink,
        NavigationMenuList,
        NavigationMenuTrigger,
        Separator,
        Settings,
        User,
        UserAvatar,
    },
};
</script>

<template>
    <NavigationMenu>
        <NavigationMenuList>
            <NavigationMenuItem>
                <NavigationMenuTrigger class="flex gap-3 h-10">
                    <UserAvatar/>
                    Naëlle Furlan
                </NavigationMenuTrigger>
                <NavigationMenuContent>
                    <ul class="flex flex-col justify-evenly w-43 h-30">
                        <NavigationMenuLink>
                            <User/>
                            My profile
                        </NavigationMenuLink>
                        <NavigationMenuLink>
                            <Settings/>
                            Parameters
                        </NavigationMenuLink>
                        <Separator/>
                        <NavigationMenuLink>
                            <LogOut/>
                            Log out
                        </NavigationMenuLink>
                    </ul>
                </NavigationMenuContent>
            </NavigationMenuItem>
        </NavigationMenuList>
    </NavigationMenu>
</template>

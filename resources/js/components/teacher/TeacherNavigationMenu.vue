<script>
import {
    NavigationMenu,
    NavigationMenuItem,
    NavigationMenuLink,
    NavigationMenuList,
} from '@/components/ui/navigation-menu';
import {
    CalendarDays,
    ChartColumn,
    CircleDollarSign,
    House,
    Mails,
    Users,
} from '@lucide/vue';

export default {
    name: 'TeacherNavigationMenu',
    components: {
        NavigationMenuList,
        NavigationMenuLink,
        NavigationMenuItem,
        NavigationMenu,
        House,
        Users,
        CalendarDays,
        Mails,
        CircleDollarSign,
        ChartColumn,
    }
};
</script>

<template>
    <NavigationMenu>
        <NavigationMenuList>
            <NavigationMenuItem>
                <NavigationMenuLink>
                    <House/>
                    Home
                </NavigationMenuLink>
            </NavigationMenuItem>
            <NavigationMenuItem>
                <NavigationMenuLink>
                    <Users/>
                    Student
                </NavigationMenuLink>
            </NavigationMenuItem>
            <NavigationMenuItem>
                <NavigationMenuLink>
                    <CalendarDays/>
                    Calendar
                </NavigationMenuLink>
            </NavigationMenuItem>
            <NavigationMenuItem>
                <NavigationMenuLink>
                    <Mails/>
                    Messaging
                </NavigationMenuLink>
            </NavigationMenuItem>
            <NavigationMenuItem>
                <NavigationMenuLink>
                    <CircleDollarSign/>
                    Payments
                </NavigationMenuLink>
            </NavigationMenuItem>
            <NavigationMenuItem>
                <NavigationMenuLink>
                    <ChartColumn/>
                    Statistics
                </NavigationMenuLink>
            </NavigationMenuItem>
        </NavigationMenuList>
    </NavigationMenu>
</template>

<script setup lang="ts">
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Student } from '@/types/Student';
import UserAvatar from '@/components/users/UserAvatar.vue';
import { User } from '@/types/User';
import { computed } from 'vue';
import { getFullName } from '@/utils/user';
import { Item, ItemActions, ItemContent, ItemDescription, ItemTitle } from '@/components/ui/item';
import { Button } from '@/components/ui/button';
import ItemHeader from '@/components/ui/item/ItemHeader.vue';
import ItemMedia from '@/components/ui/item/ItemMedia.vue';

const props = defineProps<{ student: Student }>();
const fullName = computed(
    () => getFullName(props.student),
);
</script>

<template>
    <Card class="gap-4 border-0 bg-secondary shadow-none">
        <CardHeader class="flex flex-row items-center gap-4">
            <UserAvatar class="size-16" :user="student as User" />
            <h1>{{ fullName }}</h1>
        </CardHeader>

        <CardContent class="flex flex-col gap-4">
            <Item class="flex flex-col items-start bg-primary-foreground">
                <ItemContent class="gap-4">
                    <div>
                        <p class="text-xs uppercase text-muted-foreground">Email</p>
                        <p class="text-sm">{{ student.email }}</p>
                    </div>
                    <div>
                        <p class="text-xs uppercase text-muted-foreground">Phone</p>
                        <p class="text-sm">{{ student.phone }}</p>
                    </div>
                </ItemContent>
            </Item>

            <Item class="rounded-2xl border-0 bg-primary-foreground">
                <ItemHeader>
                    <h4 class="font-serif text-lg">Next lesson</h4>
                </ItemHeader>

                <ItemMedia
                    class="size-14 flex-col gap-0 rounded-xl bg-secondary text-primary"
                >
                    <span class="text-[10px] font-semibold uppercase">Oct</span>
                    <span class="font-serif text-xl leading-none">14</span>
                </ItemMedia>

                <ItemContent>
                    <ItemTitle>French conversation</ItemTitle>
                    <ItemDescription>Wednesday · 16:00-17:00</ItemDescription>
                </ItemContent>

                <ItemActions>
                    <Button
                        variant="ghost"
                        size="sm"
                        class="font-semibold text-primary cursor-pointer"
                    >
                        View calendar
                    </Button>
                </ItemActions>
            </Item>
        </CardContent>
    </Card>
</template>

<script setup lang="ts">
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Student } from '@/types/Student';
import UserAvatar from '@/components/users/UserAvatar.vue';
import { User } from '@/types/User';
import { computed } from 'vue';
import { getFullName } from '@/utils/user';
import { Item, ItemContent } from '@/components/ui/item';

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
        </CardContent>
    </Card>
</template>

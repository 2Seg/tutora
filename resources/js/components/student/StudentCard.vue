<script lang="ts" setup>
import { Card, CardContent, CardHeader } from '@/components/ui/card';
import { Student } from '@/types/Student';
import UserAvatar from '@/components/user/UserAvatar.vue';
import { User } from '@/types/User';
import {
    Item,
    ItemActions,
    ItemContent,
    ItemDescription,
    ItemTitle,
} from '@/components/ui/item';
import { Button } from '@/components/ui/button';
import ItemHeader from '@/components/ui/item/ItemHeader.vue';
import ItemMedia from '@/components/ui/item/ItemMedia.vue';

const props = defineProps<{ student: Student }>();
</script>

<template>
    <Card class="gap-4 border-0 bg-secondary shadow-none min-h-full">
        <CardHeader class="flex flex-row items-center gap-4">
            <UserAvatar :user="student as User" class="size-16" />
            <h1>{{ student.fullName }}</h1>
        </CardHeader>

        <CardContent class="flex flex-col gap-4">
            <Item class="flex flex-col items-start bg-primary-foreground">
                <ItemContent class="gap-4">
                    <div>
                        <p class="text-xs uppercase text-muted-foreground">
                            {{ $t('students.card.email') }}
                        </p>
                        <p class="text-sm">{{ student.email }}</p>
                    </div>
                    <div>
                        <p class="text-xs uppercase text-muted-foreground">
                            {{ $t('students.card.phone') }}
                        </p>
                        <p class="text-sm">{{ student.phone }}</p>
                    </div>
                </ItemContent>
            </Item>

            <Item class="rounded-2xl border-0 bg-primary-foreground">
                <ItemHeader>
                    <h4 class="font-serif text-lg">
                        {{ $t('students.card.next_lesson') }}
                    </h4>
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
                        class="font-semibold text-primary cursor-pointer"
                        size="sm"
                        variant="ghost"
                    >
                        {{ $t('students.card.view_calendar') }}
                    </Button>
                </ItemActions>
            </Item>
        </CardContent>
    </Card>
</template>

<script setup lang="ts">
import { Student } from '@/types/Student';
import { cn } from '@/lib/utils';
import SearchInput from '@/components/shared/SearchInput.vue';
import UserAvatar from '@/components/users/UserAvatar.vue';
import { getFullName } from '@/utils/user';

defineProps<{ students: Student[], selectedStudentId: number | null, class?: string }>();
defineEmits<{ select: [id: number] }>();
</script>

<template>
    <nav :class="cn('flex flex-col gap-3', $props.class)">
        <SearchInput class="shrink-0" placeholder="Search mesages..."/>

        <ul class="bg-card rounded-2xl p-2 min-h-0 flex-1 space-y-1 overflow-y-auto">
            <li v-for="student in students" :key="student.id">
                <div
                    :class="cn(
                        'flex w-full items-center gap-3 rounded-lg p-2 text-left hover:bg-accent cursor-pointer',
                        student.id === selectedStudentId && 'bg-accent/50',
                    )"
                    @click="$emit('select', student.id)"
                >
                    <UserAvatar :user="student" class="size-10"/>

                    <div class="flex flex-col min-w-0">
                        <span class="truncate text-sm font-medium">{{ getFullName(student) }}</span>
                        <span class="truncate text-xs text-muted-foreground">Preview of last message...</span>
                    </div>
                </div>
            </li>
        </ul>
    </nav>
</template>

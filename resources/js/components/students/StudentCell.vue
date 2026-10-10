<!-- resources/js/components/students/StudentCell.vue -->
<script setup lang="ts">
import { computed } from 'vue';
import { Avatar, AvatarFallback, AvatarImage } from '@/components/ui/avatar';
import type { Student } from '@/types/Student';

const props = defineProps<{ student: Student }>();

const fullName = computed(
    () => `${props.student.firstName} ${props.student.lastName}`,
);
const initials = computed(() =>
    `${props.student.firstName[0] ?? ''}${props.student.lastName[0] ?? ''}`.toUpperCase(),
);
</script>

<template>
    <div class="flex items-center gap-4">
        <Avatar class="size-10">
            <AvatarImage :src="student.avatarImg" :alt="fullName" />
            <AvatarFallback class="bg-primary/10 font-semibold text-primary">
                {{ initials }}
            </AvatarFallback>
        </Avatar>
        <div class="min-w-0">
            <p class="font-semibold leading-tight">{{ fullName }}</p>
            <p class="text-sm text-muted-foreground">{{ student.email }}</p>
        </div>
    </div>
</template>

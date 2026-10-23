<script lang="ts" setup>
import { Student } from '@/types/Student';
import UserAvatar from '@/components/user/UserAvatar.vue';

const props = defineProps<{ student: Student }>();
</script>

<template>
    <div class="flex items-center gap-4">
        <UserAvatar :user="student" class="size-10" />
        <div class="min-w-0">
            <p class="font-semibold leading-tight">{{ student.fullName }}</p>
            <p class="text-sm text-muted-foreground">{{ student.email }}</p>
        </div>
    </div>
</template>

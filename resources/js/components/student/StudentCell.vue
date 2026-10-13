<script setup lang="ts">
import { computed } from 'vue';
import { Avatar, AvatarFallback, AvatarImage } from '@/components/ui/avatar';
import { Student } from '@/types/Student';
import UserAvatar from '@/components/users/UserAvatar.vue';
import { getFullName, getInitials } from '@/utils/user';

const props = defineProps<{ student: Student }>();

const fullName = computed(() => getFullName(props.student));
</script>

<template>
    <div class="flex items-center gap-4">
        <UserAvatar class="size-10" :user="student" />
        <div class="min-w-0">
            <p class="font-semibold leading-tight">{{ fullName }}</p>
            <p class="text-sm text-muted-foreground">{{ student.email }}</p>
        </div>
    </div>
</template>

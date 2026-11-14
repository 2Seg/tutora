<script lang="ts" setup>
import { Avatar, AvatarFallback, AvatarImage } from '@/components/ui/avatar';
import { StudentSummary } from '@/types/Student';

const props = defineProps<{ user: StudentSummary }>();
</script>

<template>
    <Avatar class="size-8">
        <AvatarImage :alt="props.user.fullName" :src="user.avatarImg ?? ''" />
        <AvatarFallback class="bg-primary/10 font-semibold text-primary">
            {{ props.user.initials }}
        </AvatarFallback>
    </Avatar>
</template>

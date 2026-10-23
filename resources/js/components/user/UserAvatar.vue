<script setup lang="ts">
import { Avatar, AvatarFallback, AvatarImage } from '@/components/ui/avatar';
import { User } from '@/types/User';

const props = defineProps<{ user: User }>();
</script>

<template>
    <Avatar class="size-8">
        <AvatarImage :src="user.avatarImg ?? ''" :alt="props.user.fullName" />
        <AvatarFallback class="bg-primary/10 font-semibold text-primary">
            {{ props.user.initials }}
        </AvatarFallback>
    </Avatar>
</template>

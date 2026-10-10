<script setup lang="ts">
import { Avatar, AvatarFallback, AvatarImage } from '@/components/ui/avatar';
import { User } from '@/types/User';
import { computed } from 'vue';

const props = defineProps<{ user: User }>();

const fullName = computed(
    () => `${props.user.firstName} ${props.user.lastName}`,
);
const initials = computed(() =>
    `${props.user.firstName[0] ?? ''}${props.user.lastName[0] ?? ''}`.toUpperCase(),
);
</script>

<template>
    <Avatar class="size-8">
        <AvatarImage :src="user.avatarImg ?? ''" :alt="fullName" />
        <AvatarFallback class="bg-primary/10 font-semibold text-primary">
            {{ initials }}
        </AvatarFallback>
    </Avatar>
</template>

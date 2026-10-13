<script setup lang="ts">
import { Avatar, AvatarFallback, AvatarImage } from '@/components/ui/avatar';
import { User } from '@/types/User';
import { computed } from 'vue';
import { getFullName, getInitials } from '@/utils/user';

const props = defineProps<{ user: User }>();

const fullName = computed(() => getFullName(props.user));
const initials = computed(() => getInitials(props.user));
</script>

<template>
    <Avatar class="size-8">
        <AvatarImage :src="user.avatarImg ?? ''" :alt="fullName" />
        <AvatarFallback class="bg-primary/10 font-semibold text-primary">
            {{ initials }}
        </AvatarFallback>
    </Avatar>
</template>

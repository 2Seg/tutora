<script lang="ts" setup>
import { Avatar, AvatarFallback, AvatarImage } from '@/components/ui/avatar';
import { User } from '@/types/User';

const props = defineProps<{ user: Pick<User, 'fullName' | 'initials' | 'avatarImg'> }>();
</script>

<template>
    <Avatar class="size-8">
        <AvatarImage :alt="props.user.fullName" :src="user.avatarImg ?? ''" />
        <AvatarFallback class="bg-primary/10 font-semibold text-primary">
            {{ props.user.initials }}
        </AvatarFallback>
    </Avatar>
</template>

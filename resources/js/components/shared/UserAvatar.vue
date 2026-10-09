<script lang="ts">
import { Avatar, AvatarFallback, AvatarImage } from '@/components/ui/avatar';

export default {
    name: 'UserAvatar',
    components: { AvatarFallback, AvatarImage, Avatar },
};
</script>

<template>
    <Avatar>
        <AvatarImage src="#"/>
        <AvatarFallback>NF</AvatarFallback>
    </Avatar>
</template>

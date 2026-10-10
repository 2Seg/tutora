<script lang="ts">
import { Item, ItemActions, ItemDescription, ItemTitle } from '@/components/ui/item';

export default {
    name: 'MainHeader',
    components: { ItemActions, ItemDescription, ItemTitle, Item },
    props: {
        title: { type: String, default: '' },
        description: { type: String, default: '' },
    },
};
</script>

<template>
    <Item class="flex flex-col items-start">
        <ItemTitle>
            <h1>{{ title }}</h1>
        </ItemTitle>
        <ItemDescription>
            <p class="text-primary">{{ description }}</p>
        </ItemDescription>
    </Item>
</template>

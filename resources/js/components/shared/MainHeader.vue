<script setup lang="ts">
import {
    Item,
    ItemActions,
    ItemDescription,
    ItemTitle,
} from '@/components/ui/item';

withDefaults(
    defineProps<{
        title: string,
        description: string,
    }>(),
    {
        title: '',
        description: '',
    }
);
</script>

<template>
    <Item class="flex flex-col items-start py-2 gap-1">
        <ItemTitle>
            <h1>{{ title }}</h1>
        </ItemTitle>
        <ItemDescription>
            <p class="text-primary">{{ description }}</p>
        </ItemDescription>
    </Item>
</template>

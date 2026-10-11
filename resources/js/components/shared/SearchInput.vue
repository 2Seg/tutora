<script setup lang="ts">
import { Search } from '@lucide/vue';
import { Input } from '@/components/ui/input';

const props = withDefaults(
    defineProps<{
        placeholder?: string,
        id?: string,
    }>(),
    { placeholder: 'Search...' },
);

const model = defineModel<string>({ default: '' });
</script>

<template>
    <div class="relative w-full max-w-sm items-center">
        <Input
            :id="id"
            class="pl-10"
            :placeholder="placeholder"
            :model-value="model"
            @update:model-value="model = String($event)"
        />
        <span class="absolute inset-s-0 inset-y-0 flex items-center justify-center px-2">
            <Search class="size-5 text-muted-foreground" />
        </span>
    </div>
</template>

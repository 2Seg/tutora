<script setup lang="ts">
import { SendHorizontal } from '@lucide/vue';
import {
    InputGroup,
    InputGroupAddon,
    InputGroupButton,
    InputGroupTextarea,
} from '@/components/ui/input-group';
import { ref } from 'vue';

const emit = defineEmits<{ send: [body: string] }>();

const body = ref('');

function send() {
    const trimmed = body.value.trim();
    if (!trimmed) return;

    emit('send', trimmed);
    body.value = '';
}
</script>

<template>
    <InputGroup>
        <InputGroupTextarea
            v-model="body"
            rows="1"
            placeholder="Write a message..."
            class="max-h-40 min-h-0"
            @keydown.enter.exact.prevent="send"
        />
        <InputGroupAddon align="block-end">
            <InputGroupButton
                class="ml-auto"
                variant="default"
                size="icon-xs"
                :disabled="!body.trim()"
                @click="send"
            >
                <SendHorizontal />
            </InputGroupButton>
        </InputGroupAddon>
    </InputGroup>
</template>

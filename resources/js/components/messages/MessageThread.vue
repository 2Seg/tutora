<script setup lang="ts">
import { Message } from '@/types/Message';
import { computed } from 'vue';
import { cn } from '@/lib/utils';

const props = defineProps<{
    messages: Message[];
    authUserId: number;
    class?: string;
}>();
const messagesReversed = computed(() => [...props.messages].reverse());
</script>

<template>
    <section :class="cn('flex flex-col rounded-xl bg-card', props.class)">
        <div
            class="flex min-h-0 flex-1 flex-col-reverse gap-2 overflow-y-auto p-4"
        >
            <div
                v-for="message in messagesReversed"
                :key="message.id"
                :class="
                    cn(
                        'flex',
                        message.fromUserId === authUserId
                            ? 'justify-end'
                            : 'justify-start',
                    )
                "
            >
                <p
                    :class="
                        cn(
                            'max-w-[70%] rounded-xl px-3 py-2 text-sm',
                            message.fromUserId === authUserId
                                ? 'bg-primary/15'
                                : 'bg-muted',
                        )
                    "
                >
                    {{ message.body }}
                </p>
            </div>
        </div>
    </section>
</template>

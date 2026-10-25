<script lang="ts" setup>
import type { PaginationNextProps } from 'reka-ui';
import type { HTMLAttributes } from 'vue';
import type { ButtonVariants } from '@/components/ui/button';
import { ChevronRightIcon } from '@lucide/vue';
import { reactiveOmit } from '@vueuse/core';
import { cn } from 'cn';
import { PaginationNext, useForwardProps } from 'reka-ui';
import { buttonVariants } from '@/components/ui/button';

const props = withDefaults(
    defineProps<
        PaginationNextProps & {
            size?: ButtonVariants['size'];
            class?: HTMLAttributes['class'];
        }
    >(),
    {
        size: 'sm',
    },
);

const delegatedProps = reactiveOmit(props, 'class', 'size');
const forwarded = useForwardProps(delegatedProps);
</script>

<template>
    <PaginationNext
        :class="cn(buttonVariants({ variant: 'ghost', size }), 'gap-1 px-2.5 sm:pr-2.5', props.class)"
        data-slot="pagination-next"
        v-bind="forwarded"
    >
        <slot>
            <ChevronRightIcon />
        </slot>
    </PaginationNext>
</template>

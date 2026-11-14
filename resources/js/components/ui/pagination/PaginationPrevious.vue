<script lang="ts" setup>
import type { PaginationPrevProps } from 'reka-ui';
import type { HTMLAttributes } from 'vue';
import type { ButtonVariants } from '@/components/ui/button';
import { ChevronLeftIcon } from '@lucide/vue';
import { reactiveOmit } from '@vueuse/core';
import { PaginationPrev, useForwardProps } from 'reka-ui';
import { buttonVariants } from '@/components/ui/button';
import { cn } from '@/lib/utils';

const props = withDefaults(
    defineProps<
        PaginationPrevProps & {
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
    <PaginationPrev
        :class="cn(buttonVariants({ variant: 'ghost', size }), 'gap-1 px-2.5 sm:pr-2.5', props.class)"
        data-slot="pagination-previous"
        v-bind="forwarded"
    >
        <slot>
            <ChevronLeftIcon />
        </slot>
    </PaginationPrev>
</template>

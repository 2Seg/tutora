<script lang="ts" setup>
import type { PaginationListItemProps } from 'reka-ui';
import type { HTMLAttributes } from 'vue';
import type { ButtonVariants } from '@/components/ui/button';
import { reactiveOmit } from '@vueuse/core';
import { PaginationListItem } from 'reka-ui';
import { buttonVariants } from '@/components/ui/button';
import { cn } from '@/lib/utils';

const props = withDefaults(
    defineProps<
        PaginationListItemProps & {
            size?: ButtonVariants['size'];
            class?: HTMLAttributes['class'];
            isActive?: boolean;
        }
    >(),
    {
        size: 'sm',
    },
);

const delegatedProps = reactiveOmit(props, 'class', 'size', 'isActive');
</script>

<template>
    <PaginationListItem
        :class="
            cn(
                buttonVariants({ variant: 'ghost', size }),
                'text-muted-foreground',
                isActive && 'bg-accent font-semibold text-primary',
                props.class,
            )
        "
        data-slot="pagination-item"
        v-bind="delegatedProps"
    >
        <slot />
    </PaginationListItem>
</template>

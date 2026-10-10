<script setup lang="ts">
import type { NavigationMenuLinkEmits, NavigationMenuLinkProps } from 'reka-ui';
import type { HTMLAttributes } from 'vue';
import { reactiveOmit } from '@vueuse/core';
import { NavigationMenuLink, useForwardPropsEmits } from 'reka-ui';
import { cn } from '@/lib/utils';
import { Link } from '@inertiajs/vue3';

const props = defineProps<
    NavigationMenuLinkProps & { class?: HTMLAttributes['class'] }
>();
const emits = defineEmits<NavigationMenuLinkEmits>();

const delegatedProps = reactiveOmit(props, 'class');
const forwarded = useForwardPropsEmits(delegatedProps, emits);
</script>

<template>
    <NavigationMenuLink
        data-slot="navigation-menu-link"
        v-bind="forwarded"
        :class="
            cn(
                `data-active:focus:bg-accent data-active:hover:bg-accent data-active:bg-accent/50 data-active:text-accent-foreground hover:bg-accent hover:text-accent-foreground focus:bg-accent focus:text-accent-foreground ring-ring/10 dark:ring-ring/20 dark:outline-ring/40 outline-ring/50 flex flex-col items-start justify-center gap-2 rounded-sm p-2 text-sm transition-[color,box-shadow] focus-visible:ring-4 focus-visible:outline-1 [&_svg:not([class*='size-'])]:size-4`,
                props.class,
            )
        "
    >
        <Link class="flex flex-row items-center justify-center gap-2">
            <slot />
        </Link>
    </NavigationMenuLink>
</template>

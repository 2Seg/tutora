<script lang="ts" setup>
import { Form } from '@inertiajs/vue3';
import { trans } from 'laravel-vue-i18n';
import { ref } from 'vue';
import {
    AlertDialog,
    AlertDialogCancel,
    AlertDialogContent,
    AlertDialogFooter,
    AlertDialogHeader,
    AlertDialogTitle,
    AlertDialogTrigger,
} from '@/components/ui/alert-dialog';
import { Button } from '@/components/ui/button';
import type { RouteDefinition } from '@/wayfinder';

withDefaults(
    defineProps<{
        action: RouteDefinition<'get' | 'post' | 'put' | 'patch' | 'delete'>;
        title: string;
        destructive?: boolean;
    }>(),
    { destructive: false },
);

const emit = defineEmits<{ success: [] }>();

const isOpen = ref(false);

function handleSuccess() {
    isOpen.value = false;
    emit('success');
}
</script>

<template>
    <AlertDialog v-model:open="isOpen">
        <AlertDialogTrigger as-child>
            <slot />
        </AlertDialogTrigger>
        <AlertDialogContent :aria-describedby="undefined">
            <AlertDialogHeader>
                <AlertDialogTitle>{{ title }}</AlertDialogTitle>
            </AlertDialogHeader>
            <Form
                #default="{ processing }"
                :action="action"
                :options="{ preserveScroll: true }"
                @success="handleSuccess"
            >
                <AlertDialogFooter>
                    <AlertDialogCancel type="button">{{ trans('common.dialog.cancel') }}</AlertDialogCancel>
                    <Button :disabled="processing" :variant="destructive ? 'destructive' : 'default'" type="submit">
                        {{ trans('common.dialog.confirm') }}
                    </Button>
                </AlertDialogFooter>
            </Form>
        </AlertDialogContent>
    </AlertDialog>
</template>

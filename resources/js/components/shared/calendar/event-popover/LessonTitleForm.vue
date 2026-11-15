<script lang="ts" setup>
import { ArrowRight, X } from '@lucide/vue';
import { Form } from '@inertiajs/vue3';
import { trans } from 'laravel-vue-i18n';
import { update } from '@/routes/teacher/calendar/events';
import type { Lesson } from '@/types/calendar';
import { InputGroup, InputGroupAddon, InputGroupButton, InputGroupInput } from '@/components/ui/input-group';

defineProps<{
    event: Lesson;
}>();

const emit = defineEmits<{
    done: [];
}>();
</script>

<template>
    <Form
        #default="{ errors, processing }"
        :action="update(Number(event.id))"
        :options="{ preserveScroll: true }"
        class="flex flex-col gap-2 pr-8"
        @success="emit('done')"
    >
        <InputGroup>
            <InputGroupInput
                :aria-invalid="!!errors.title"
                :aria-label="trans('calendar.event.popover.title.label')"
                :default-value="event.title ?? ''"
                :placeholder="trans('calendar.event.popover.title.placeholder')"
                autofocus
                maxlength="50"
                name="title"
            />
            <InputGroupAddon align="inline-end">
                <InputGroupButton :title="trans('calendar.event.popover.cancel')" variant="ghost" @click="emit('done')">
                    <X />
                </InputGroupButton>
                <InputGroupButton
                    :disabled="processing"
                    :title="trans('calendar.event.popover.save')"
                    variant="default"
                >
                    <ArrowRight />
                </InputGroupButton>
            </InputGroupAddon>
        </InputGroup>
        <p v-if="errors.title" class="text-sm text-destructive">{{ errors.title }}</p>
    </Form>
</template>

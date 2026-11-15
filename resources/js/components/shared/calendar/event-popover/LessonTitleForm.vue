<script lang="ts" setup>
import { Form } from '@inertiajs/vue3';
import { trans } from 'laravel-vue-i18n';
import { Input } from '@/components/ui/input';
import FormActions from '@/components/shared/calendar/event-popover/FormActions.vue';
import { update } from '@/routes/teacher/calendar/events';
import type { Lesson } from '@/types/calendar';

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
        <Input
            :aria-invalid="!!errors.title"
            :aria-label="trans('calendar.event.popover.title.label')"
            :default-value="event.title ?? ''"
            :placeholder="trans('calendar.event.popover.title.placeholder')"
            autofocus
            maxlength="50"
            name="title"
        />
        <p v-if="errors.title" class="text-sm text-destructive">{{ errors.title }}</p>

        <FormActions
            :processing="processing"
            :submit-label="trans('calendar.event.popover.save')"
            @cancel="emit('done')"
        />
    </Form>
</template>

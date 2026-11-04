<script lang="ts" setup>
import { computed, shallowRef, watch } from 'vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { formatEventSchedule } from '@/lib/calendar-event';
import type { Draft, DraftUpdate, EventType, SelectLessonStatus } from '@/types/calendar';
import { Tabs, TabsContent, TabsList, TabsTrigger } from '@/components/ui/tabs';
import { trans } from 'laravel-vue-i18n';
import { Select, SelectContent, SelectGroup, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import StatusBadge from '@/components/shared/calendar/StatusBadge.vue';
import { StatusBadgeVariant } from '@/lib/calendar-status';
import { StudentSummary } from '@/types/Student';
import UserAvatar from '@/components/user/UserAvatar.vue';
import { Label } from '@/components/ui/label';
import { Form } from '@inertiajs/vue3';
import { store } from '@/routes/teacher/calendar';

const props = defineProps<{
    draft: Draft;
    students?: StudentSummary[];
}>();

const emit = defineEmits<{
    cancel: [];
    update: [update: DraftUpdate];
}>();

const schedule = computed(() => formatEventSchedule(props.draft));
const statuses = ['planned', 'requested', 'cancelled'] as const satisfies readonly SelectLessonStatus[];

const status = defineModel<StatusBadgeVariant>('status', { default: 'planned' });
const studentId = shallowRef<StudentSummary['id']>();
const selectedStudent = computed(() => props.students?.find((student) => student.id === studentId.value));

watch(studentId, (id) => {
    const student = props.students?.find((student) => student.id === id);
    if (student && !props.draft.title) emit('update', { title: student.fullName });
});
</script>

<template>
    <Form #default="{ errors, processing }" :action="store()" class="flex flex-col gap-3" @success="emit('cancel')">
        <Tabs :model-value="draft.type" @update:model-value="emit('update', { type: $event as EventType })">
            <TabsList>
                <TabsTrigger value="lesson">{{ trans('calendar.popover.lesson') }}</TabsTrigger>
                <TabsTrigger value="time_off">{{ trans('calendar.popover.time_off') }}</TabsTrigger>
                <input :value="draft.type" name="type" type="hidden" />
            </TabsList>

            <p v-if="errors.failed" class="text-sm text-center text-destructive">
                {{ errors.failed }}
            </p>

            <div class="text-sm">
                <p class="m-0">{{ schedule.day }}</p>
                <p class="m-0 text-muted-foreground tabular-nums">{{ schedule.timeRange }}</p>
                <input :value="props.draft.startsAt.toISOString()" name="starts_at" type="hidden" />
                <input :value="props.draft.endsAt.toISOString()" name="ends_at" type="hidden" />
            </div>

            <TabsContent class="flex flex-col gap-2" value="lesson">
                <div class="grid grid-cols-2 gap-2">
                    <div class="flex min-w-0 flex-col gap-2">
                        <Label for="status">{{ trans('calendar.popover.status.label') }}</Label>
                        <Select v-slot="{ modelValue }" v-model="status" name="status">
                            <SelectTrigger :aria-invalid="!!errors.status" class="w-full truncate cursor-pointer">
                                <SelectValue>
                                    <StatusBadge :variant="(modelValue as StatusBadgeVariant) ?? 'planned'" />
                                </SelectValue>
                            </SelectTrigger>
                            <SelectContent>
                                <SelectGroup>
                                    <SelectItem v-for="status in statuses" :key="status" :value="status">
                                        <StatusBadge :variant="status" />
                                    </SelectItem>
                                </SelectGroup>
                            </SelectContent>
                        </Select>
                        <p v-if="errors.status" class="text-sm text-destructive">
                            {{ errors.status }}
                        </p>
                    </div>

                    <div class="flex min-w-0 flex-col gap-2">
                        <Label for="student">{{ trans('calendar.popover.student.label') }}</Label>
                        <Select v-model="studentId" name="student_id">
                            <SelectTrigger :aria-invalid="!!errors.student_id" class="w-full cursor-pointer">
                                <SelectValue :placeholder="$t('calendar.popover.student.placeholder')">
                                    <template v-if="selectedStudent">
                                        <UserAvatar :user="selectedStudent" class="size-6.5 shrink-0" />
                                        <span class="truncate">{{ selectedStudent.fullName }}</span>
                                    </template>
                                </SelectValue>
                            </SelectTrigger>
                            <SelectContent>
                                <SelectGroup>
                                    <SelectItem v-for="student in props.students" :key="student.id" :value="student.id">
                                        <UserAvatar :user="student" class="size-6.5" />
                                        {{ student.fullName }}
                                    </SelectItem>
                                </SelectGroup>
                            </SelectContent>
                        </Select>
                        <p v-if="errors.student_id" class="text-sm text-destructive">
                            {{ errors.student_id }}
                        </p>
                    </div>
                </div>

                <Label for="title">{{ trans('calendar.popover.title.label') }}</Label>
                <Input
                    :aria-invalid="!!errors.title"
                    :aria-label="$t('calendar.popover.title')"
                    :model-value="draft.title ?? ''"
                    :placeholder="$t('calendar.popover.title.placeholder')"
                    name="title"
                    @update:model-value="emit('update', { title: String($event) })"
                />
                <p v-if="errors.title" class="text-sm text-destructive">
                    {{ errors.title }}
                </p>
            </TabsContent>

            <div class="flex justify-end gap-2">
                <Button size="sm" type="button" variant="ghost" @click="emit('cancel')">
                    {{ $t('calendar.popover.cancel') }}
                </Button>
                <Button :disabled="processing" size="sm">
                    {{ $t('calendar.popover.create') }}
                </Button>
            </div>
        </Tabs>
    </Form>
</template>

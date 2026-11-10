<script lang="ts" setup>
import { computed, shallowRef, watch } from 'vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import TimeInput from '@/components/shared/TimeInput.vue';
import { formatDuration, formatEventSchedule } from '@/lib/calendar-event';
import {
    LESSON_DURATIONS,
    minutesToTime,
    slotMinutes,
    timeToMinutes,
    withEnd,
    withLessonDuration,
    withLessonStart,
    withStart,
} from '@/lib/calendar-utils';
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
const times = computed(() => {
    const { startMin, endMin } = slotMinutes(props.draft);
    return { start: minutesToTime(startMin), end: minutesToTime(endMin), duration: endMin - startMin };
});

function moveStartTo(time: string | undefined) {
    if (!time) return;
    const startMin = timeToMinutes(time);
    const isLesson = props.draft.type === 'lesson';
    emit('update', isLesson ? withLessonStart(props.draft, startMin) : withStart(props.draft, startMin));
}

function moveEndTo(time: string | undefined) {
    if (time) emit('update', withEnd(props.draft, timeToMinutes(time)));
}

const inlineField =
    'h-auto w-auto -mx-1 mr-0.5 rounded-sm border-0 bg-transparent p-0 text-sm shadow-none dark:bg-transparent ' +
    'hover:bg-accent focus-visible:bg-accent focus-visible:ring-0 aria-invalid:text-destructive';

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
                <TabsTrigger value="lesson">{{ trans('calendar.event.popover.lesson') }}</TabsTrigger>
                <TabsTrigger value="time_off">{{ trans('calendar.event.popover.time_off') }}</TabsTrigger>
                <input :value="draft.type" name="type" type="hidden" />
            </TabsList>

            <p v-if="errors.failed" class="text-sm text-center text-destructive">
                {{ errors.failed }}
            </p>

            <div class="text-sm">
                <p class="m-0 flex items-center gap-2">
                    {{ schedule.day }}
                    <template v-if="draft.type === 'lesson'">
                        <span class="text-secondary-foreground font-bold">·</span>
                        <Select
                            :model-value="times.duration"
                            @update:model-value="emit('update', withLessonDuration(draft, Number($event)))"
                        >
                            <SelectTrigger
                                :aria-invalid="!!errors.ends_at"
                                :aria-label="trans('calendar.event.popover.time.duration')"
                                :class="[
                                    inlineField,
                                    'mx-0 gap-0.5 font-bold max-h-5 text-secondary-foreground [&_svg]:size-3.5',
                                ]"
                            >
                                <SelectValue />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectGroup>
                                    <SelectItem v-for="duration in LESSON_DURATIONS" :key="duration" :value="duration">
                                        {{ formatDuration(duration) }}
                                    </SelectItem>
                                </SelectGroup>
                            </SelectContent>
                        </Select>
                    </template>
                </p>
                <p class="m-0 flex items-center gap-2 text-muted-foreground tabular-nums">
                    <TimeInput
                        :aria-invalid="!!errors.starts_at"
                        :aria-label="trans('calendar.event.popover.time.start')"
                        :model-value="times.start"
                        @update:model-value="moveStartTo"
                    />
                    <span>–</span>
                    <TimeInput
                        v-if="draft.type === 'time_off'"
                        :aria-invalid="!!errors.ends_at"
                        :aria-label="trans('calendar.event.popover.time.end')"
                        :model-value="times.end"
                        @update:model-value="moveEndTo"
                    />
                    <span v-else>{{ times.end }}</span>
                </p>
                <input :value="props.draft.startsAt.toISOString()" name="starts_at" type="hidden" />
                <input :value="props.draft.endsAt.toISOString()" name="ends_at" type="hidden" />
            </div>
            <p v-if="errors.starts_at" class="text-sm text-destructive">{{ errors.starts_at }}</p>
            <p v-if="errors.ends_at" class="text-sm text-destructive">{{ errors.ends_at }}</p>

            <TabsContent class="flex flex-col gap-2" value="lesson">
                <div class="grid grid-cols-2 gap-2">
                    <div class="flex min-w-0 flex-col gap-2">
                        <Label for="status">{{ trans('calendar.event.popover.status.label') }}</Label>
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
                        <Label for="student">{{ trans('calendar.event.popover.student.label') }}</Label>
                        <Select v-model="studentId" name="student_id">
                            <SelectTrigger :aria-invalid="!!errors.student_id" class="w-full cursor-pointer">
                                <SelectValue :placeholder="$t('calendar.event.popover.student.placeholder')">
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

                <Label for="title">{{ trans('calendar.event.popover.title.label') }}</Label>
                <Input
                    :aria-invalid="!!errors.title"
                    :aria-label="$t('calendar.event.popover.title')"
                    :model-value="draft.title ?? ''"
                    :placeholder="$t('calendar.event.popover.title.placeholder')"
                    name="title"
                    @update:model-value="emit('update', { title: String($event) })"
                />
                <p v-if="errors.title" class="text-sm text-destructive">
                    {{ errors.title }}
                </p>
            </TabsContent>

            <div class="flex justify-end gap-2">
                <Button size="sm" type="button" variant="ghost" @click="emit('cancel')">
                    {{ $t('calendar.event.popover.cancel') }}
                </Button>
                <Button :disabled="processing" size="sm">
                    {{ $t('calendar.event.popover.create') }}
                </Button>
            </div>
        </Tabs>
    </Form>
</template>

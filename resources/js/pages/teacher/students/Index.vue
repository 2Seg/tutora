<script setup lang="ts">
import { router } from '@inertiajs/vue3';
import DataTable from '@/components/shared/data-table/DataTable.vue';
import { studentColumns } from '@/pages/teacher/students/columns';
import { computed, markRaw, ref, watch } from 'vue';
import StudentCard from '@/components/student/StudentCard.vue';
import CreateStudentPopover from '@/components/student/CreateStudentPopover.vue';
import { index } from '@/routes/teacher/students';
import { Student } from '@/types/Student';

const props = defineProps<{ students: Student[]; timezones: string[]; selectedStudentId?: number | null }>();
const columns = markRaw(studentColumns);
const selectedStudentId = ref<string | null>(props.selectedStudentId?.toString() ?? null);
const selectedStudent = computed(
    () => props.students.find((student) => String(student.id) === selectedStudentId.value) ?? null,
);

watch(selectedStudentId, (studentId) =>
    router.replace({
        url: index({ mergeQuery: { student: studentId } }).url,
        preserveState: true,
        preserveScroll: true,
    }),
);
</script>

<template>
    <div class="grid grid-cols-3 content-start gap-6">
        <section class="col-span-2 flex flex-col">
            <DataTable
                v-model:selected-row-id="selectedStudentId"
                class="min-h-0 flex-1 h-full"
                :columns="columns"
                :data="props.students"
                :get-row-id="(student: Student) => String(student.id)"
                search-placeholder="Search by name or email..."
            >
                <template #actions>
                    <CreateStudentPopover :timezones="props.timezones" />
                </template>
            </DataTable>
        </section>

        <aside v-if="selectedStudent">
            <StudentCard
                class="h-full"
                :student="selectedStudent"
                @click.stop
            />
        </aside>
    </div>
</template>

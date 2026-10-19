<script setup lang="ts">
import DataTable from '@/components/shared/data-table/DataTable.vue';
import { studentColumns } from '@/pages/teacher/students/columns';
import { markRaw, ref } from 'vue';
import StudentCard from '@/components/student/StudentCard.vue';
import { Student } from '@/types/Student';

const props = defineProps<{ students: Student[] }>()
const columns = markRaw(studentColumns);
const selectedStudent = ref<Student | null>(null);
</script>

<template>
    <div class="grid grid-cols-3 content-start gap-6">
        <section class="col-span-2 flex flex-col">
            <DataTable
                class="min-h-0 flex-1 h-full"
                :columns="columns"
                :data="props.students"
                search-placeholder="Search by name or email..."
                @row-select="selectedStudent = $event"
            />
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

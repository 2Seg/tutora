<script setup lang="ts">
import DataTable from '@/components/shared/data-table/DataTable.vue';
import { studentColumns } from '@/pages/teacher/students/columns';
import { students } from '@/pages/teacher/students/data';
import { markRaw, ref } from 'vue';
import StudentCard from '@/components/students/StudentCard.vue';
import { Student } from '@/types/Student';

const columns = markRaw(studentColumns);
const selectedStudent = ref<Student | null>(null);
</script>

<template>
    <section class="col-span-2">
        <DataTable
            class="min-h-0 flex-1"
            :columns="columns"
            :data="students"
            search-placeholder="Search by name or email..."
            @row-select="selectedStudent = $event"
        />
    </section>

    <aside v-if="selectedStudent">
        <StudentCard :student="selectedStudent" @click.stop/>
    </aside>
</template>

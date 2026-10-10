<script lang="ts">
import DataTable from '@/components/shared/data-table/DataTable.vue';
import { studentColumns } from '@/pages/teacher/students/columns';
import { students } from '@/pages/teacher/students/data';
import { markRaw } from 'vue';

export default {
    name: "Index",
    components: { DataTable },
    data() {
        return {
            studentColumns: markRaw(studentColumns),
            students,
        }
    },

}
</script>

<template>
    <section class="col-span-2">
        <DataTable
            class="min-h-0 flex-1"
            :columns="studentColumns"
            :data="students"
            search-placeholder="Search by name or email..."
        />
    </section>

    <aside class="bg-secondary rounded-2xl">
        Aside
    </aside>
</template>

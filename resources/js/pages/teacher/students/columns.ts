import { createColumnHelper, type ColumnDef } from '@tanstack/vue-table';
import { h } from 'vue';
import type { DataTableFeatures } from '@/components/shared/data-table/features';
import type { Student } from '@/types/Student';
import StudentCell from '@/components/students/StudentCell.vue';

const columnHelper = createColumnHelper<DataTableFeatures, Student>()

export const studentColumns = columnHelper.columns([
    columnHelper.accessor((row) => `${row.firstName} ${row.lastName} ${row.email}`, {
        id: 'student',
        header: 'Student',
        cell: ({ row }) => h(StudentCell, { student: row.original }),
    }),
])

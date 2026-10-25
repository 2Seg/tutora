<script lang="ts" setup>
import type { ColumnDef } from '@tanstack/vue-table';
import { FlexRender, useTable } from '@tanstack/vue-table';
import SearchInput from '@/components/shared/SearchInput.vue';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { features, type DataTableFeatures } from '@/components/shared/data-table/features';
import { computed, onBeforeUnmount, onMounted, ref } from 'vue';
import {
    PaginationContent,
    PaginationNext,
    Pagination,
    PaginationPrevious,
    PaginationItem,
    PaginationEllipsis,
} from '@/components/ui/pagination';

const props = withDefaults(
    defineProps<{
        columns: ColumnDef<DataTableFeatures, any, any>[];
        data: any[];
        searchPlaceholder?: string;
        itemLabel?: string;
        pageSize?: number;
    }>(),
    { pageSize: 6 },
);

const emit = defineEmits<{
    'row-select': [row: any | null];
}>();
const selectedRowId = ref<string | null>(null);

function select(row: { id: string; original: unknown }) {
    selectedRowId.value = row.id;
    emit('row-select', row.original);
}

function clear() {
    selectedRowId.value = null;
    emit('row-select', null);
}

onMounted(() => document.addEventListener('click', clear));
onBeforeUnmount(() => document.removeEventListener('click', clear));

const table = useTable({
    features,
    get data() {
        return props.data;
    },
    get columns() {
        return props.columns;
    },
    globalFilterFn: 'includesString',
    initialState: {
        pagination: { pageIndex: 0, pageSize: props.pageSize },
    },
});

const pagination = computed(() => table.atoms.pagination.get());
const total = computed(() => table.getFilteredRowModel().rows.length);
const from = computed(() => (total.value === 0 ? 0 : pagination.value.pageIndex * pagination.value.pageSize + 1));
const to = computed(() => Math.min((pagination.value.pageIndex + 1) * pagination.value.pageSize, total.value));
</script>

<template>
    <div class="space-y-4">
        <SearchInput
            :model-value="table.atoms.globalFilter.get() ?? ''"
            :placeholder="searchPlaceholder"
            class="mb-4"
            @update:model-value="table.setGlobalFilter($event)"
        />

        <div class="flex min-h-112 flex-col rounded-2xl bg-card p-4">
            <Table>
                <TableHeader class="[&_tr]:border-0">
                    <TableRow
                        v-for="headerGroup in table.getHeaderGroups()"
                        :key="headerGroup.id"
                        class="border-0 hover:bg-transparent"
                    >
                        <TableHead
                            v-for="header in headerGroup.headers"
                            :key="header.id"
                            class="h-auto px-3 pb-4 text-xs font-medium uppercase tracking-wider text-muted-foreground"
                        >
                            <FlexRender v-if="!header.isPlaceholder" :header="header" />
                        </TableHead>
                    </TableRow>
                </TableHeader>

                <TableBody>
                    <template v-if="table.getRowModel().rows.length">
                        <TableRow
                            v-for="row in table.getRowModel().rows"
                            :key="row.id"
                            class="group border-0 hover:bg-transparent cursor-pointer"
                            @click.stop="select(row)"
                        >
                            <TableCell
                                v-for="cell in row.getVisibleCells()"
                                :key="cell.id"
                                :class="{
                                    'bg-accent/50': selectedRowId === row.id,
                                }"
                                class="px-3 py-2 gap-1 first:rounded-l-xl last:rounded-r-xl group-hover:bg-accent"
                            >
                                <FlexRender :cell="cell" />
                            </TableCell>
                        </TableRow>
                    </template>
                    <TableRow v-else class="border-0 hover:bg-transparent">
                        <TableCell :colspan="columns.length" class="h-24 text-center"> No results. </TableCell>
                    </TableRow>
                </TableBody>
            </Table>

            <div class="mt-auto flex items-center justify-between px-3">
                <p class="text-sm text-primary">
                    {{
                        $tChoice('common.pagination.results', total, {
                            from: String(from),
                            to: String(to),
                            total: String(total),
                        })
                    }}
                </p>

                <div class="flex items-center gap-1">
                    <Pagination
                        :items-per-page="pagination.pageSize"
                        :page="pagination.pageIndex + 1"
                        :sibling-count="1"
                        :total="table.getRowCount()"
                        show-edges
                        @update:page="table.setPageIndex($event - 1)"
                    >
                        <PaginationContent v-slot="{ items }">
                            <PaginationPrevious />

                            <template v-for="(item, index) in items" :key="index">
                                <PaginationItem
                                    v-if="item.type === 'page'"
                                    :is-active="item.value === pagination.pageIndex + 1"
                                    :value="item.value"
                                >
                                    {{ item.value }}
                                </PaginationItem>

                                <PaginationEllipsis v-else :index="index" />
                            </template>

                            <PaginationNext />
                        </PaginationContent>
                    </Pagination>
                </div>
            </div>
        </div>
    </div>
</template>

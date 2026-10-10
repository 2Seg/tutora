import {
    columnFilteringFeature,
    columnVisibilityFeature,
    createFilteredRowModel,
    createPaginatedRowModel,
    filterFn_includesString,
    globalFilteringFeature,
    rowPaginationFeature,
    tableFeatures,
} from '@tanstack/vue-table';

export const features = tableFeatures({
    columnFilteringFeature,
    columnVisibilityFeature,
    globalFilteringFeature,
    rowPaginationFeature,
    filteredRowModel: createFilteredRowModel(),
    paginatedRowModel: createPaginatedRowModel(),
    filterFns: { includesString: filterFn_includesString },
});

export type DataTableFeatures = typeof features;

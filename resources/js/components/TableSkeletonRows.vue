<script setup lang="ts">
import { Skeleton } from '@/components/ui/skeleton';
import { TableCell, TableRow } from '@/components/ui/table';

withDefaults(
    defineProps<{
        rows?: number;
        columns: number;
    }>(),
    {
        rows: 5,
    },
);
</script>

<template>
    <TableRow v-for="row in rows" :key="row">
        <TableCell v-for="column in columns" :key="column">
            <Skeleton class="h-4 w-full" />
        </TableCell>
    </TableRow>
</template>

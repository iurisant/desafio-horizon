<script setup lang="ts">
import { Button } from '@/components/ui/button';
import type { PaginationLink } from '@/types';

defineProps<{
    links: PaginationLink[];
    from: number | null;
    to: number | null;
    total: number;
}>();

const emit = defineEmits<{
    (e: 'navigate', url: string): void;
}>();
</script>

<template>
    <div
        v-if="total > 0"
        class="flex flex-col items-center justify-between gap-3 sm:flex-row"
    >
        <p class="text-sm text-muted-foreground">
            Mostrando {{ from }}–{{ to }} de {{ total }}
        </p>

        <div class="flex flex-wrap items-center gap-1">
            <Button
                v-for="(link, index) in links"
                :key="index"
                type="button"
                size="sm"
                :variant="link.active ? 'default' : 'outline'"
                :disabled="link.url === null"
                @click="link.url && emit('navigate', link.url)"
            >
                <span v-html="link.label" />
            </Button>
        </div>
    </div>
</template>

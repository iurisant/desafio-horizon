<script setup lang="ts">
import { Badge } from '@/components/ui/badge';

defineProps<{
    status: 'ativo' | 'inativo';
}>();
</script>

<template>
    <Badge
        v-if="status === 'ativo'"
        variant="default"
        class="border-transparent bg-green-100 text-green-800 dark:bg-green-500/10 dark:text-green-400"
    >
        Ativo
    </Badge>
    <Badge v-else variant="secondary">Inativo</Badge>
</template>

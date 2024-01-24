<script setup>
defineProps({
    label: {
        type: String,
    },
});
</script>

<template>
    <span class="tw-text-right">{{ label }}</span>
    <slot />
</template>

<style scoped></style>

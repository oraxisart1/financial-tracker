<script setup>
defineProps({
    title: {
        type: String,
    },
});
</script>

<template>
    <div
        class="tw-grid tw-grid-rows-[60px_1fr] tw-rounded-md tw-bg-light-pastel tw-max-h-full tw-min-h-full"
    >
        <div
            class="tw-text-white tw-p-3 tw-font-medium tw-text-2xl tw-text-center tw-bg-teal tw-rounded-t-md tw-col-span-full"
        >
            {{ title }}
        </div>

        <div class="tw-overflow-y-auto">
            <slot />
        </div>
    </div>
</template>

<style scoped></style>

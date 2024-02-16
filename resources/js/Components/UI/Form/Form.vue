<script setup>
defineProps({
    title: {
        type: String,
    },
});

const emit = defineEmits(["submit"]);
</script>

<template>
    <div
        class="tw-flex tw-flex-col tw-rounded-md tw-bg-light-pastel tw-overflow-y-auto"
    >
        <div
            class="tw-text-white tw-p-3 tw-font-medium tw-text-2xl tw-text-center tw-bg-teal tw-rounded-t-md"
        >
            {{ title }}
        </div>

        <form
            class="tw-py-4 tw-px-8 tw-grid tw-grid-cols-[120px_1fr] tw-items-center tw-gap-y-6 tw-gap-x-12"
            @submit.prevent="emit('submit', $event)"
        >
            <slot />
        </form>
    </div>
</template>

<style scoped></style>

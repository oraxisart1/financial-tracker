<script setup>
defineProps({
    tabs: {
        type: Array,
        required: true,
    },
    modelValue: {
        type: String,
        required: true,
    },
});

const emit = defineEmits(["update:modelValue"]);
</script>

<template>
    <div class="tw-flex tw-items-center tw-text-white">
        <div
            v-for="tab in tabs"
            :key="tab.name"
            :class="`tw-text-center tw-flex-grow tw-flex-1 tw-font-semibold tw-text-3xl tw-py-1.5 tw-cursor-pointer hover:tw-bg-navigation ${
                modelValue === tab.name
                    ? 'tw-bg-navigation'
                    : 'tw-bg-navigation-inactive'
            } ${tab.classes || ''}`"
            @click="emit('update:modelValue', tab.name)"
        >
            {{ tab.label }}
        </div>
    </div>
</template>

<style scoped></style>

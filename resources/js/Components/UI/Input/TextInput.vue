<script setup>
defineProps({
    modelValue: { type: [String] },
    label: { type: String },
    error: { type: String },
    placeholder: { type: String },
    type: { type: String, default: "text" },
});
const emit = defineEmits(["update:modelValue"]);

const onInput = (e) => {
    emit("update:modelValue", e.target.value);
};
</script>

<template>
    <div class="tw-flex tw-flex-col tw-gap-1">
        <input
            :class="`tw-bg-pastel tw-rounded-md tw-w-full tw-py-4 tw-px-4 tw-shadow-sm tw-text-md tw-min-h-[56px] ${
                error
                    ? '!tw-border-2 !tw-border-red-600 tw-text-red-600 focus:tw-ring-red-500'
                    : 'focus:tw-ring-indigo-500 focus:tw-ring-1 tw-border tw-border-light'
            }`"
            :placeholder="placeholder"
            :type="type"
            :value="modelValue"
            @input="onInput"
        />

        <div v-if="error" class="tw-text-xs tw-ml-2 tw-text-red-600">
            {{ error }}
        </div>
    </div>
</template>

<style scoped></style>

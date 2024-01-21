<script setup>
import { computed } from "vue";

const props = defineProps({
    active: {
        type: Boolean,
        default: false,
    },
});
const emit = defineEmits(["click"]);

const baseClasses =
    "tw-text-white tw-text-3xl tw-font-semibold tw-rounded-lg tw-shadow-lg tw-py-1.5 tw-px-4";

const stateClasses = computed(() =>
    props.active ? "tw-bg-navigation" : "tw-bg-navigation-inactive",
);
</script>

<template>
    <button
        :class="`${baseClasses} ${stateClasses}`"
        @click="emit('click', $event)"
    >
        <slot />
    </button>
</template>

<style scoped></style>

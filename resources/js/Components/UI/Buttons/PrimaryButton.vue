<script setup>
import { computed } from "vue";

const props = defineProps({
    active: {
        type: Boolean,
        default: false,
    },
    size: {
        type: String,
        default: "large",
    },
    width: {
        type: String,
        default: "",
    },
});
const emit = defineEmits(["click"]);

const baseClasses =
    "tw-text-white tw-font-semibold tw-rounded-lg tw-shadow-lg tw-py-1.5 tw-px-4";

const stateClasses = computed(() =>
    props.active ? "tw-bg-navigation" : "tw-bg-navigation-inactive",
);

const sizeClasses = computed(() => {
    const fontSize = {
        medium: "2xl",
        large: "3xl",
    }[props.size];

    const fontWeight = {
        medium: "semibold",
        large: "bold",
    }[props.size];

    const width = props.width;

    return `tw-text-${fontSize} tw-font-${fontWeight} ${
        width ? `tw-w-[${width}]` : ""
    }`;
});
</script>

<template>
    <button
        :class="`${baseClasses} ${stateClasses} ${sizeClasses}`"
        @click="emit('click', $event)"
    >
        <slot />
    </button>
</template>

<style scoped></style>

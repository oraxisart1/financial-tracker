<script setup>
import PrimaryButton from "@/Components/UI/Buttons/PrimaryButton.vue";
import { computed } from "vue";

const props = defineProps({
    type: {
        type: String,
        default: "button",
    },
    active: {
        type: Boolean,
        default: null,
    },
    width: {
        type: String,
        default: "200px",
    },
});
const buttonProps = computed(() => {
    return {
        active: props.type === "submit" && props.active !== false,
    };
});
</script>

<template>
    <PrimaryButton
        :="buttonProps"
        :type="type"
        :width="width"
        class="tw-py-3"
        size="medium"
    >
        <slot />
    </PrimaryButton>
</template>

<style scoped></style>

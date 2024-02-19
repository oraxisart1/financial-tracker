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
        class="tw-py-3"
        size="medium"
        width="200px"
    >
        <slot />
    </PrimaryButton>
</template>

<style scoped></style>

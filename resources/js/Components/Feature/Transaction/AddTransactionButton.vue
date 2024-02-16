<script setup>
import PrimaryButton from "@/Components/UI/Buttons/PrimaryButton.vue";

const emit = defineEmits(["click"]);

const onClick = (e) => {
    emit("click", e);
};
</script>

<template>
    <PrimaryButton active class="tw-px-40" size="large" @click="onClick"
        >Add
    </PrimaryButton>
</template>

<style scoped></style>

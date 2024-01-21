<script setup>
const emit = defineEmits(["click"]);

const onClick = (e) => {
    emit("click", e);
};
</script>

<template>
    <button
        class="tw-bg-navigation tw-text-white tw-px-40 tw-py-1.5 tw-text-3xl tw-font-semibold tw-rounded-lg tw-shadow-lg hover:tw-bg-navigation-inactive focus:tw-bg-navigation-inactive"
        @click="onClick"
    >
        Add
    </button>
</template>

<style scoped></style>

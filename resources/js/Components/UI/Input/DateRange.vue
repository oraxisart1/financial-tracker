<script setup>
import VueDatePicker from "@vuepic/vue-datepicker";
import "@vuepic/vue-datepicker/dist/main.css";
import { computed, ref } from "vue";
import {
    endOfMonth,
    endOfWeek,
    endOfYear,
    startOfMonth,
    startOfWeek,
    startOfYear,
    subYears,
} from "date-fns";

const props = defineProps({
    modelValue: {
        type: Array,
        required: true,
    },
});

const emit = defineEmits(["update:modelValue"]);

const presetDates = ref([
    { label: "Today", value: [new Date(), new Date()] },
    {
        label: "This week",
        value: [startOfWeek(new Date()), endOfWeek(new Date())],
    },
    {
        label: "This month",
        value: [startOfMonth(new Date()), endOfMonth(new Date())],
    },
    {
        label: "This year",
        value: [startOfYear(new Date()), endOfYear(new Date())],
    },
    {
        label: "Last year",
        value: [
            startOfYear(subYears(new Date(), 1)),
            endOfYear(subYears(new Date(), 1)),
        ],
    },
]);

const value = computed({
    get: () => props.modelValue,
    set: (value) => emit("update:modelValue", value),
});
</script>

<template>
    <VueDatePicker
        v-model="value"
        :clearable="false"
        :enable-time-picker="false"
        :preset-dates="presetDates"
        auto-apply
        class="tw-w-fit"
        hide-input-icon
        input-class-name="tw-text-md tw-font-medium tw-bg-pastel tw-px-2 tw-py-0 tw-rounded-md tw-border tw-border-light tw-w-fit tw-text-center"
        menu-class-name="tw-bg-stone-50 calendar__menu"
        model-type="yyyy-MM-dd"
        range
    />
</template>

<style scoped>
.dp__action_select {
    color: black;
}
</style>

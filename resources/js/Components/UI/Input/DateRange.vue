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
    format,
} from "date-fns";

const props = defineProps({
    modelValue: {
        type: Array,
        required: true,
    },
    allTime: {
        type: Boolean,
        default: false,
    },
});

const emit = defineEmits(["update:modelValue", "update:allTime"]);

const picker = ref(null);
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
    {
        label: "All Time",
        value: [],
        slot: "all-time-preset",
    },
]);

const value = computed({
    get: () => props.modelValue,
    set: (value) => emit("update:modelValue", value),
});

const pickAllTime = () => {
    picker.value.closeMenu();
    emit("update:allTime", true);
};
</script>

<template>
    <VueDatePicker
        ref="picker"
        v-model="value"
        :clearable="false"
        :enable-time-picker="false"
        :preset-dates="presetDates"
        auto-apply
        class="tw-w-fit"
        hide-input-icon
        menu-class-name="tw-bg-stone-50 calendar__menu"
        model-type="yyyy-MM-dd"
        range
        @update:model-value="emit('update:allTime', false)"
    >
        <template #all-time-preset="{ label, value, presetDate }">
            <button
                class="dp__btn dp--preset-range"
                type="button"
                @click="pickAllTime"
                @keyup.enter.prevent="pickAllTime"
                @keyup.space.prevent="pickAllTime"
            >
                {{ label }}
            </button>
        </template>

        <template #trigger>
            <div
                class="tw-cursor-pointer tw-text-md tw-font-medium tw-bg-pastel tw-px-2 tw-py-0 tw-rounded-md tw-border tw-border-light tw-w-fit tw-text-center"
            >
                {{
                    allTime
                        ? "All time"
                        : `${value[0] ? format(value[0], "P") : "-"} - ${
                              value[1] ? format(value[1], "P") : "-"
                          }`
                }}
            </div>
        </template>
    </VueDatePicker>
</template>

<style scoped>
.dp__action_select {
    color: black;
}
</style>

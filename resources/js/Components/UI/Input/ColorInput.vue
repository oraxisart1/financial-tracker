<script setup>
import { computed, ref } from "vue";

const props = defineProps({
    modelValue: {
        type: String,
        required: true,
    },
    predefinedColors: {
        type: Array,
    },
    error: { type: String },
});

const emit = defineEmits(["update:modelValue"]);

const colorBaseClasses = "tw-h-10 tw-w-10 tw-rounded-full tw-cursor-pointer";
const stateColorClasses = "tw-ring-navigation tw-ring-4";

const colorsContainerBaseClasses =
    "tw-flex tw-items-center tw-justify-between tw-border-2 tw-rounded-md tw-p-2";
const colorsContainerErrorClasses = computed(() =>
    props.error ? "tw-border-red-600" : "tw-border-pastel",
);

const selectedColor = computed({
    get: () => props.modelValue,
    set: (value) => selectColor(value),
});

const isColorPopupOpened = ref(false);

const selectColor = (color) => {
    emit("update:modelValue", color);
};
</script>

<template>
    <div class="tw-flex tw-flex-col tw-gap-1">
        <div
            :class="`${colorsContainerBaseClasses} ${colorsContainerErrorClasses}`"
        >
            <span
                v-for="color in predefinedColors"
                :class="`${colorBaseClasses} ${
                    color === modelValue ? stateColorClasses : ''
                }`"
                :style="{ backgroundColor: color }"
                @click="selectColor(color)"
            ></span>

            <span
                v-if="
                    selectedColor && !predefinedColors.includes(selectedColor)
                "
                :class="`${colorBaseClasses} ${stateColorClasses}`"
                :style="{ backgroundColor: selectedColor }"
            ></span>

            <q-icon
                :name="isColorPopupOpened ? 'expand_less' : 'arrow_drop_down'"
                class="cursor-pointer tw-text-navigation"
                size="md"
            >
                <q-popup-proxy
                    transition-hide="scale"
                    transition-show="scale"
                    @before-hide="isColorPopupOpened = false"
                    @before-show="isColorPopupOpened = true"
                >
                    <q-color v-model="selectedColor" />
                </q-popup-proxy>
            </q-icon>
        </div>

        <div v-if="error" class="tw-text-xs tw-ml-2 tw-text-red-600">
            {{ error }}
        </div>
    </div>
</template>

<style scoped></style>

<script setup>
import { ref } from "vue";

defineProps({
    modelValue: { type: [Object, String] },
    label: { type: String },
    error: { type: String },
    placeholder: { type: String },
    options: { type: Array, required: true },
    optionLabel: { type: String, default: "label" },
    optionValue: { type: String, default: "value" },
    noOptionsLabel: { type: String, default: "Options not found" },
});

const emit = defineEmits(["update:modelValue"]);

const isOpen = ref(false);
const bodyHandler = ref(null);

const onInput = (e) => {
    emit("update:modelValue", e.target.value);
};

const open = () => {
    isOpen.value = true;
    const handler = (e) => {
        if (!e.target.closest("[data-select]")) {
            close();
        }
    };
    document.addEventListener("click", handler);
    bodyHandler.value = handler;
};

const close = () => {
    isOpen.value = false;
    if (bodyHandler.value) {
        document.removeEventListener("click", bodyHandler.value);
    }
};

const selectOption = (option) => {
    emit("update:modelValue", option);
    close();
};
</script>

<template>
    <div class="tw-flex tw-flex-col tw-gap-1">
        <div class="tw-flex tw-items-center tw-w-full">
            <div class="tw-flex-grow tw-w-full tw-relative" data-select>
                <div
                    :class="`tw-bg-pastel tw-p-4 tw-w-full tw-rounded-md tw-flex tw-items-center tw-cursor-pointer tw-min-h-[56px] ${
                        error ? 'tw-border-2 tw-border-red-600' : ''
                    }`"
                    @click="isOpen ? close() : open()"
                >
                    <div
                        v-if="!modelValue?.[optionLabel] && placeholder"
                        class="tw-w-full tw-text-center tw-text-stone-500 tw-text-md"
                    >
                        {{ placeholder }}
                    </div>
                    <span v-else class="tw-text-md">
                        {{ modelValue?.[optionLabel] || "" }}
                    </span>

                    <q-icon
                        :name="isOpen ? 'expand_less' : 'expand_more'"
                        class="tw-ml-auto"
                    ></q-icon>
                </div>

                <Transition>
                    <div
                        v-show="isOpen"
                        class="tw-absolute tw-flex tw-flex-col tw-bg-gray-100 tw-w-full tw-rounded-md tw-mt-1 tw-overflow-auto tw-max-h-[400px]"
                    >
                        <template v-if="options.length">
                            <div
                                v-for="option in options"
                                :key="option[optionValue]"
                                :class="`tw-p-4 tw-cursor-pointer hover:tw-bg-pastel ${
                                    modelValue?.[optionValue] ===
                                    option[optionValue]
                                        ? 'tw-bg-pastel'
                                        : ''
                                }`"
                                @click="selectOption(option)"
                            >
                                {{ option[optionLabel] }}
                            </div>
                        </template>

                        <template v-else>
                            <div class="tw-p-6">
                                {{ noOptionsLabel }}
                            </div>
                        </template>
                    </div>
                </Transition>
            </div>
        </div>

        <div v-if="error" class="tw-text-xs tw-ml-2 tw-text-red-600">
            {{ error }}
        </div>
    </div>
</template>

<style scoped>
.v-enter-active,
.v-leave-active {
    transition: opacity 0.2s ease;
}

.v-enter-from,
.v-leave-to {
    opacity: 0;
}
</style>
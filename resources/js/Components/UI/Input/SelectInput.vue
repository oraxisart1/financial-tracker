<script setup>
import { computed, nextTick, onBeforeUnmount, ref } from "vue";

const props = defineProps({
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
const filterValue = ref("");
const clickOutsideHandler = ref(null);
const container = ref(null);
const menu = ref(null);
const root = ref(null);
const input = ref(null);

const filteredOptions = computed(() => {
    if (!filterValue.value) {
        return props.options;
    }

    const value = String(filterValue.value).trim().toLowerCase();
    return props.options.filter(
        (opt) =>
            String(opt[props.optionLabel])
                .trim()
                .toLowerCase()
                .indexOf(value) !== -1,
    );
});

const open = () => {
    isOpen.value = true;
};

const close = () => {
    isOpen.value = false;
};

const selectOption = (option) => {
    emit("update:modelValue", option);
    close();
};

const onMenuEnter = () => {
    alignMenu();
    focusInput();
    addClickOutsideHandler();
};

const onMenuLeave = () => {
    removeClickOutsideHandler();
};

const addClickOutsideHandler = () => {
    clickOutsideHandler.value = (e) => {
        if (!menu.value?.contains(e.target) && !root.value.contains(e.target)) {
            close();
        }
    };
    document.addEventListener("click", clickOutsideHandler.value);
};

const removeClickOutsideHandler = () => {
    if (clickOutsideHandler.value) {
        document.removeEventListener("click", clickOutsideHandler.value);
    }
};

const alignMenu = () => {
    menu.value.style.minWidth = `${container.value.offsetWidth}px`;

    const containerRect = container.value.getBoundingClientRect();
    menu.value.style.top = `${containerRect.y + containerRect.height}px`;
    menu.value.style.left = `${containerRect.x}px`;
};

const focusInput = () => {
    input.value.focus();
};

const onContainerClick = (e) => {
    if (e.target === input.value) {
        return;
    }

    isOpen.value ? close() : open();
};

const clearFilter = () => {
    filterValue.value = "";
};

const onAfterMenuLeave = () => {
    nextTick(clearFilter);
};

onBeforeUnmount(() => {
    removeClickOutsideHandler();
});
</script>

<template>
    <div ref="root" class="tw-flex tw-flex-col tw-gap-1">
        <div class="tw-flex tw-items-center tw-w-full">
            <div class="tw-flex-grow tw-w-full tw-relative" data-select>
                <div
                    ref="container"
                    :class="`tw-bg-pastel tw-p-4 tw-w-full tw-rounded-md tw-flex tw-items-center tw-cursor-pointer tw-min-h-[58px] ${
                        error
                            ? 'tw-border-2 tw-border-red-600'
                            : 'tw-border tw-border-light'
                    }`"
                    @click="onContainerClick"
                >
                    <div v-show="!isOpen" class="tw-w-full">
                        <div
                            v-if="!modelValue?.[optionLabel] && placeholder"
                            class="tw-w-full tw-text-center tw-text-stone-500 tw-text-md tw-select-none"
                        >
                            {{ placeholder }}
                        </div>

                        <div v-else class="tw-text-md tw-select-none">
                            {{ modelValue?.[optionLabel] || "" }}
                        </div>
                    </div>

                    <input
                        v-show="isOpen"
                        ref="input"
                        :value="filterValue"
                        class="tw-bg-transparent !tw-border-none tw-border-b tw-p-0 tw-shadow-none tw-w-full tw-text-md"
                        @input="filterValue = $event.target.value"
                    />

                    <q-icon
                        :name="isOpen ? 'expand_less' : 'expand_more'"
                        class="tw-ml-auto"
                    ></q-icon>
                </div>

                <Teleport to="body">
                    <Transition
                        @enter="onMenuEnter"
                        @leave="onMenuLeave"
                        @after-leave="onAfterMenuLeave"
                    >
                        <div
                            v-if="isOpen"
                            ref="menu"
                            v-key-press="{ escape: close }"
                            class="tw-absolute tw-flex tw-flex-col tw-bg-gray-100 tw-rounded-md tw-mt-1 tw-overflow-auto tw-max-h-[400px] tw-top-0"
                        >
                            <template v-if="filteredOptions.length">
                                <div
                                    v-for="option in filteredOptions"
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

                            <template v-else-if="filterValue">
                                <div class="tw-p-6">Nothing was found</div>
                            </template>

                            <template v-else>
                                <div class="tw-p-6">
                                    {{ noOptionsLabel }}
                                </div>
                            </template>
                        </div>
                    </Transition>
                </Teleport>
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

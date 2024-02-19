<script setup>
import { computed, ref } from "vue";

const props = defineProps({
    modelValue: {
        type: Boolean,
        default: false,
    },
    escDismiss: {
        type: Boolean,
        default: true,
    },
});
const emit = defineEmits(["update:modelValue"]);

const clickOutsideListener = ref(null);
const dialog = ref(null);

const show = computed({
    get: () => props.modelValue,
    set: (value) => emit("update:modelValue", value),
});

const close = () => {
    show.value = false;
};

const onDialogEnter = () => {
    addClickOutsideListener();
};
const onDialogLeave = () => {
    removeClickOutsideListener();
};

const addClickOutsideListener = () => {
    clickOutsideListener.value = (e) => {
        if (!dialog.value?.contains(e.target)) {
            close();
        }
    };

    document.addEventListener("click", clickOutsideListener.value, {
        capture: true,
    });
};

const removeClickOutsideListener = () => {
    if (clickOutsideListener.value) {
        document.removeEventListener("click", clickOutsideListener.value);
    }
};
</script>

<template>
    <Teleport to="body">
        <Transition @enter="onDialogEnter" @leave="onDialogLeave">
            <div
                v-if="show"
                v-key-press="{ escape: escDismiss ? close : () => {} }"
                class="tw-flex tw-justify-center tw-items-center tw-absolute tw-top-0 tw-right-0 tw-bottom-0 tw-left-0 tw-bg-opacity-40 tw-backdrop-blur-sm tw-bg-stone-900"
            >
                <div ref="dialog" class="tw-shadow-lg tw-absolute tw-top-1/4">
                    <slot />
                </div>
            </div>
        </Transition>
    </Teleport>
</template>

<style scoped>
.v-enter-active,
.v-leave-active {
    transition: opacity 0.3s;
}

.v-enter,
.v-leave-to {
    opacity: 0;
}
</style>

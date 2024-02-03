<script setup>
import { ref } from "vue";

const show = ref(false);
const title = ref("");
const message = ref("");
const okButton = ref("");
const cancelButton = ref("");
const resolvePromise = ref(null);
const rejectPromise = ref(null);
const keysHandler = ref(null);
const clickOutsideListener = ref(null);
const dialog = ref(null);

const open = (options = {}) => {
    options = {
        title: "Confirm action",
        message: "Are you sure you want to delete?",
        okButton: "Confirm",
        cancelButton: "Cancel",
        ...options,
    };

    title.value = options.title;
    message.value = options.message;
    okButton.value = options.okButton;
    cancelButton.value = options.cancelButton;
    show.value = true;

    return new Promise((resolve, reject) => {
        resolvePromise.value = resolve;
        rejectPromise.value = reject;
    });
};

const confirm = () => {
    show.value = false;
    resolvePromise.value(true);
};

const cancel = () => {
    show.value = false;
    resolvePromise.value(false);
};

const clear = () => {
    resolvePromise.value = null;
    rejectPromise.value = null;
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
            cancel();
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
defineExpose({ open });
</script>

<template>
    <Teleport to="body">
        <Transition @enter="onDialogEnter" @leave="onDialogLeave">
            <div
                v-if="show"
                v-key-press="{ escape: cancel, enter: confirm }"
                class="tw-flex tw-justify-center tw-items-center tw-absolute tw-top-0 tw-right-0 tw-bottom-0 tw-left-0 tw-bg-opacity-40 tw-bg-stone-900"
            >
                <div
                    ref="dialog"
                    class="tw-flex tw-flex-col tw-gap-8 tw-bg-light tw-p-8 tw-rounded-xl tw-shadow-lg tw-absolute tw-top-1/4"
                >
                    <div class="tw-text-xl tw-font-semibold">
                        {{ title }}
                    </div>

                    <div class="tw-text-md">
                        {{ message }}
                    </div>

                    <div class="tw-flex tw-gap-4">
                        <button
                            class="tw-px-8 tw-py-3 tw-bg-red-600 tw-text-light tw-rounded-md tw-shadow-md hover:tw-bg-red-700 focus:tw-bg-red-700"
                            @click="confirm"
                        >
                            {{ okButton }}
                        </button>

                        <button
                            class="tw-px-8 tw-py-3 tw-bg-light tw-rounded-md tw-border tw-border-stone-500 hover:tw-bg-gray-300 focus:tw-bg-gray-300"
                            @click="cancel"
                        >
                            {{ cancelButton }}
                        </button>
                    </div>
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

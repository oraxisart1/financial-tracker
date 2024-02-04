<script setup>
import { computed, ref } from "vue";
import Dialog from "@/Components/UI/Dialog/Dialog.vue";

const props = defineProps({
    escDismiss: {
        type: Boolean,
        default: true,
    },
    submitOnEnterPress: {
        type: Boolean,
        default: true,
    },
    buttons: {
        type: Array,
        default: () => [],
    },
});

const show = ref(false);
const options = ref("");
const title = ref("");
const message = ref("");
const okButton = ref("");
const cancelButton = ref("");
const resolvePromise = ref(null);

const buttons = computed(() => {
    return props.buttons.length
        ? props.buttons
        : [
              { type: "confirm", label: okButton.value, value: true },
              { type: "cancel", label: cancelButton.value, value: false },
          ];
});

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

    return new Promise((resolve) => {
        resolvePromise.value = resolve;
    });
};

const confirm = () => {
    show.value = false;
    resolvePromise.value(true);
    clear();
};

const clear = () => {
    resolvePromise.value = null;
};

const handleButtonClick = (button) => {
    show.value = false;
    resolvePromise.value(button.value);
    clear();
};

defineExpose({ open });
</script>

<template>
    <Dialog v-model="show" :esc-dismiss="escDismiss">
        <div
            v-key-press="{ enter: submitOnEnterPress ? confirm : () => {} }"
            class="tw-flex tw-flex-col tw-gap-8 tw-bg-light tw-rounded-xl tw-p-8"
        >
            <div class="tw-text-xl tw-font-semibold">
                {{ title }}
            </div>

            <div class="tw-text-md tw-max-w-xl">
                {{ message }}
            </div>

            <div class="tw-flex tw-gap-4">
                <button
                    v-for="button in buttons"
                    :class="`tw-px-8 tw-py-3 tw-rounded-md ${
                        button.type === 'confirm'
                            ? 'tw-bg-red-600 hover:tw-bg-red-700 tw-text-light focus:tw-bg-red-700 tw-shadow-md '
                            : 'tw-border-stone-500 hover:tw-bg-gray-300 focus:tw-bg-gray-300 tw-border tw-bg-light'
                    }`"
                    @click="handleButtonClick(button)"
                >
                    {{ button.label }}
                </button>
            </div>
        </div>
    </Dialog>
</template>

<style scoped></style>

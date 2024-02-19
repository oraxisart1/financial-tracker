<script setup>
import { computed, ref } from "vue";
import Dialog from "@/Components/UI/Dialog/Dialog.vue";
import PrimaryButton from "@/Components/UI/Buttons/PrimaryButton.vue";

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
            class="tw-rounded-md tw-bg-pastel tw-w-[50vw]"
        >
            <div
                class="tw-bg-teal tw-text-white tw-text-2xl tw-font-medium tw-text-center tw-p-3 tw-rounded-t-md"
            >
                {{ title }}
            </div>

            <div
                class="tw-flex tw-flex-col tw-gap-8 tw-pt-6 tw-pb-4 tw-items-center"
            >
                <div class="tw-text-sm tw-text-center">
                    {{ message }}
                </div>

                <div class="tw-flex tw-gap-4">
                    <PrimaryButton
                        v-for="button in buttons"
                        class="tw-py-3"
                        size="medium"
                        @click="handleButtonClick(button)"
                    >
                        {{ button.label }}
                    </PrimaryButton>
                </div>
            </div>
        </div>
    </Dialog>
</template>

<style scoped></style>

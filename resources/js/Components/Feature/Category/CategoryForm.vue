<script setup>
import Form from "@/Components/UI/Form/Form.vue";
import useForm from "@/Hooks/useForm.js";
import { computed, watch } from "vue";
import FormRow from "@/Components/UI/Form/FormRow.vue";
import TextInput from "@/Components/UI/Input/TextInput.vue";
import ColorInput from "@/Components/UI/Input/ColorInput.vue";
import { useQuasar } from "quasar";
import SelectInput from "@/Components/UI/Input/SelectInput.vue";

const predefinedColors = [
    "#62698F",
    "#D3F485",
    "#983966",
    "#FFB134",
    "#98F8DD",
    "#450C49",
    "#DFE0E5",
    "#735861",
    "#074632",
    "#747B7C",
];

const typeOptions = {
    expense: {
        label: "Expense",
        value: "expense",
    },
    income: {
        label: "Income",
        value: "income",
    },
};

const props = defineProps({
    type: {
        type: String,
        required: true,
    },
});
const emit = defineEmits(["save", "cancel"]);

const quasar = useQuasar();
const form = useForm({
    title: "",
    color: "",
    id: null,
    type: typeOptions[props.type],
});

const isEditing = computed(() => Boolean(form.id));

const save = () => {
    const options = {
        onSuccess: () => {
            quasar.notify({
                color: "positive",
                message: isEditing.value
                    ? "Category successfully updated"
                    : "Category successfully created",
            });

            emit("save");
            clear();
        },
        onError(errors) {
            if (!Object.values(errors).length) {
                quasar.notify({
                    color: "negative",
                    message: "Something went wrong",
                });
            }
        },
    };

    const transform = form.transform((data) => ({
        ...data,
        type: data.type.value,
    }));

    if (form.id) {
        transform.patch(
            route("categories.update", { category: form.id }),
            options,
        );
    } else {
        transform.post(route("categories.store"), options);
    }
};
const cancel = () => {
    emit("cancel");
    clear();
};

const clear = () => {
    form.reset();
};

const setModel = (model) => {
    form.title = model.title;
    form.color = model.color;
    form.id = model.id;
    form.type = typeOptions[model.type];
};

defineExpose({ setModel, clear });
</script>

<template>
    <Form
        v-key-press="{ escape: cancel }"
        :title="isEditing ? 'Edit category' : 'Create category'"
        @submit="save"
    >
        <FormRow label="Type">
            <SelectInput
                v-model="form.type"
                :error="form.errors.type"
                :options="Object.values(typeOptions)"
            />
        </FormRow>

        <FormRow label="Title">
            <TextInput v-model="form.title" :error="form.errors.title" />
        </FormRow>

        <FormRow label="Color">
            <ColorInput
                v-model="form.color"
                :error="form.errors.color"
                :predefined-colors="predefinedColors"
            />
        </FormRow>

        <div class="tw-justify-center tw-flex tw-gap-12 tw-col-span-full">
            <button
                class="tw-bg-navigation tw-text-white tw-py-3 tw-w-[200px] tw-text-2xl tw-font-medium tw-rounded-lg tw-shadow-lg hover:tw-bg-navigation-inactive focus:tw-bg-navigation-inactive"
                type="submit"
            >
                Save
            </button>

            <button
                class="tw-bg-navigation-inactive tw-text-white tw-py-3 tw-w-[200px] tw-text-2xl tw-font-medium tw-rounded-lg tw-shadow-lg hover:tw-bg-navigation focus:tw-bg-navigation"
                type="reset"
                @click="cancel"
            >
                Cancel
            </button>
        </div>
    </Form>
</template>

<style scoped></style>

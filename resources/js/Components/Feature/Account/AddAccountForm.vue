<script setup>
import Form from "@/Components/UI/Form/Form.vue";
import TextInput from "@/Components/UI/Input/TextInput.vue";
import NumberInput from "@/Components/UI/Input/NumberInput.vue";
import SelectInput from "@/Components/UI/Input/SelectInput.vue";
import ColorInput from "@/Components/UI/Input/ColorInput.vue";
import FormRow from "@/Components/UI/Form/FormRow.vue";
import { useQuasar } from "quasar";
import useForm from "@/Hooks/useForm.js";

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

defineProps({
    currencies: {
        type: Array,
        required: true,
    },
});

const emit = defineEmits(["store", "cancel"]);

const form = useForm({
    title: "",
    currency: null,
    balance: "",
    color: "",
});
const quasar = useQuasar();

const save = () => {
    form.transform((data) => {
        return {
            ...data,
            currency: data.currency?.code,
        };
    }).post(route("accounts.store"), {
        onSuccess: () => {
            quasar.notify({
                color: "positive",
                message: "Account successfully created",
            });

            emit("store");
        },
        onError(errors) {
            if (!Object.values(errors).length) {
                quasar.notify({
                    color: "negative",
                    message: "Something went wrong",
                });
            }
        },
    });
};

const cancel = () => {
    emit("cancel");
};
</script>

<template>
    <Form title="Add" @submit="save">
        <FormRow label="Account name">
            <TextInput
                v-model="form.title"
                :error="form.errors.title"
                placeholder="Enter account name..."
            />
        </FormRow>

        <FormRow label="Currency">
            <SelectInput
                v-model="form.currency"
                :error="form.errors.currency"
                :options="currencies"
                option-label="name"
                option-value="code"
                placeholder="Choose a currency..."
            />
        </FormRow>

        <FormRow label="Amount">
            <NumberInput
                v-model="form.balance"
                :error="form.errors.balance"
                placeholder="Enter initial balance..."
            />
        </FormRow>

        <FormRow label="Account color">
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

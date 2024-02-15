<script setup>
import Form from "@/Components/UI/Form/Form.vue";
import TextInput from "@/Components/UI/Input/TextInput.vue";
import NumberInput from "@/Components/UI/Input/NumberInput.vue";
import SelectInput from "@/Components/UI/Input/SelectInput.vue";
import ColorInput from "@/Components/UI/Input/ColorInput.vue";
import FormRow from "@/Components/UI/Form/FormRow.vue";
import { useQuasar } from "quasar";
import useForm from "@/Hooks/useForm.js";
import FormButton from "@/Components/UI/Buttons/FormButton.vue";
import FormActions from "@/Components/UI/Form/FormActions.vue";

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

const props = defineProps({
    currencies: {
        type: Array,
        required: true,
    },
});

const emit = defineEmits(["save", "cancel"]);

const form = useForm({
    title: "",
    currency: null,
    balance: "",
    color: "",
});
const quasar = useQuasar();

const save = () => {
    const transformed = form.transform((data) => {
        return {
            ...data,
            currency: data.currency?.code,
        };
    });

    const options = {
        onSuccess: () => {
            quasar.notify({
                color: "positive",
                message: form.id
                    ? "Account successfully updated"
                    : "Account successfully created",
            });

            emit("save");
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

    if (form.id) {
        transformed.patch(
            route("accounts.update", { account: form.id }),
            options,
        );
    } else {
        transformed.post(route("accounts.store"), options);
    }
};

const cancel = () => {
    clear();
    emit("cancel");
};

const clear = () => {
    form.reset();
    form.id = null;
};

const setModel = (model) => {
    form.balance = +Number(model.balance || 0).toFixed(2);
    form.title = model.title;
    form.color = model.color;
    form.id = model.id;

    const currency = props.currencies.find(
        (currency) => currency.code === model.currency.code,
    );
    form.currency = currency || null;
};

defineExpose({ clear, setModel });
</script>

<template>
    <Form :title="form.id ? 'Edit' : 'Add'" @submit="save">
        <FormRow label="Account name">
            <TextInput
                v-model="form.title"
                :error="form.errors.title"
                placeholder="Enter account name..."
            />
        </FormRow>

        <FormRow v-if="!form.id" label="Currency">
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

        <FormActions>
            <FormButton type="submit">Save</FormButton>
            <FormButton type="reset" @click="cancel">Cancel</FormButton>
        </FormActions>
    </Form>
</template>

<style scoped></style>

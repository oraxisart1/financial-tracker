<script setup>
import Form from "@/Components/UI/Form/Form.vue";
import { useQuasar } from "quasar";
import { usePage } from "@inertiajs/vue3";
import useForm from "@/Hooks/useForm.js";
import { computed } from "vue";
import FormRow from "@/Components/UI/Form/FormRow.vue";
import SelectInput from "@/Components/UI/Input/SelectInput.vue";
import FormActions from "@/Components/UI/Form/FormActions.vue";
import FormButton from "@/Components/UI/Buttons/FormButton.vue";

const quasar = useQuasar();
const page = usePage();

const emit = defineEmits(["cancel", "save"]);

const currencyOptions = computed(() => page.props.currencies);
const userSettings = computed(() => page.props.userSettings);

const form = useForm({
    currency: currencyOptions.value.find(
        (o) => o.code === userSettings.value.default_currency.code,
    ),
});

const cancel = () => {
    clear();
    emit("cancel");
};

const clear = () => {
    form.reset();
};

const save = () => {
    form.transform((data) => ({ ...data, currency: data.currency.code })).patch(
        route("user-settings.update"),
        {
            onSuccess: () => {
                quasar.notify({
                    color: "positive",
                    message: "Profile successfully updated",
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
        },
    );
};
</script>

<template>
    <Form title="General Settings" @submit="save">
        <FormRow label="Default currency">
            <SelectInput
                v-model="form.currency"
                :error="form.errors.currency"
                :options="currencyOptions"
                option-label="name"
                option-value="code"
            />
        </FormRow>

        <FormActions>
            <FormButton type="submit">Save</FormButton>
            <FormButton @click="cancel">Cancel</FormButton>
        </FormActions>
    </Form>
</template>

<style scoped></style>

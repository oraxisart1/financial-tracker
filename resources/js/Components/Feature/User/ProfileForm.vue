<script setup>
import Form from "@/Components/UI/Form/Form.vue";
import TextInput from "@/Components/UI/Input/TextInput.vue";
import FormRow from "@/Components/UI/Form/FormRow.vue";
import useForm from "@/Hooks/useForm.js";
import { usePage } from "@inertiajs/vue3";
import { useQuasar } from "quasar";
import FormActions from "@/Components/UI/Form/FormActions.vue";
import FormButton from "@/Components/UI/Buttons/FormButton.vue";

const quasar = useQuasar();
const page = usePage();
const form = useForm({
    name: page.props.auth.user.name,
});

const emit = defineEmits(["cancel", "save"]);

const cancel = () => {
    clear();
    emit("cancel");
};

const clear = () => {
    form.reset();
};

const save = () => {
    form.patch(route("profile.update"), {
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
    });
};
</script>

<template>
    <Form title="Profile" @submit="save">
        <FormRow label="Name">
            <TextInput v-model="form.name" :error="form.errors.name" />
        </FormRow>

        <FormActions>
            <FormButton type="submit">Save</FormButton>
            <FormButton @click="cancel">Cancel</FormButton>
        </FormActions>
    </Form>
</template>

<style scoped></style>

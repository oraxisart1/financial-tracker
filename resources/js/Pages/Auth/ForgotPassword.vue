<script>
import GuestLayout from "@/Layouts/GuestLayout.vue";

export default {
    layout: GuestLayout,
};
</script>

<script setup>
import { Head } from "@inertiajs/vue3";
import FormButton from "@/Components/UI/Buttons/FormButton.vue";
import FormRow from "@/Components/UI/Form/FormRow.vue";
import FormActions from "@/Components/UI/Form/FormActions.vue";
import useForm from "@/Hooks/useForm.js";
import TextInput from "@/Components/UI/Input/TextInput.vue";
import { ref } from "vue";
import { Link } from "@inertiajs/vue3";
import Form from "@/Components/UI/Form/Form.vue";

defineProps({
    status: {
        type: String,
    },
});

const form = useForm({
    email: "",
});
const sent = ref(false);

const submit = () => {
    form.post(route("password.email"), {
        onSuccess: () => {
            sent.value = true;
        },
    });
};
</script>

<template>
    <Head title="Forgot Password" />

    <Form class="tw-w-5/12" title="Password recovery" @submit="submit">
        <template v-if="!sent">
            <FormRow label="E-mail">
                <TextInput
                    v-model="form.email"
                    :error="form.errors.email"
                    autocomplete="username"
                    autofocus
                    type="email"
                />
            </FormRow>

            <FormActions>
                <span>The password reset link will be send to your email.</span>
            </FormActions>

            <FormActions>
                <FormButton :active="false" type="submit">Send</FormButton>
            </FormActions>
        </template>

        <template v-else>
            <FormActions>
                <span>The password reset link was sent to your email.</span>
            </FormActions>

            <FormActions>
                <Link
                    :href="route('login')"
                    class="tw-bg-navigation-inactive tw-text-2xl tw-text-white tw-font-semibold tw-p-3 tw-rounded-md"
                    >To login page
                </Link>
            </FormActions>
        </template>
    </Form>
</template>

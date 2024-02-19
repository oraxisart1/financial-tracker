<script>
import GuestLayout from "@/Layouts/GuestLayout.vue";

export default {
    layout: GuestLayout,
};
</script>

<script setup>
import { Head, Link } from "@inertiajs/vue3";
import FormRow from "@/Components/UI/Form/FormRow.vue";
import Checkbox from "@/Components/Checkbox.vue";
import FormActions from "@/Components/UI/Form/FormActions.vue";
import FormButton from "@/Components/UI/Buttons/FormButton.vue";
import useForm from "@/Hooks/useForm.js";
import Form from "@/Components/UI/Form/Form.vue";
import TextInput from "@/Components/UI/Input/TextInput.vue";

const form = useForm({
    name: "",
    email: "",
    password: "",
    password_confirmation: "",
});

const submit = () => {
    form.post(route("register"), {
        onFinish: () => form.reset("password", "password_confirmation"),
    });
};
</script>

<template>
    <Head title="Register" />

    <Form class="tw-w-5/12" title="Sing up to FS" @submit="submit">
        <FormRow label="Profile name">
            <TextInput
                v-model="form.name"
                :error="form.errors.name"
                autofocus
            />
        </FormRow>

        <FormRow label="E-mail">
            <TextInput
                v-model="form.email"
                :error="form.errors.email"
                autocomplete="username"
                autofocus
                type="email"
            />
        </FormRow>

        <FormRow label="Password">
            <TextInput
                v-model="form.password"
                :error="form.errors.password"
                autocomplete="new-password"
                type="password"
            />
        </FormRow>

        <FormRow label="Confirm password">
            <TextInput
                v-model="form.password_confirmation"
                :error="form.errors.password"
                autocomplete="new-password"
                type="password"
            />
        </FormRow>

        <FormActions>
            <FormButton :active="false" type="submit" width="300px"
                >Create new account
            </FormButton>
        </FormActions>

        <FormActions>
            <div>
                <span>Already have an account?</span>
                <Link
                    :href="route('login')"
                    class="tw-underline tw-text-sm tw-font-semibold tw-ml-2"
                    >Sign in
                </Link>
            </div>
        </FormActions>
    </Form>
</template>

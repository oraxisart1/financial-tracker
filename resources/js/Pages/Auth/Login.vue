<script>
import GuestLayout from "@/Layouts/GuestLayout.vue";
import Form from "@/Components/UI/Form/Form.vue";

export default {
    layout: GuestLayout,
};
</script>

<script setup>
import Checkbox from "@/Components/Checkbox.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import { Head, Link, useForm } from "@inertiajs/vue3";
import TextInput from "@/Components/UI/Input/TextInput.vue";
import FormRow from "@/Components/UI/Form/FormRow.vue";
import FormActions from "@/Components/UI/Form/FormActions.vue";
import FormButton from "@/Components/UI/Buttons/FormButton.vue";

const form = useForm({
    email: "",
    password: "",
    remember: true,
});

const submit = () => {
    form.post(route("login"), {
        onFinish: () => form.reset("password"),
    });
};
</script>

<template>
    <Head title="Log in" />

    <Form class="tw-w-5/12" title="Sing in to FS" @submit="submit">
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
                autocomplete="current-password"
                type="password"
            />
        </FormRow>

        <FormRow>
            <div class="tw-flex tw-justify-between tw-items-center">
                <label class="tw-flex tw-items-center">
                    <Checkbox v-model:checked="form.remember" name="remember" />
                    <span class="tw-ms-2 tw-text-sm tw-text-gray-600"
                        >Remember</span
                    >
                </label>

                <Link
                    :href="route('password.request')"
                    class="tw-underline tw-text-sm tw-font-semibold"
                >
                    Forgot your password?
                </Link>
            </div>
        </FormRow>

        <FormActions>
            <FormButton :active="false" type="submit">Sign in</FormButton>
        </FormActions>

        <FormActions>
            <div>
                <span>Don't have an account?</span>
                <Link
                    :href="route('register')"
                    class="tw-underline tw-text-sm tw-font-semibold tw-ml-2"
                    >Register
                </Link>
            </div>
        </FormActions>
    </Form>

    <!--    <form class="tw-w-1/2" @submit.prevent="submit">-->
    <!--        <div></div>-->

    <!--        <div class="tw-mt-4"></div>-->

    <!--        <div class="tw-block tw-mt-4">-->
    <!--            <label class="tw-flex tw-items-center">-->
    <!--                <Checkbox v-model:checked="form.remember" name="remember" />-->
    <!--                <span class="tw-ms-2 tw-text-sm tw-text-gray-600"-->
    <!--                    >Remember me</span-->
    <!--                >-->
    <!--            </label>-->
    <!--        </div>-->

    <!--        <div class="tw-flex tw-items-center tw-justify-end tw-mt-4">-->
    <!--            <Link-->
    <!--                :href="route('password.request')"-->
    <!--                class="tw-underline tw-text-sm tw-text-gray-600 tw-hover:text-gray-900 tw-rounded-md tw-focus:outline-none tw-focus:ring-2 tw-focus:ring-offset-2 tw-focus:ring-indigo-500"-->
    <!--            >-->
    <!--                Forgot your password?-->
    <!--            </Link>-->

    <!--            <PrimaryButton-->
    <!--                :class="{ 'tw-opacity-25': form.processing }"-->
    <!--                :disabled="form.processing"-->
    <!--                class="tw-ms-4"-->
    <!--            >-->
    <!--                Log in-->
    <!--            </PrimaryButton>-->
    <!--        </div>-->
    <!--    </form>-->
</template>

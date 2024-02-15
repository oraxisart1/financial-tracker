<script setup>
import Form from "@/Components/UI/Form/Form.vue";
import TextInput from "@/Components/UI/Input/TextInput.vue";
import FormRow from "@/Components/UI/Form/FormRow.vue";
import useForm from "@/Hooks/useForm.js";
import { usePage } from "@inertiajs/vue3";
import { useQuasar } from "quasar";
import { watch } from "vue";

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

        <div class="tw-justify-center tw-flex tw-gap-12 tw-col-span-full">
            <button
                class="tw-bg-navigation tw-text-white tw-py-3 tw-w-[200px] tw-text-2xl tw-font-medium tw-rounded-lg tw-shadow-lg hover:tw-bg-navigation-inactive focus:tw-bg-navigation-inactive"
                type="submit"
            >
                Save
            </button>

            <button
                class="tw-bg-navigation-inactive tw-text-white tw-py-3 tw-w-[200px] tw-text-2xl tw-font-medium tw-rounded-lg tw-shadow-lg hover:tw-bg-navigation focus:tw-bg-navigation"
                type="button"
                @click="cancel"
            >
                Cancel
            </button>
        </div>
    </Form>
</template>

<style scoped></style>

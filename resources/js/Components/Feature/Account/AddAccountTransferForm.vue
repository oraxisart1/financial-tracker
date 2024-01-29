<script setup>
import useForm from "@/Hooks/useForm.js";
import Form from "@/Components/UI/Form/Form.vue";
import FormRow from "@/Components/UI/Form/FormRow.vue";
import SelectInput from "@/Components/UI/Input/SelectInput.vue";
import { computed } from "vue";
import DateInput from "@/Components/UI/Input/DateInput.vue";
import TextInput from "@/Components/UI/Input/TextInput.vue";
import NumberInput from "@/Components/UI/Input/NumberInput.vue";
import { useQuasar } from "quasar";
import { format } from "date-fns";

const props = defineProps({
    accounts: {
        type: Array,
        required: true,
    },
});

const emit = defineEmits(["store", "cancel"]);

const form = useForm({
    accountFrom: null,
    accountTo: null,
    amount: "",
    date: format(new Date(), "yyyy-MM-dd"),
    comment: "",
    convertedAmount: "",
});
const quasar = useQuasar();

const accountFromOptions = computed(() => {
    if (!form.accountTo) {
        return props.accounts;
    }

    return props.accounts.filter((acc) => acc.id !== form.accountTo.id);
});

const accountToOptions = computed(() => {
    if (!form.accountFrom) {
        return props.accounts;
    }

    return props.accounts.filter((acc) => acc.id !== form.accountFrom.id);
});

const save = () => {
    form.transform((data) => {
        return {
            ...data,
            account_from_id: data.accountFrom?.id,
            account_to_id: data.accountTo?.id,
            converted_amount: data.convertedAmount,
        };
    }).post(route("account-transfers.store"), {
        onSuccess: () => {
            quasar.notify({
                color: "positive",
                message: "Transfer successfully created",
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
</script>

<template>
    <Form title="Transfer between accounts" @submit="save">
        <FormRow label="Transfer">
            <div class="tw-flex tw-justify-between tw-items-center tw-gap-9">
                <div class="tw-flex tw-flex-1 tw-items-center tw-gap-2.5">
                    <span>from</span>
                    <SelectInput
                        v-model="form.accountFrom"
                        :error="form.errors.account_from_id"
                        :options="accountFromOptions"
                        class="tw-w-full"
                        option-label="title"
                        option-value="id"
                        placeholder="Select account..."
                    />
                </div>

                <div class="tw-flex tw-flex-1 tw-items-center tw-gap-2.5">
                    <span>to</span>
                    <SelectInput
                        v-model="form.accountTo"
                        :error="form.errors.account_to_id"
                        :options="accountToOptions"
                        class="tw-w-full"
                        option-label="title"
                        option-value="id"
                        placeholder="Select account..."
                    />
                </div>
            </div>
        </FormRow>

        <FormRow label="Amount">
            <template
                v-if="
                    !form.accountFrom ||
                    !form.accountTo ||
                    form.accountFrom?.currency.code ===
                        form.accountTo?.currency.code
                "
            >
                <NumberInput
                    v-model="form.amount"
                    :error="form.errors.amount"
                />
            </template>

            <template v-else>
                <div
                    class="tw-flex tw-justify-between tw-items-center tw-gap-4"
                >
                    <div class="tw-flex tw-flex-1 tw-items-center tw-gap-2.5">
                        <NumberInput
                            v-model="form.amount"
                            :error="form.errors.amount"
                            class="full-width"
                        />

                        <span class="tw-text-md">{{
                            form.accountFrom.currency.code
                        }}</span>
                    </div>

                    <span>=</span>

                    <div class="tw-flex tw-flex-1 tw-items-center tw-gap-2.5">
                        <NumberInput
                            v-model="form.convertedAmount"
                            :error="form.errors.converted_amount"
                            class="full-width"
                        />

                        <span class="tw-text-md">{{
                            form.accountTo.currency.code
                        }}</span>
                    </div>
                </div>
            </template>
        </FormRow>

        <FormRow label="Date">
            <DateInput v-model="form.date" :error="form.errors.date" />
        </FormRow>

        <FormRow label="Comment">
            <TextInput v-model="form.comment" />
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
                @click="emit('cancel')"
            >
                Cancel
            </button>
        </div>
    </Form>
</template>

<style scoped></style>

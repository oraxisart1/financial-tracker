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
import FormButton from "@/Components/UI/Buttons/FormButton.vue";
import FormActions from "@/Components/UI/Form/FormActions.vue";

const props = defineProps({
    accounts: {
        type: Array,
        required: true,
    },
});

const emit = defineEmits(["save", "cancel"]);

const form = useForm({
    accountFrom: null,
    accountTo: null,
    amount: "",
    date: format(new Date(), "yyyy-MM-dd"),
    description: "",
    convertedAmount: "",
    id: null,
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
    const transformed = form.transform((data) => {
        return {
            ...data,
            account_from_id: data.accountFrom?.id,
            account_to_id: data.accountTo?.id,
            converted_amount: data.convertedAmount,
        };
    });

    const options = {
        onSuccess: () => {
            quasar.notify({
                color: "positive",
                message: form.id
                    ? "Transfer successfully updated"
                    : "Transfer successfully created",
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
            route("account-transfers.update", { account_transfer: form.id }),
            options,
        );
    } else {
        transformed.post(route("account-transfers.store"), options);
    }
};

const setModel = (transfer) => {
    const accountFrom = props.accounts.find(
        (accountFrom) => accountFrom.id === transfer.account_from.id,
    );
    form.accountFrom = accountFrom || null;

    const accountTo = props.accounts.find(
        (accountTo) => accountTo.id === transfer.account_to.id,
    );
    form.accountTo = accountTo || null;

    form.amount = +Number(transfer.amount || 0).toFixed(2);
    if (accountFrom?.currency.code !== accountTo?.currency.code) {
        form.convertedAmount = +Number(transfer.converted_amount || 0).toFixed(
            2,
        );
    }

    form.date = transfer.date;
    form.description = transfer.description;
    form.id = transfer.id;
};

const clear = () => {
    form.reset();
};

defineExpose({ setModel, clear });
</script>

<template>
    <Form
        v-key-press="{ escape: () => emit('cancel') }"
        title="Transfer between accounts"
        @submit="save"
    >
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
            <TextInput v-model="form.description" />
        </FormRow>

        <FormActions>
            <FormButton type="submit">Save</FormButton>
            <FormButton type="reset" @click="emit('cancel')">Cancel</FormButton>
        </FormActions>
    </Form>
</template>

<style scoped></style>

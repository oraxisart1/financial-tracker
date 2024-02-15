<script setup>
import { usePage } from "@inertiajs/vue3";
import { format } from "date-fns";
import { useQuasar } from "quasar";
import NumberInput from "@/Components/UI/Input/NumberInput.vue";
import DateInput from "@/Components/UI/Input/DateInput.vue";
import TextInput from "@/Components/UI/Input/TextInput.vue";
import SelectInput from "@/Components/UI/Input/SelectInput.vue";
import { watch } from "vue";
import useForm from "@/Hooks/useForm.js";
import FormButton from "@/Components/UI/Buttons/FormButton.vue";
import FormActions from "@/Components/UI/Form/FormActions.vue";

const props = defineProps({
    transactionType: { type: String },
    categories: { type: Array },
    accounts: { type: Array },
});

const form = useForm({
    date: format(new Date(), "yyyy-MM-dd"),
    account: null,
    amount: "",
    category_id: "",
    description: "",
});

const page = usePage();

watch(
    () => page.props.query.transaction_type,
    () => {
        form.category_id = "";
        if (form.id) {
            cancel();
        }
    },
);

const emit = defineEmits(["cancel", "save"]);
const quasar = useQuasar();

const cancel = () => {
    clear();
    emit("cancel");
};

const clear = () => {
    form.reset();
    form.id = null;
};

const selectCategory = (categoryId) => {
    form.category_id = categoryId;
};

const save = () => {
    const transformed = form.transform((data) => {
        return {
            ...data,
            account_id: data.account?.id,
            currency: data.account?.currency?.code,
            type: props.transactionType,
        };
    });

    const options = {
        onSuccess: () => {
            quasar.notify({
                color: "positive",
                message: form.id
                    ? "Transaction successfully updated"
                    : "Transaction successfully created",
            });

            emit("save");
            clear();
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
            route("transactions.update", { transaction: form.id }),
            options,
        );
    } else {
        transformed.post(route("transactions.store"), options);
    }
};

const setModel = (model) => {
    form.amount = +Number(model.amount || 0).toFixed(2);
    form.date = model.date;
    form.description = model.description || "";
    form.category_id = model.category.id;
    form.id = model.id;

    const account = props.accounts.find(
        (acc) => Number(acc.id) === Number(model.account.id),
    );
    form.account = account || null;
};

defineExpose({ setModel, clear });
</script>

<template>
    <div
        v-key-press="{ escape: cancel }"
        class="tw-flex tw-flex-col tw-rounded-md tw-bg-light-pastel tw-max-h-full tw-min-h-full tw-overflow-y-auto"
    >
        <div
            class="tw-text-white tw-p-3 tw-font-medium tw-text-2xl tw-text-center tw-bg-teal tw-rounded-t-md"
        >
            {{ form.id ? "Edit" : "Add" }}
            {{ transactionType === "expense" ? "Expense" : "Income" }}
        </div>

        <form
            class="tw-py-4 tw-px-8 tw-grid tw-grid-cols-[100px_1fr] tw-items-center tw-gap-y-6 tw-gap-x-12"
            @submit.prevent="save"
        >
            <span class="tw-text-right">Date</span>
            <DateInput
                v-model="form.date"
                :error="form.errors.date"
                label="Date"
            />

            <span class="tw-text-right">Account</span>
            <SelectInput
                v-model="form.account"
                :error="form.errors.account_id"
                :options="accounts"
                label="Account"
                no-options-label="You don't have any accounts created"
                option-label="title"
                option-value="id"
                placeholder="Select account..."
            />

            <span class="tw-text-right">Amount</span>
            <div class="tw-flex tw-gap-4 tw-items-center">
                <div class="tw-flex-grow">
                    <NumberInput
                        v-model.number="form.amount"
                        :error="form.errors.amount"
                        label="Amount"
                        placeholder="Enter transaction amount..."
                    />
                </div>

                <div v-if="form.account" class="tw-text-md">
                    {{ form.account.currency.code }}
                </div>
            </div>

            <span class="tw-text-right">Category</span>
            <div class="tw-flex tw-flex-col tw-gap-1">
                <div
                    v-if="categories.length"
                    :class="`tw-grid tw-grid-cols-5 tw-gap-y-4 tw-flex-grow tw-w-full tw-border-2 tw-rounded-md tw-p-2 tw-bg-light-pastel tw-overflow-y-auto tw-max-h-48 ${
                        form.errors.category_id
                            ? 'tw-border-red-600'
                            : 'tw-border-light'
                    }`"
                >
                    <div
                        v-for="category in categories"
                        class="tw-flex tw-flex-col tw-items-center tw-gap-2 tw-cursor-pointer"
                        @click="selectCategory(category.id)"
                    >
                        <div
                            :class="`tw-w-12 tw-h-12 tw-rounded-full ${
                                category.id === form.category_id
                                    ? 'tw-ring-navigation tw-ring-4'
                                    : ''
                            }`"
                            :style="{ backgroundColor: category.color }"
                        ></div>

                        <span
                            :class="`tw-text-center ${
                                category.id === form.category_id
                                    ? 'tw-font-bold'
                                    : ''
                            }`"
                            >{{ category.title }}</span
                        >
                    </div>
                </div>

                <q-btn
                    v-else
                    :class="`!tw-bg-pastel !tw-py-4 !tw-rounded-md ${
                        form.errors.category_id
                            ? '!tw-border-2 !tw-border-solid !tw-border-red-600'
                            : ''
                    }`"
                    icon="add"
                    no-caps
                    unelevated
                    >Create new category
                </q-btn>

                <div
                    v-if="form.errors.category_id"
                    class="tw-text-xs tw-ml-2 tw-text-red-600"
                >
                    {{ form.errors.category_id }}
                </div>
            </div>

            <span class="tw-text-right">Description</span>
            <div>
                <TextInput
                    v-model="form.description"
                    label="Description"
                    placeholder="Enter transaction description..."
                />
            </div>

            <FormActions>
                <FormButton type="submit">Save</FormButton>
                <FormButton type="reset" @click="cancel">Cancel</FormButton>
            </FormActions>
        </form>
    </div>
</template>

<style scoped></style>

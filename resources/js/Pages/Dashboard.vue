<script setup>
import { Head, router, usePage } from "@inertiajs/vue3";
import { computed, ref, watch } from "vue";
import TransactionsTable from "@/Components/Feature/Transaction/TransactionsTable.vue";
import AddTransactionButton from "@/Components/Feature/Transaction/AddTransactionButton.vue";
import TransactionForm from "@/Components/Feature/Transaction/TransactionForm.vue";
import { formatCurrency } from "../Helpers/number.js";
import { format, lastDayOfMonth } from "date-fns";
import DateRange from "@/Components/UI/Input/DateRange.vue";
import TransactionsChart from "@/Components/Feature/Transaction/TransactionsChart.vue";
import BudgetChart from "@/Components/Feature/Budget/BudgetChart.vue";

const page = usePage();

const transactionForm = ref(null);
const showTransactionForm = ref(false);
const filter = ref({
    transactionType: page.props.query.transaction_type,
    category: Number(page.props.query.category || ""),
    date: [
        page.props.query.date_from || format(new Date(), "yyyy-MM-01"),
        page.props.query.date_to ||
            format(lastDayOfMonth(new Date()), "yyyy-MM-dd"),
    ],
    allTime: Boolean(page.props.query.all_time),
});

const serializedFilter = computed(() => JSON.stringify(filter.value));

const onCategorySelect = (categoryId) => {
    if (categoryId === filter.value.category) {
        return;
    }

    filter.value.category = categoryId;
};

const openTransactionForm = () => {
    transactionForm.value.clear();
    showTransactionForm.value = true;
};

const closeTransactionForm = () => {
    showTransactionForm.value = false;
};

const onTransactionEdit = (row) => {
    transactionForm.value.setModel(row);
    showTransactionForm.value = true;
};

watch(serializedFilter, (value, oldValue) => {
    value = JSON.parse(value);
    oldValue = JSON.parse(oldValue);

    const isTypeChanged = value.transactionType !== oldValue.transactionType;
    if (isTypeChanged) {
        if (filter.value.category) {
            filter.value.category = 0;
            return;
        }
    }

    let params = {
        transaction_type: filter.value.transactionType,
    };

    if (filter.value.allTime) {
        params.all_time = Number(filter.value.allTime);
    } else {
        params = {
            ...params,
            date_from: filter.value.date[0],
            date_to: filter.value.date[1],
        };
    }

    if (filter.value.category) {
        params.category = filter.value.category;
    }

    router.get(route("dashboard"), params, {
        preserveState: true,
        replace: true,
    });
});
</script>

<template>
    <Head title="Dashboard" />

    <q-tabs
        v-model="filter.transactionType"
        active-class="tw-bg-navigation"
        class="tw-mb-4"
        content-class="tw-flex tw-text-white tw-bg-navigation-inactive"
        indicator-color="transparent"
        no-caps
    >
        <q-tab
            class="tw-text-center tw-flex-grow tw-flex-1 tw-font-semibold tw-text-3xl !tw-py-0.5"
            name="expense"
        >
            Expenses
        </q-tab>

        <q-tab
            class="tw-text-center tw-flex-grow tw-flex-1 tw-font-semibold tw-text-3xl !tw-py-0.5"
            name="income"
        >
            Incomes
        </q-tab>
    </q-tabs>

    <div :class="`tw-flex tw-p-4 tw-gap-x-8 tw-max-h-[89%] tw-min-h-[89%]`">
        <div
            class="tw-flex-grow tw-flex-1 tw-flex tw-flex-col tw-justify-between tw-items-center tw-gap-8"
        >
            <AddTransactionButton @click="openTransactionForm" />

            <div
                class="tw-bg-light-pastel tw-basis-1/2 tw-w-full tw-flex-grow tw-rounded-md tw-flex tw-flex-col tw-items-center tw-p-2 tw-gap-2.5"
            >
                <DateRange
                    v-model="filter.date"
                    v-model:all-time="filter.allTime"
                />

                <div class="tw-text-2xl tw-font-medium">
                    <span>
                        {{
                            filter.transactionType === "expense"
                                ? "Expenses"
                                : "Incomes"
                        }}
                    </span>
                    <span>: </span>
                    <span>{{
                        formatCurrency(
                            page.props.transactions.reduce(
                                (acc, el) => acc + Number(el.amount),
                                0,
                            ),
                        )
                    }}</span>
                </div>

                <TransactionsChart :transactions="page.props.transactions" />
            </div>

            <div
                class="tw-bg-light-pastel tw-basis-1/4 tw-w-full tw-rounded-md tw-flex tw-justify-center"
            >
                <BudgetChart />
            </div>
        </div>

        <div class="tw-flex-grow tw-flex-1">
            <TransactionsTable
                v-show="!showTransactionForm"
                :categories="page.props.categories"
                :filter="filter"
                :transactions="page.props.transactions"
                @select-category="onCategorySelect"
                @edit-click="onTransactionEdit"
            />

            <TransactionForm
                v-show="showTransactionForm"
                ref="transactionForm"
                :accounts="page.props.accounts"
                :categories="page.props.categories"
                :transaction-type="filter.transactionType"
                @cancel="closeTransactionForm"
                @save="closeTransactionForm"
            />
        </div>
    </div>
</template>

<style scoped></style>

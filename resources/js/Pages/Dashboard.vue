<script setup>
import { Head, router, usePage } from "@inertiajs/vue3";
import { computed, ref, watch } from "vue";
import TransactionsTable from "@/Components/Feature/Transaction/TransactionsTable.vue";
import AddTransactionButton from "@/Components/Feature/Transaction/AddTransactionButton.vue";
import AddTransactionForm from "@/Components/Feature/Transaction/AddTransactionForm.vue";

const page = usePage();

const showAddForm = ref(false);
const filter = ref({
    transactionType: page.props.query.transaction_type,
    category: Number(page.props.query.category || ""),
});

const serializedFilter = computed(() => JSON.stringify(filter.value));

const onCategorySelect = (categoryId) => {
    if (categoryId === filter.value.category) {
        return;
    }

    filter.value.category = categoryId;
};

const openAddForm = () => {
    showAddForm.value = true;
};

const closeAddForm = () => {
    showAddForm.value = false;
};

watch(serializedFilter, (value, oldValue) => {
    value = JSON.parse(value);
    oldValue = JSON.parse(oldValue);

    const isTypeChanged = value.transactionType !== oldValue.transactionType;
    if (isTypeChanged) {
        // showAddForm.value = false;

        if (filter.value.category) {
            filter.value.category = 0;
            return;
        }
    }

    const params = {
        transaction_type: filter.value.transactionType,
    };

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
        content-class="tw-flex tw-mt-4 tw-text-white tw-bg-navigation-inactive"
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

    <div
        :class="`tw-flex tw-p-4 tw-gap-x-8 tw-min-h-0 ${
            showAddForm ? '' : 'tw-max-h-[89%] tw-min-h-[89%]'
        }`"
    >
        <div
            class="tw-flex-grow tw-flex-1 tw-flex tw-flex-col tw-justify-between tw-items-center"
        >
            <AddTransactionButton @click="openAddForm" />
        </div>

        <div class="tw-flex-grow tw-flex-1">
            <TransactionsTable
                v-show="!showAddForm"
                :categories="page.props.categories"
                :filter="filter"
                :transactions="page.props.transactions"
                @select-category="onCategorySelect"
            />

            <AddTransactionForm
                v-if="showAddForm"
                :accounts="page.props.accounts"
                :categories="page.props.categories"
                :transaction-type="filter.transactionType"
                @cancel="closeAddForm"
                @store="closeAddForm"
            />
        </div>
    </div>
</template>

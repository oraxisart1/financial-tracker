<script setup>
import { Head, router, usePage } from "@inertiajs/vue3";
import { computed, ref, watch } from "vue";
import TransactionsTable from "@/Components/Feature/Transaction/TransactionsTable.vue";

const page = usePage();

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

watch(serializedFilter, (value, oldValue) => {
    value = JSON.parse(value);
    oldValue = JSON.parse(oldValue);
    if (
        value.transactionType !== oldValue.transactionType &&
        filter.value.category
    ) {
        filter.value.category = 0;
        return;
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
            class="tw-py-2 tw-text-center tw-flex-grow tw-flex-1 tw-font-medium tw-text-xl"
            name="expense"
        >
            Expenses
        </q-tab>

        <q-tab
            class="tw-py-2 tw-text-center tw-flex-grow tw-flex-1 tw-font-medium tw-text-xl"
            name="income"
        >
            Incomes
        </q-tab>
    </q-tabs>

    <div class="tw-flex tw-p-4 tw-gap-x-8 tw-max-h-[90%] tw-min-h-0">
        <div class="tw-flex-grow tw-flex-1"></div>

        <TransactionsTable
            :categories="page.props.categories"
            :filter="filter"
            :transactions="page.props.transactions"
            @select-category="onCategorySelect"
        />
    </div>
</template>

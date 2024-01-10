<script setup>
import { Head, router, usePage } from "@inertiajs/vue3";
import { computed, ref, watch } from "vue";
import { guessFontColorByBackgroundColor } from "@/Helpers/color.js";
import { formatCurrency } from "@/Helpers/number.js";

const page = usePage();

const filter = ref({
    transactionType: page.props.query.transaction_type,
    category: Number(page.props.query.category || ""),
});

const transactions = computed(() => {
    const result = {};
    for (const transaction of page.props.transactions) {
        if (!Array.isArray(result[transaction.date])) {
            result[transaction.date] = [];
        }

        result[transaction.date].push(transaction);
    }

    return result;
});

const categories = computed(() => {
    return [
        { title: "All", color: "#D70040", id: 0 },
        ...page.props.categories,
    ];
});

const serializedFilter = computed(() => JSON.stringify(filter.value));

const onCategoryClick = (categoryId) => {
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

        <div
            class="tw-flex-grow tw-flex-1 tw-flex tw-flex-col tw-rounded-md tw-bg-light-pastel"
        >
            <div
                class="tw-text-white tw-p-3 tw-font-medium tw-text-xl tw-text-center tw-bg-teal tw-rounded-t-md"
            >
                {{
                    filter.transactionType === "expense"
                        ? "Expenses"
                        : "Incomes"
                }}
            </div>

            <div class="tw-flex tw-overflow-hidden tw-gap-0.5 tw-divide-x-2">
                <q-list
                    class="tw-overflow-y-auto tw-space-y-1 tw-pt-1 tw-basis-2/3"
                >
                    <template
                        v-for="(transactions, date) in transactions"
                        :key="date"
                    >
                        <div class="tw-text-center tw-font-medium tw-underline">
                            {{ date }}
                        </div>

                        <q-item
                            v-for="transaction in transactions"
                            :key="transaction.id"
                            class="tw-flex tw-items-center tw-bg-pastel"
                        >
                            <q-item-section avatar>
                                <div
                                    :style="{
                                        backgroundColor:
                                            transaction.category.color,
                                    }"
                                    class="tw-w-7 tw-h-7 tw-rounded-full"
                                ></div>
                            </q-item-section>

                            <q-item-section
                                class="tw-flex tw-flex-col tw-justify-between tw-gap-2"
                            >
                                <span>{{ transaction.category.title }}</span>
                                <span class="tw-text-xs tw-break-all">{{
                                    transaction.description
                                }}</span>
                            </q-item-section>

                            <q-item-section
                                class="tw-justify-between tw-items-center tw-ml-auto !tw-text-inherit"
                                side
                            >
                                <span>{{
                                    formatCurrency(
                                        transaction.amount,
                                        transaction.currency.code,
                                    )
                                }}</span>
                                <span class="tw-text-xs">{{
                                    transaction.account.title
                                }}</span>
                            </q-item-section>
                        </q-item>
                    </template>
                </q-list>

                <div class="tw-basis-1/3">
                    <div
                        v-for="category in categories"
                        :style="{
                            backgroundColor:
                                category.id === filter.category
                                    ? '#B5D2D1'
                                    : category.color,
                            color: guessFontColorByBackgroundColor(
                                category.id === filter.category
                                    ? '#B5D2D1'
                                    : category.color,
                            ),
                        }"
                        class="tw-text-center tw-p-4 tw-cursor-pointer"
                        @click="onCategoryClick(category.id)"
                    >
                        {{ category.title }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { guessFontColorByBackgroundColor } from "@/Helpers/color.js";
import { computed } from "vue";
import { formatCurrency } from "@/Helpers/number.js";
import { format } from "date-fns";

const props = defineProps({
    filter: { type: Object },
    categories: { type: Object },
    transactions: { type: Object },
});

const emit = defineEmits(["select-category"]);

const transactions = computed(() => {
    const result = {};
    for (const transaction of props.transactions) {
        if (!Array.isArray(result[transaction.date])) {
            result[transaction.date] = [];
        }

        result[transaction.date].push(transaction);
    }

    return result;
});

const categories = computed(() => {
    return [{ title: "All", color: "#D70040", id: 0 }, ...props.categories];
});

const onCategoryClick = (categoryId) => {
    emit("select-category", categoryId);
};
</script>

<template>
    <div
        class="tw-grid tw-grid-rows-[60px_1fr] tw-grid-cols-[4fr, 1fr] tw-rounded-md tw-bg-light-pastel tw-max-h-full tw-min-h-full"
    >
        <div
            class="tw-text-white tw-p-3 tw-font-medium tw-text-2xl tw-text-center tw-bg-teal tw-rounded-t-md tw-col-span-full"
        >
            {{ filter.transactionType === "expense" ? "Expenses" : "Incomes" }}
        </div>

        <div class="tw-flex tw-overflow-hidden tw-gap-0.5 tw-divide-x-2">
            <q-list
                class="tw-overflow-y-auto tw-space-y-0.5 tw-pt-1 tw-basis-3/4"
            >
                <template
                    v-for="(transactions, date) in transactions"
                    :key="date"
                >
                    <div class="tw-text-center tw-underline">
                        {{ format(date, "PP") }}
                    </div>

                    <q-item
                        v-for="transaction in transactions"
                        :key="transaction.id"
                        class="tw-flex tw-items-center tw-bg-pastel"
                    >
                        <q-item-section avatar>
                            <div
                                :style="{
                                    backgroundColor: transaction.category.color,
                                }"
                                class="tw-w-9 tw-h-9 tw-rounded-full"
                            ></div>
                        </q-item-section>

                        <q-item-section
                            class="tw-flex tw-flex-col tw-justify-between tw-gap-1"
                        >
                            <span>{{ transaction.category.title }}</span>
                            <span class="tw-text-xs tw-break-all">{{
                                transaction.description
                            }}</span>
                        </q-item-section>

                        <q-item-section
                            class="tw-flex tw-flex-col tw-justify-between tw-items-center tw-ml-auto !tw-text-inherit tw-gap-1"
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

            <div
                class="tw-basis-1/4 tw-flex tw-flex-col justify-between tw-gap-2"
            >
                <div class="tw-overflow-y-scroll">
                    <div
                        v-for="category in categories"
                        :class="`tw-text-center tw-px-4 tw-cursor-pointer tw-text-md tw-font-medium ${
                            category.id === filter.category
                                ? 'tw-py-4.5'
                                : 'tw-py-2.5'
                        }`"
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
                        @click="onCategoryClick(category.id)"
                    >
                        {{ category.title }}
                    </div>
                </div>

                <q-btn class="!tw-bg-pastel" icon="add" unelevated></q-btn>
            </div>
        </div>
    </div>
    <!--    <div-->
    <!--        class="tw-flex tw-flex-col tw-rounded-md tw-bg-light-pastel tw-max-h-full tw-min-h-full"-->
    <!--    >-->
    <!--        <div-->
    <!--            class="tw-text-white tw-p-3 tw-font-medium tw-text-2xl tw-text-center tw-bg-teal tw-rounded-t-md"-->
    <!--        >-->
    <!--            {{ filter.transactionType === "expense" ? "Expenses" : "Incomes" }}-->
    <!--        </div>-->

    <!--        <div class="tw-flex tw-overflow-hidden tw-gap-0.5 tw-divide-x-2">-->
    <!--            <q-list-->
    <!--                class="tw-overflow-y-auto tw-space-y-0.5 tw-pt-1 tw-basis-3/4"-->
    <!--            >-->
    <!--                <template-->
    <!--                    v-for="(transactions, date) in transactions"-->
    <!--                    :key="date"-->
    <!--                >-->
    <!--                    <div class="tw-text-center tw-underline">-->
    <!--                        {{ format(date, "PP") }}-->
    <!--                    </div>-->

    <!--                    <q-item-->
    <!--                        v-for="transaction in transactions"-->
    <!--                        :key="transaction.id"-->
    <!--                        class="tw-flex tw-items-center tw-bg-pastel"-->
    <!--                    >-->
    <!--                        <q-item-section avatar>-->
    <!--                            <div-->
    <!--                                :style="{-->
    <!--                                    backgroundColor: transaction.category.color,-->
    <!--                                }"-->
    <!--                                class="tw-w-9 tw-h-9 tw-rounded-full"-->
    <!--                            ></div>-->
    <!--                        </q-item-section>-->

    <!--                        <q-item-section-->
    <!--                            class="tw-flex tw-flex-col tw-justify-between tw-gap-1"-->
    <!--                        >-->
    <!--                            <span>{{ transaction.category.title }}</span>-->
    <!--                            <span class="tw-text-xs tw-break-all">{{-->
    <!--                                transaction.description-->
    <!--                            }}</span>-->
    <!--                        </q-item-section>-->

    <!--                        <q-item-section-->
    <!--                            class="tw-flex tw-flex-col tw-justify-between tw-items-center tw-ml-auto !tw-text-inherit tw-gap-1"-->
    <!--                            side-->
    <!--                        >-->
    <!--                            <span>{{-->
    <!--                                formatCurrency(-->
    <!--                                    transaction.amount,-->
    <!--                                    transaction.currency.code,-->
    <!--                                )-->
    <!--                            }}</span>-->
    <!--                            <span class="tw-text-xs">{{-->
    <!--                                transaction.account.title-->
    <!--                            }}</span>-->
    <!--                        </q-item-section>-->
    <!--                    </q-item>-->
    <!--                </template>-->
    <!--            </q-list>-->

    <!--            <div-->
    <!--                class="tw-basis-1/4 tw-flex tw-flex-col justify-between tw-gap-2"-->
    <!--            >-->
    <!--                <div class="tw-overflow-y-scroll">-->
    <!--                    <div-->
    <!--                        v-for="category in categories"-->
    <!--                        :class="`tw-text-center tw-px-4 tw-cursor-pointer tw-text-md tw-font-medium ${-->
    <!--                            category.id === filter.category-->
    <!--                                ? 'tw-py-4.5'-->
    <!--                                : 'tw-py-2.5'-->
    <!--                        }`"-->
    <!--                        :style="{-->
    <!--                            backgroundColor:-->
    <!--                                category.id === filter.category-->
    <!--                                    ? '#B5D2D1'-->
    <!--                                    : category.color,-->
    <!--                            color: guessFontColorByBackgroundColor(-->
    <!--                                category.id === filter.category-->
    <!--                                    ? '#B5D2D1'-->
    <!--                                    : category.color,-->
    <!--                            ),-->
    <!--                        }"-->
    <!--                        @click="onCategoryClick(category.id)"-->
    <!--                    >-->
    <!--                        {{ category.title }}-->
    <!--                    </div>-->
    <!--                </div>-->

    <!--                <q-btn class="!tw-bg-pastel" icon="add" unelevated></q-btn>-->
    <!--            </div>-->
    <!--        </div>-->
    <!--    </div>-->
</template>

<style scoped></style>

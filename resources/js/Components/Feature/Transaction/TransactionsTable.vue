<script setup>
import { computed, ref, watch } from "vue";
import { guessFontColorByBackgroundColor } from "@/Helpers/color.js";
import Table from "@/Components/UI/Table.vue";
import MenuButton from "@/Components/UI/Buttons/MenuButton.vue";
import { router } from "@inertiajs/vue3";
import { useQuasar } from "quasar";
import ConfirmationDialog from "@/Components/UI/Dialog/ConfirmationDialog.vue";
import axios from "axios";
import { format } from "date-fns";
import { formatCurrency } from "@/Helpers/number.js";
import { PlusIcon } from "@heroicons/vue/20/solid/index.js";
import { Link } from "@inertiajs/vue3";

const rowButtons = [
    {
        label: "Edit",
        icon: "PencilIcon",
        onClick: (row) => {
            emit("edit-click", row);
        },
    },
    {
        label: "Delete",
        icon: "TrashIcon",
        onClick: async (row) => {
            const ok = await confirmationDialog.value.open({
                title: "Are you sure you want to delete this transaction?",
                message: "This will delete this transaction permanently.",
            });

            if (ok) {
                router.delete(
                    route("transactions.destroy", { transaction: row.id }),
                    {
                        onSuccess: () => {
                            quasar.notify({
                                color: "positive",
                                message: "Transaction successfully deleted",
                            });
                        },
                        onError() {
                            quasar.notify({
                                color: "negative",
                                message: "Something went wrong",
                            });
                        },
                    },
                );
            }
        },
    },
];

const props = defineProps({
    filter: { type: Object },
    categories: { type: Object },
    transactions: { type: Object },
});
const emit = defineEmits(["select-category", "edit-click"]);

const quasar = useQuasar();

const confirmationDialog = ref(null);
const transactions = ref(props.transactions[0]);
const nextPageUrl = ref(props.transactions.links.next);

const groupedTransactions = computed(() => {
    const result = {};
    for (const transaction of transactions.value) {
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

const loadMore = async () => {
    if (!nextPageUrl.value) {
        return;
    }

    const [, searchParams] = nextPageUrl.value.split("?");

    const response = await axios.get(
        route(
            "api.transactions.index",
            Object.fromEntries(new URLSearchParams(searchParams).entries()),
        ),
    );
    transactions.value = [...transactions.value, ...response.data.transactions];
    nextPageUrl.value = response.data.nextPageUrl;
};

watch(
    () => props.transactions,
    (value) => {
        transactions.value = value[0];
        nextPageUrl.value = value.links.next;
    },
);
</script>

<template>
    <Table
        :title="filter.transactionType === 'expense' ? 'Expenses' : 'Incomes'"
    >
        <div
            class="tw-flex tw-overflow-hidden tw-gap-0.5 tw-divide-x-2 tw-max-h-full tw-min-h-full"
        >
            <div
                v-infinite-scroll="loadMore"
                class="tw-overflow-y-auto tw-space-y-0.5 tw-pt-1 tw-basis-3/4"
                scroll-region
            >
                <template
                    v-for="(transactions, date) in groupedTransactions"
                    :key="date"
                >
                    <div class="tw-text-center tw-underline">
                        {{ format(date, "PP") }}
                    </div>

                    <div
                        v-for="transaction in transactions"
                        class="tw-flex tw-bg-pastel tw-p-3 tw-gap-4 tw-items-center transaction-row"
                    >
                        <div class="tw-basis-10">
                            <div
                                :style="{
                                    backgroundColor: transaction.category.color,
                                }"
                                class="tw-h-10 tw-w-10 tw-rounded-full"
                            ></div>
                        </div>

                        <div
                            class="tw-flex tw-flex-col tw-justify-between tw-gap-1 tw-basis-2/3"
                        >
                            <span>{{ transaction.category.title }}</span>
                            <span class="tw-text-xs tw-break-all">{{
                                transaction.description
                            }}</span>
                        </div>

                        <div
                            class="tw-flex tw-flex-col tw-justify-between tw-items-end tw-ml-auto tw-text-inherit tw-gap-1"
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
                        </div>

                        <div
                            class="tw-basis-10 tw-flex tw-justify-center tw-items-center tw-h-full"
                        >
                            <MenuButton
                                :items="rowButtons"
                                :target="transaction"
                                class="tw-hidden menu-button"
                            />
                        </div>
                    </div>
                </template>
            </div>

            <div
                class="tw-basis-1/4 tw-flex tw-flex-col justify-between tw-gap-2"
            >
                <div class="tw-overflow-y-scroll" scroll-region>
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

                <Link
                    :href="
                        route('settings', {
                            show_form: 1,
                            category_type: filter.transactionType,
                        })
                    "
                    as="button"
                    class="tw-bg-pastel tw-py-1.5 tw-flex tw-items-center tw-justify-center tw-rounded-br-md"
                >
                    <PlusIcon class="tw-w-6 tw-h-6" />
                </Link>
            </div>
        </div>

        <ConfirmationDialog ref="confirmationDialog" />
    </Table>
</template>

<style scoped>
.transaction-row:hover .menu-button {
    display: block;
}
</style>

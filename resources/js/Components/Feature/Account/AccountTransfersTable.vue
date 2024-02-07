<script setup>
import Table from "@/Components/UI/Table.vue";
import { computed, ref, watch } from "vue";
import { ArrowLongRightIcon } from "@heroicons/vue/24/outline/index.js";
import { guessFontColorByBackgroundColor } from "@/Helpers/color.js";
import { router } from "@inertiajs/vue3";
import MenuButton from "@/Components/UI/Buttons/MenuButton.vue";
import ConfirmationDialog from "@/Components/UI/Dialog/ConfirmationDialog.vue";
import { useQuasar } from "quasar";
import { format } from "date-fns";
import { formatCurrency } from "../../../Helpers/number.js";

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
                title: "Are you sure you want to delete this account transfer?",
                message: "This will delete this account transfer permanently.",
            });

            if (ok) {
                router.delete(
                    route("account-transfers.destroy", {
                        accountTransfer: row.id,
                    }),
                    {
                        onSuccess: () => {
                            quasar.notify({
                                color: "positive",
                                message:
                                    "Account transfer successfully deleted",
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
    transfers: {
        type: Array,
        required: true,
    },
    accounts: {
        type: Array,
        required: true,
    },
});
const emit = defineEmits(["edit-click"]);

const quasar = useQuasar();

const confirmationDialog = ref(null);
const filter = ref({
    account: 0,
});

const transfers = computed(() => {
    const result = {};
    for (const transfer of props.transfers) {
        if (!Array.isArray(result[transfer.date])) {
            result[transfer.date] = [];
        }

        result[transfer.date].push(transfer);
    }

    return result;
});

const accounts = computed(() => {
    return [{ title: "All", color: "#D70040", id: 0 }, ...props.accounts];
});

watch(
    filter,
    () => {
        const params = {};
        if (filter.value.account) {
            params.account_id = filter.value.account;
        }

        router.get(route("accounts.index"), params, {
            preserveState: true,
            replace: true,
        });
    },
    { deep: true },
);

const selectAccount = (id) => {
    filter.value.account = id;
};
</script>

<template>
    <Table title="History">
        <div
            class="tw-flex tw-overflow-hidden tw-gap-0.5 tw-divide-x-2 tw-min-h-full tw-max-h-full"
        >
            <q-list
                class="tw-overflow-y-auto tw-space-y-0.5 tw-pt-1 tw-basis-3/4"
            >
                <template v-for="(transfers, date) in transfers" :key="date">
                    <div class="tw-text-center tw-underline">
                        {{ format(date, "PP") }}
                    </div>

                    <div
                        v-for="transfer in transfers"
                        :key="transfer.id"
                        class="tw-flex tw-bg-pastel tw-p-3 tw-items-center"
                    >
                        <div
                            class="tw-flex tw-items-center tw-gap-2.5 tw-flex-1 tw-justify-start tw-basis-1/3"
                        >
                            <span
                                :style="{
                                    backgroundColor:
                                        transfer.account_from.color,
                                }"
                                class="tw-h-10 tw-w-10 tw-rounded-full"
                            ></span>

                            <div class="tw-flex tw-flex-col">
                                <span>{{ transfer.account_from.title }}</span>
                                <span>
                                    {{
                                        formatCurrency(
                                            transfer.amount,
                                            transfer.account_from.currency.code,
                                        )
                                    }}
                                </span>
                            </div>
                        </div>

                        <div class="tw-flex tw-items-center">
                            <ArrowLongRightIcon class="tw-h-10" />
                        </div>

                        <div
                            class="tw-flex tw-items-center tw-gap-2.5 tw-flex-1 tw-justify-start tw-ml-3 tw-basis-1/3"
                        >
                            <span
                                :style="{
                                    backgroundColor: transfer.account_to.color,
                                }"
                                class="tw-h-10 tw-w-10 tw-rounded-full"
                            ></span>

                            <div class="tw-flex tw-flex-col">
                                <span>{{ transfer.account_to.title }}</span>
                                <span>
                                    {{
                                        formatCurrency(
                                            transfer.converted_amount,
                                            transfer.account_to.currency.code,
                                        )
                                    }}
                                </span>
                            </div>
                        </div>

                        <div>
                            <MenuButton
                                :items="rowButtons"
                                :target="transfer"
                                :visible="true"
                            />
                        </div>
                    </div>
                </template>
            </q-list>

            <div
                class="tw-basis-1/4 tw-flex tw-flex-col tw-justify-between tw-gap-2"
            >
                <div class="tw-overflow-y-scroll">
                    <div
                        v-for="account in accounts"
                        :class="`tw-text-center tw-px-4 tw-cursor-pointer tw-text-md tw-font-medium ${
                            account.id === filter.account
                                ? 'tw-py-4.5'
                                : 'tw-py-2.5'
                        }`"
                        :style="{
                            backgroundColor:
                                account.id === filter.account
                                    ? '#B5D2D1'
                                    : account.color,
                            color: guessFontColorByBackgroundColor(
                                account.id === filter.account
                                    ? '#B5D2D1'
                                    : account.color,
                            ),
                        }"
                        @click="selectAccount(account.id)"
                    >
                        {{ account.title }}
                    </div>
                </div>

                <q-btn class="!tw-bg-pastel" icon="add" unelevated></q-btn>
            </div>
        </div>

        <ConfirmationDialog ref="confirmationDialog" />
    </Table>
</template>

<style scoped></style>

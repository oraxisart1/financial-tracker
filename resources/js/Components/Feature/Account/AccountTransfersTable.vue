<script setup>
import Table from "@/Components/UI/Table.vue";
import { computed, ref, watch } from "vue";
import { ArrowLongRightIcon } from "@heroicons/vue/24/outline/index.js";
import { guessFontColorByBackgroundColor } from "@/Helpers/color.js";
import { router, usePage } from "@inertiajs/vue3";
import MenuButton from "@/Components/UI/Buttons/MenuButton.vue";
import ConfirmationDialog from "@/Components/UI/Dialog/ConfirmationDialog.vue";
import { useQuasar } from "quasar";
import { format } from "date-fns";
import { formatCurrency } from "@/Helpers/number.js";
import axios from "axios";

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
                        account_transfer: row.id,
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
        type: Object,
        required: true,
    },
    accounts: {
        type: Array,
        required: true,
    },
});
const emit = defineEmits(["edit-click"]);

const quasar = useQuasar();
const page = usePage();

const confirmationDialog = ref(null);
const filter = ref({
    account: Number(page.props.query.account_id || 0),
});
const transfers = ref(props.transfers[0]);
const nextPageUrl = ref(props.transfers.links.next);

const groupedTransfers = computed(() => {
    const result = {};
    for (const transfer of transfers.value) {
        if (!Array.isArray(result[transfer.date])) {
            result[transfer.date] = [];
        }

        result[transfer.date].push(transfer);
    }

    return result;
});
const serializedFilter = computed(() => JSON.stringify(filter.value));

const accounts = computed(() => {
    return [{ title: "All", color: "#D70040", id: 0 }, ...props.accounts];
});

const selectAccount = (id) => {
    filter.value.account = id;
};

const loadMore = async () => {
    if (!nextPageUrl.value) {
        return;
    }

    const [, searchParams] = nextPageUrl.value.split("?");

    const response = await axios.get(
        route(
            "api.account-transfers.index",
            Object.fromEntries(new URLSearchParams(searchParams).entries()),
        ),
    );

    transfers.value = [...transfers.value, ...response.data.accountTransfers];
    nextPageUrl.value = response.data.nextPageUrl;
};

watch(
    filter,
    () => {
        const params = {};
        if (filter.value.account) {
            params.account_id = filter.value.account;
        }

        router.get(route("accounts.index"), params, {
            preserveState: true,
            preserveScroll: false,
        });
    },
    { deep: true },
);
watch(
    () => props.transfers,
    (value) => {
        transfers.value = value[0];
        nextPageUrl.value = value.links.next;
    },
);
</script>

<template>
    <Table :key="serializedFilter" title="History">
        <div
            class="tw-flex tw-overflow-hidden tw-gap-0.5 tw-divide-x-2 tw-min-h-full tw-max-h-full"
        >
            <q-list
                v-infinite-scroll="loadMore"
                class="tw-overflow-y-auto tw-space-y-0.5 tw-pt-1 tw-basis-3/4"
                scroll-region
            >
                <template
                    v-for="(transfers, date) in groupedTransfers"
                    :key="date"
                >
                    <div class="tw-text-center tw-underline">
                        {{ format(date, "PP") }}
                    </div>

                    <div
                        v-for="transfer in transfers"
                        :key="transfer.id"
                        class="tw-flex tw-bg-pastel tw-p-3 tw-items-center transfer-row"
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

                        <div
                            class="tw-basis-10 tw-flex tw-justify-center tw-items-center tw-h-full"
                        >
                            <MenuButton
                                :items="rowButtons"
                                :target="transfer"
                                class="tw-hidden menu-button"
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

<style scoped>
.transfer-row:hover .menu-button {
    display: block;
}
</style>

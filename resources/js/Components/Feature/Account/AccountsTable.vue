<script setup>
import Table from "@/Components/UI/Table.vue";
import MenuButton from "@/Components/UI/Buttons/MenuButton.vue";
import { router } from "@inertiajs/vue3";
import { useQuasar } from "quasar";
import { ref } from "vue";
import ConfirmationDialog from "@/Components/UI/Dialog/ConfirmationDialog.vue";
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
            const answer = await confirmationDialog.value.open({
                title: "Are you sure you want to delete this account?",
                message:
                    "This will delete this account permanently. Also you need to choose delete only account or delete it with all transactions and transfers",
            });

            if (answer !== false) {
                router.delete(
                    route("accounts.destroy", {
                        account: row.id,
                        mode: answer,
                    }),
                    {
                        onSuccess: () => {
                            quasar.notify({
                                color: "positive",
                                message: "Account successfully deleted",
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

const deleteDialogButtons = [
    { type: "confirm", label: "Delete all", value: "delete_all" },
    { type: "confirm", label: "Only account", value: "delete_account" },
    { type: "cancel", label: "Cancel", value: false },
];

defineProps({
    accounts: {
        type: Array,
        required: true,
    },
});
const emit = defineEmits(["edit-click"]);

const quasar = useQuasar();
const confirmationDialog = ref(null);
</script>

<template>
    <Table title="Accounts">
        <div class="tw-flex tw-flex-col tw-gap-0.5">
            <div
                v-for="account in accounts"
                class="tw-flex tw-bg-pastel tw-p-3 tw-items-center tw-gap-10 account-row"
            >
                <div class="tw-basis-2/3 tw-flex tw-items-center tw-gap-x-4">
                    <span
                        :style="{
                            backgroundColor: account.color,
                        }"
                        class="tw-h-10 tw-w-10 tw-rounded-full"
                    ></span>
                    <span>{{ account.title }}</span>
                </div>

                <div
                    class="tw-basis-1/3 tw-flex tw-justify-center tw-items-center tw-gap-20"
                >
                    <span
                        class="tw-text-md tw-text-center tw-flex-1 tw-basis-2/3"
                        >{{ formatCurrency(account.balance) }}</span
                    >
                    <span class="tw-flex-1 tw-text-left">{{
                        account.currency.code
                    }}</span>
                </div>

                <div
                    class="tw-basis-10 tw-flex tw-justify-center tw-items-center tw-h-full"
                >
                    <MenuButton
                        :items="rowButtons"
                        :target="account"
                        class="tw-hidden menu-button"
                    />
                </div>
            </div>
        </div>

        <ConfirmationDialog
            ref="confirmationDialog"
            :buttons="deleteDialogButtons"
            :submit-on-enter-press="false"
        />
    </Table>
</template>

<style scoped>
.account-row:hover .menu-button {
    display: block;
}
</style>

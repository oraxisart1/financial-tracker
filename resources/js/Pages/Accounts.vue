<script setup>
import { Head } from "@inertiajs/vue3";
import PageTitle from "@/Components/UI/PageTitle.vue";
import PrimaryButton from "@/Components/UI/Buttons/PrimaryButton.vue";
import { ref } from "vue";
import AccountsTable from "@/Components/Feature/Account/AccountsTable.vue";
import AccountForm from "@/Components/Feature/Account/AccountForm.vue";
import AccountTransferForm from "@/Components/Feature/Account/AccountTransferForm.vue";
import AccountTransfersTable from "@/Components/Feature/Account/AccountTransfersTable.vue";

const tabs = [
    { label: "Accounts", name: "accounts" },
    { label: "Add", name: "add_account" },
    { label: "Transfer between accounts", name: "transfer" },
    { label: "History", name: "history" },
];

const activeTab = ref("history");
const accountForm = ref(null);
const showForm = ref(false);
const accountTransferForm = ref(null);
const showTransferForm = ref(false);

const selectTab = (tabName) => {
    showForm.value = false;
    accountForm.value.clear();

    showTransferForm.value = false;
    accountTransferForm.value.clear();

    activeTab.value = tabName;
};

const onAccountEdit = (account) => {
    accountForm.value.setModel(account);
    showForm.value = true;
};

const closeForm = () => {
    selectTab("accounts");
};

const onAccountTransferEdit = (transfer) => {
    accountTransferForm.value.setModel(transfer);
    showTransferForm.value = true;
};
</script>

<template>
    <Head title="Accounts" />

    <PageTitle>Accounts</PageTitle>

    <div
        class="tw-flex tw-max-h-[89%] tw-min-h-[89%] tw-mt-4 tw-p-4 tw-gap-x-8"
    >
        <div
            class="tw-flex-grow tw-flex tw-flex-col tw-justify-center tw-items-center tw-basis-1/2"
        >
            <div class="tw-flex tw-flex-col tw-justify-center tw-gap-7.5">
                <PrimaryButton
                    v-for="tab in tabs"
                    :key="tab.name"
                    :active="activeTab === tab.name"
                    @click="selectTab(tab.name)"
                >
                    {{ tab.label }}
                </PrimaryButton>
            </div>
        </div>

        <div class="tw-flex-grow tw-basis-1/2">
            <AccountsTable
                v-show="activeTab === 'accounts' && !showForm"
                :accounts="$page.props.accounts"
                @edit-click="onAccountEdit"
            />

            <AccountForm
                v-show="activeTab === 'add_account' || showForm"
                ref="accountForm"
                :currencies="$page.props.currencies"
                @cancel="closeForm"
                @save="closeForm"
            />

            <AccountTransferForm
                v-show="activeTab === 'transfer' || showTransferForm"
                ref="accountTransferForm"
                :accounts="$page.props.accounts"
                @cancel="selectTab('history')"
                @save="selectTab('history')"
            />

            <AccountTransfersTable
                v-show="activeTab === 'history' && !showTransferForm"
                :accounts="$page.props.accounts"
                :transfers="$page.props.transfers"
                @edit-click="onAccountTransferEdit"
            />
        </div>
    </div>
</template>

<style scoped></style>

<script setup>
import { Head } from "@inertiajs/vue3";
import PageTitle from "@/Components/UI/PageTitle.vue";
import PrimaryButton from "@/Components/UI/Buttons/PrimaryButton.vue";
import { ref } from "vue";
import AccountsTable from "@/Components/Feature/Account/AccountsTable.vue";

const tabs = [
    { label: "Accounts", name: "accounts" },
    { label: "Add", name: "add_account" },
    { label: "Transfer between accounts", name: "transfer" },
    { label: "History", name: "history" },
];

const activeTab = ref("accounts");

const selectTab = (tabName) => {
    if (tabName === activeTab.value) {
        return;
    }

    activeTab.value = tabName;
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
                v-show="activeTab === 'accounts'"
                :accounts="$page.props.accounts"
            />
        </div>
    </div>
</template>

<style scoped></style>

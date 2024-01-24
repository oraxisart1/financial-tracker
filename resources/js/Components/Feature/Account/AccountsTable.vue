<script setup>
import Table from "@/Components/UI/Table.vue";
import { formatCurrency } from "../../../Helpers/number.js";

defineProps({
    accounts: {
        type: Array,
        required: true,
    },
});
</script>

<template>
    <Table title="Accounts">
        <div class="tw-flex tw-flex-col tw-gap-0.5">
            <div
                v-for="account in accounts"
                class="tw-flex tw-bg-pastel tw-p-3"
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
                    <span class="tw-text-md tw-text-center tw-flex-1">{{
                        formatCurrency(account.balance)
                    }}</span>
                    <span class="tw-flex-1">{{ account.currency.code }}</span>
                </div>
            </div>
        </div>
    </Table>
</template>

<style scoped></style>

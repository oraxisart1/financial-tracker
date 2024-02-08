<script setup>
import { computed, nextTick, ref, watch } from "vue";
import { router, usePage } from "@inertiajs/vue3";
import {
    CheckIcon,
    ChevronDownIcon,
    ChevronUpIcon,
} from "@heroicons/vue/16/solid/index.js";
import { useQuasar } from "quasar";
import { formatCurrency } from "@/Helpers/number.js";

const page = usePage();
const quasar = useQuasar();

const container = ref();
const menu = ref();
const show = ref(false);

const accounts = computed(() => page.props.accounts);

const amount = computed(() =>
    accounts.value
        .filter((acc) => acc.active)
        .reduce((acc, account) => acc + Number(account.balance), 0),
);

const toggle = () => {
    show.value ? close() : open();
};

const open = () => {
    show.value = true;
};

const close = () => {
    show.value = false;
};

const onMenuEnter = () => {
    alignMenu();
};

const alignMenu = () => {
    if (show.value) {
        menu.value.style.minWidth = `${container.value.offsetWidth}px`;

        const containerRect = container.value.getBoundingClientRect();
        menu.value.style.top = `${containerRect.y + containerRect.height}px`;
        menu.value.style.left = `${containerRect.x}px`;
    }
};

const toggleAccount = (account) => {
    router.post(
        route("accounts.toggle", { account: account.id }),
        {},
        {
            onSuccess: () => {
                quasar.notify({
                    color: "positive",
                    message: "Account successfully updated",
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
};

const onClickOutsideParams = [close, { targets: [container, menu] }];

watch(
    accounts,
    () => {
        nextTick(alignMenu);
    },
    { deep: true },
);
</script>

<template>
    <div ref="container" class="tw-relative">
        <div
            ref="trigger"
            class="tw-flex tw-items-center tw-gap-1 tw-bg-light-pastel tw-px-2 tw-py-1 tw-rounded-t-[6px] tw-cursor-pointer"
            @click="toggle"
        >
            <div class="tw-text-3xl">
                {{ formatCurrency(amount) }}
            </div>

            <ChevronUpIcon v-show="show" class="tw-w-6 tw-h-6" />
            <ChevronDownIcon v-show="!show" class="tw-w-6 tw-h-6" />
        </div>

        <Teleport to="body">
            <Transition @enter="onMenuEnter">
                <div
                    v-if="show"
                    ref="menu"
                    v-click-outside="onClickOutsideParams"
                    v-key-press="{ escape: close }"
                    class="tw-absolute tw-max-h-48 tw-bg-light-pastel tw-overflow-auto tw-flex tw-flex-col tw-gap-0.5"
                >
                    <div
                        v-for="account in accounts"
                        :class="`tw-flex tw-justify-between tw-items-center tw-p-2.5 menu-item cursor-pointer ${
                            account.active
                                ? 'tw-bg-light-pastel'
                                : 'tw-bg-white'
                        }`"
                        @click="toggleAccount(account)"
                    >
                        <div class="tw-flex tw-flex-col">
                            <span>{{ account.title }}</span>
                            <span>{{
                                formatCurrency(
                                    account.balance,
                                    account.currency.code,
                                )
                            }}</span>
                        </div>

                        <CheckIcon
                            :class="`tw-h-6 tw-w-6 menu-item__icon ${
                                account.active ? '' : 'tw-hidden'
                            }`"
                        />
                    </div>
                </div>
            </Transition>
        </Teleport>
    </div>
</template>

<style scoped>
.menu-item:hover .menu-item__icon {
    display: block;
}
</style>

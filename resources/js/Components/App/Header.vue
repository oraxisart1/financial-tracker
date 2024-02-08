flex-1
<script setup>
import Navigation from "@/Components/App/Navigation.vue";
import ApplicationLogo from "@/Components/ApplicationLogo.vue";
import { Cog8ToothIcon } from "@heroicons/vue/24/outline/index.js";
import NavLink from "@/Components/NavLink.vue";
import AccountsDropdown from "@/Components/App/AccountsDropdown.vue";
</script>

<template>
    <div
        class="tw-flex tw-py-2 tw-px-4 tw-justify-between tw-items-end tw-border-b tw-border-stone-100"
    >
        <div class="tw-flex">
            <ApplicationLogo />
            <Navigation />
        </div>

        <AccountsDropdown />

        <div class="tw-flex tw-gap-[10px] tw-items-center">
            <NavLink :href="route('logout')" as="button" method="post"
                >Log out
            </NavLink>
            <Cog8ToothIcon class="tw-w-10 tw-h-10" />
        </div>
    </div>
</template>

<style scoped></style>

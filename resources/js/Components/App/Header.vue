flex-1
<script setup>
import Navigation from "@/Components/App/Navigation.vue";
import ApplicationLogo from "@/Components/App/ApplicationLogo.vue";
import NavLink from "@/Components/NavLink.vue";
import AccountsDropdown from "@/Components/App/AccountsDropdown.vue";
</script>

<template>
    <div
        class="tw-flex tw-py-2 tw-px-4 tw-justify-between tw-items-end tw-border-b tw-border-stone-100"
    >
        <div class="tw-flex tw-gap-8">
            <ApplicationLogo />
            <Navigation />
        </div>

        <AccountsDropdown />

        <NavLink :href="route('logout')" as="button" method="post"
            >Log out
        </NavLink>
    </div>
</template>

<style scoped></style>

<script setup>
import NavLink from "@/Components/NavLink.vue";
</script>

<template>
    <div class="tw-flex tw-gap-14 tw-items-end tw-ml-4">
        <NavLink
            :active="$page.props.routeName === 'dashboard'"
            :href="route('dashboard')"
            >Transactions
        </NavLink>
        <NavLink
            :active="$page.props.routeName === 'accounts.index'"
            :href="route('accounts.index')"
            >Accounts
        </NavLink>
        <NavLink :active="$page.props.routeName === 'charts.index'" href="#"
            >Charts
        </NavLink>
        <NavLink :active="$page.props.routeName === 'budgets.index'" href="#"
            >Budget
        </NavLink>
    </div>
</template>

<style scoped></style>

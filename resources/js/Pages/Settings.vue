<script setup>
import PageTitle from "@/Components/UI/PageTitle.vue";
import Tabs from "@/Components/UI/Tabs.vue";
import { ref, watch } from "vue";
import Form from "@/Components/UI/Form/Form.vue";
import { router, usePage } from "@inertiajs/vue3";
import MenuButton from "@/Components/UI/Buttons/MenuButton.vue";
import ConfirmationDialog from "@/Components/UI/Dialog/ConfirmationDialog.vue";
import { useQuasar } from "quasar";
import { PlusIcon } from "@heroicons/vue/24/outline/index.js";
import CategoryForm from "@/Components/Feature/Category/CategoryForm.vue";
import ProfileForm from "@/Components/Feature/User/ProfileForm.vue";
import UserSettingsForm from "@/Components/Feature/User/UserSettingsForm.vue";

const tabs = [
    {
        label: "Expense categories",
        name: "expense",
        classes: "tw-rounded-tl-md",
    },
    {
        label: "Income categories",
        name: "income",
        classes: "tw-rounded-tr-md",
    },
];

const categoryButtons = [
    {
        label: "Edit",
        icon: "PencilIcon",
        onClick: (row) => {
            categoryForm.value.setModel(row);
            showForm.value = true;
        },
    },
    {
        label: "Delete",
        icon: "TrashIcon",
        onClick: async (row) => {
            const ok = await confirmationDialog.value.open({
                title: "Are you sure you want to delete this category?",
                message: "This will delete this category permanently.",
            });

            if (ok) {
                router.delete(
                    route("categories.destroy", { category: row.id }),
                    {
                        onSuccess: () => {
                            quasar.notify({
                                color: "positive",
                                message: "Transaction successfully deleted",
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

const page = usePage();
const quasar = useQuasar();

const confirmationDialog = ref();
const categoryForm = ref();
const tab = ref(page.props.query.category_type || "expense");
const showForm = ref(Boolean(page.props.query.show_form || ""));

watch(tab, (value) => {
    router.get(
        route("settings", { category_type: value }),
        {},
        { preserveState: true },
    );
});

const openForm = () => {
    categoryForm.value.clear();
    showForm.value = true;
};
</script>

<template>
    <PageTitle>Settings</PageTitle>

    <div class="tw-grid tw-grid-cols-4 tw-gap-7.5 tw-p-7.5">
        <ProfileForm class="tw-col-span-2" />
        <UserSettingsForm class="tw-col-span-2" />

        <CategoryForm
            v-show="showForm"
            ref="categoryForm"
            :type="tab"
            class="tw-col-span-full tw-col-start-2 tw-col-end-4"
            @cancel="showForm = false"
            @save="showForm = false"
        />

        <div class="tw-col-span-full tw-bg-light-pastel tw-rounded-b-md">
            <Tabs v-model="tab" :tabs="tabs" />
            <div class="tw-grid tw-grid-cols-12 tw-px-2 tw-py-4 tw-gap-4">
                <div
                    v-for="category in $page.props.categories"
                    class="tw-p-2 tw-flex tw-flex-col tw-h-40 tw-bg-pastel tw-rounded-md tw-border tw-border-light tw-items-center tw-gap-2"
                >
                    <div
                        :style="{ backgroundColor: category.color }"
                        class="tw-basis-2/3 tw-border tw-border-light tw-rounded-md tw-w-full"
                    ></div>

                    <div class="tw-flex tw-items-center tw-gap-2">
                        <span>{{ category.title }}</span>
                        <MenuButton
                            :items="categoryButtons"
                            :target="category"
                        />
                    </div>
                </div>

                <div
                    class="tw-p-2 tw-flex tw-flex-col tw-h-40 tw-bg-pastel tw-rounded-md tw-border tw-border-light tw-items-center tw-gap-2 tw-justify-center tw-cursor-pointer"
                    @click="openForm"
                >
                    <PlusIcon class="tw-h-20 tw-w-20" />
                </div>
            </div>
        </div>

        <ConfirmationDialog ref="confirmationDialog" />
    </div>
</template>

<style scoped></style>

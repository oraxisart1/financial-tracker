import { useForm as useInertiaForm } from "@inertiajs/vue3";
import { watch } from "vue";

export default function useForm(data) {
    const form = useInertiaForm(data);

    watch(
        () => form.data(),
        (newValue, oldValue) => {
            const changedField = Object.keys(newValue).find(
                (key) => newValue[key] !== oldValue[key],
            );

            if (changedField in form.errors) {
                form.clearErrors(changedField);
            }
        },
    );

    return form;
}

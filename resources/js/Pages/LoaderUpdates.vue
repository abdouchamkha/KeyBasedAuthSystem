<script setup>
    import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
    import {
        Button
    } from '@/components/ui/button'

    import {
        Head
    } from '@inertiajs/vue3';
</script>

<template>

    <Head title="Loader updates" />

    <AuthenticatedLayout>
        <template #header>
            <h2
                class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200"
            >
                Loader
            </h2>
        </template>

        <div class="flex items-center justify-center flex-1 border border-dashed rounded-lg shadow-sm">
            <div class="flex flex-col items-center gap-1 text-center">
                <h3 class="text-2xl font-bold tracking-tight">
                    You have no products
                </h3>
                <p class="text-sm text-muted-foreground">
                    You can start selling as soon as you add a product.
                </p>
                <Button class="mt-4">
                    Add Product
                </Button>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

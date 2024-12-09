<script setup>
import LoadersTable from '@/Components/Dashboard/DataTable/Files/LoadersTable.vue';
import SelectLang from '@/Components/Dashboard/SelectLang.vue';
import InputError from '@/Components/InputError.vue';
import Modal from '@/Components/Modal.vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Button } from '@/components/ui/button';
import Input from '@/components/ui/input/Input.vue';
import Label from '@/components/ui/label/Label.vue';
import { Switch } from '@/components/ui/switch';
import { Head, usePage, useForm } from '@inertiajs/vue3';
import { ref, nextTick } from 'vue';

import { Progress } from '@/components/ui/progress';

// import { CalendarIcon } from '@radix-icons/vue'
// import { Calendar } from '@/components/ui/calendar'
// import { Popover, PopoverContent, PopoverTrigger } from '@/components/ui/popover'
// import { cn } from '@/lib/utils'
const { props } = usePage();
const loaders = {files:props.files,filters:props.filters};

const createNewLoader = ref(false);
const createProductModel = ref(false);

const form = useForm({
    product: null,
    file: null,
    // unsupported_at: null,
});
const createProductForm = useForm({
    name: '',
    status: false,
    product_status: '',
});
const openCreateLoader = () => {
    createNewLoader.value = true;
    // nextTick(() => versionInput.value.focus());
};
const openProductModel = () => {
    createProductModel.value = true;
    // nextTick(() => versionInput.value.focus());
};
const createProduct = () => {
    createProductForm.post(route('files.create.products'), {
        preserveScroll: true,
        onSuccess: () => closeModal(),
        onError: (errors) => {
            console.error("Create errors:", errors);
        }
    });
};
const storeLoader = () => {
    form.post(route('files.store'), {
        preserveScroll: true,
        forceFormData: true,
        onSuccess: () => closeModal(),
        onError: (errors) => {
            console.error("Upload errors:", errors);
        }
    });
};

const closeModal = () => {
    createProductModel.value = false;
    createNewLoader.value = false;

    form.clearErrors();
    form.reset();
};

const loaderType = props.products.map(product => ({
    value: product.id.toString(), // Use `id` or any unique identifier as the value
    label: product.name,          // Use `name` as the label
}));
</script>

<template>
    <div>

        <Head title="Loader updates" />
        <AuthenticatedLayout>
            <template #header>
                <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">
                    Files
                </h2>
            </template>
            <div class="h-full p-3 border border-dashed rounded-lg shadow-sm">
                <div class="flex items-center justify-between">
                    <h3>Loaders</h3>
                    <Button @click="openProductModel">Create product</Button>
                    <Modal :show="createProductModel" @close="closeModal">
                        <div class="p-6">
                            <h2 class="text-lg font-medium ">
                                Create new product
                            </h2>
                            <div class="mt-6">
                                <Label for="name">Name:</Label>
                                <Input id="name" v-model="createProductForm.name" type="text" autocomplete="off"
                                    placeholder="Product name" class="block w-3/4 mt-1" @keyup.enter="createProduct" />
                                <InputError :message="createProductForm.errors.name" class="mt-2" />
                            </div>
                            <div class="mt-6">
                                <div class="flex items-center space-x-2">
                                    <Switch @keyup.enter="createProduct"  :checked="createProductForm.status"
                                    @update:checked="()=>{
                                        createProductForm.status =!createProductForm.status
                                    }" id="status" />
                                    <Label for="status">{{ createProductForm.status ? '' : 'Not' }} Active</Label>
                                </div>
                                <InputError :message="form.errors.tags" class="mt-2" />
                            </div>
                            <div class="mt-6">
                                <Label for="product_status">Product Status <span
                                        class="underline">optional</span>:</Label>
                                <Input id="product_status" v-model="createProductForm.product_status" type="text"
                                    autocomplete="off" placeholder="Product status" class="block w-3/4 mt-1"
                                    @keyup.enter="createProduct" />
                                <InputError :message="createProductForm.errors.product_status" class="mt-2" />
                            </div>
                            <div class="flex justify-end mt-6">
                                <Button @click="closeModal">Cancel</Button>
                                <Button class="ms-3" :class="{ 'opacity-25': form.processing }"
                                    :disabled="form.processing" @click="createProduct">
                                    Create product
                                </Button>
                            </div>
                        </div>
                    </Modal>
                    <Button @click="openCreateLoader">Create</Button>
                    <Modal :show="createNewLoader" @close="closeModal">
                        <div class="p-6">
                            <h2 class="text-lg font-medium ">
                                Upload new file
                            </h2>
                            <div class="mt-6">
                                <Label for="file">Upload file:</Label>
                                <Input id="file" type="file" @keyup.enter="storeLoader"
                                    @input="form.file = $event.target.files[0]" placeholder="UploadFile" />

                                <Progress v-if="form.progress" :value="form.progress.percentage" max="100"
                                    class="w-3/5 mt-2 bg-green-500" />
                                <InputError :message="form.errors.file" class="mt-2" />
                            </div>
                            <div class="mt-6">
                                <Label for="tags">Product:</Label>
                                <SelectLang id="tags" @keyup.enter="storeLoader" v-model="form.product"
                                    :options="loaderType" selectLabel="Select product" />
                                <InputError :message="form.errors.product" class="mt-2" />
                            </div>
                            <div class="flex justify-end mt-6">
                                <Button @click="closeModal">Cancel</Button>
                                <Button class="ms-3" :class="{ 'opacity-25': form.processing }"
                                    :disabled="form.processing" @click="storeLoader">
                                    Create
                                </Button>
                            </div>
                        </div>
                    </Modal>
                </div>
                <LoadersTable :loaders="loaders" />
            </div>
        </AuthenticatedLayout>
    </div>
</template>

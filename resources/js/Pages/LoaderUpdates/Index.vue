<script setup>
import LoadersTable from '@/Components/Dashboard/DataTable/LoadersTable/LoadersTable.vue';
import SelectLang from '@/Components/Dashboard/SelectLang.vue';
import InputError from '@/Components/InputError.vue';
import Modal from '@/Components/Modal.vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Button } from '@/components/ui/button';
import Input from '@/components/ui/input/Input.vue';
import Label from '@/components/ui/label/Label.vue';
import { Switch } from '@/components/ui/switch';
import { Textarea } from '@/components/ui/textarea';
import { Head, usePage, useForm } from '@inertiajs/vue3';
import { ref, nextTick } from 'vue';
import {
  DateFormatter,
  getLocalTimeZone,
} from '@internationalized/date'
import { Progress } from '@/components/ui/progress';

// import { CalendarIcon } from '@radix-icons/vue'
// import { Calendar } from '@/components/ui/calendar'
// import { Popover, PopoverContent, PopoverTrigger } from '@/components/ui/popover'
// import { cn } from '@/lib/utils'

const df = new DateFormatter('en-US', {
  dateStyle: 'long',
})

const { props } = usePage();
const loaders = props.loaders;

const versionInput = ref('');
const createNewLoader = ref(false);

const form = useForm({
    is_auto_version: false,
    version: '',
    lang: '',
    updateNote: { title: '', description: '' },
    loader_type: '',
    stage: '',
    file:null,
    // unsupported_at: null,
});

const openCreateLoader = () => {
    createNewLoader.value = true;
    // nextTick(() => versionInput.value.focus());
};

const storeLoader = () => {
    form.post(route('loader-updates.store'), {
        preserveScroll: true,
        forceFormData: true,  // Force FormData for file handling
        onSuccess: () => closeModal(),
        onError: (errors) => {
            console.error("Upload errors:", errors);
        }
    });
};

const closeModal = () => {
    createNewLoader.value = false;

    form.clearErrors();
    form.reset();
};

const languages = [
    { value: 'cpp', label: 'C++' },
    { value: 'js', label: 'JavaScript & TypeScript' },
];
const loaderType = [
    { value: 'no_ui', label: 'No UI loader' },
    { value: 'ui', label: 'Ui loader' },
];
const Stages = [
    { value: 'production', label: 'production' },
    { value: 'staging', label: 'staging' },
    { value: 'development', label: 'development' },
];
</script>

<template>
    <div>
        <Head title="Loader updates" />
        <AuthenticatedLayout>
            <template #header>
                <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">
                    Loader
                </h2>
            </template>
            <div class="h-full p-3 border border-dashed rounded-lg shadow-sm">
                <div class="flex items-center justify-between">
                    <h3>Loaders</h3>
                    <Button @click="openCreateLoader">Create</Button>
                    <Modal :show="createNewLoader" @close="closeModal">
                        <div class="p-6">
                            <h2 class="text-lg font-medium ">
                                Creating a new loader version
                            </h2>
                            <div class="mt-1 text-sm">
                                <p class="font-semibold">Notice:</p>
                                <br>
                                <p class="mt-1 mb-2 text-sm underline underline-offset-4">About stage:</p>
                                <ul class="ml-8 list-decimal">
                                    <li>Production: means the loader will be used in production and is online.</li>
                                    <li>Staging: means the loader is in testing mode or in beta version.</li>
                                    <li>Development: means the loader is under development and can be accessed by the
                                        development team.</li>
                                </ul>
                                <p class="mt-1 mb-2 text-sm underline underline-offset-4">About unsupported at:</p>
                                <ul class="ml-8 list-decimal">
                                    <li>This is a duration for the customers to change their configuration to use the newest
                                        update.</li>
                                    <li>It can be null.</li>
                                </ul>
                            </div>
                            <div class="flex items-center mt-6">
                                <div>
                                    <Label for="version">Version:</Label>
                                    <Input :disabled="form.is_auto_version" id="version" ref="versionInput" v-model="form.version" type="number"
                                        autocomplete="off" min="0.01" placeholder="0.01" class="block w-3/4 mt-1"
                                        @keyup.enter="storeLoader" />
                                    <InputError :message="form.errors.version" class="mt-2" />
                                </div>
                                <div class="flex items-center mt-5 space-x-2">
                                    <Switch id="auto_version" @click="form.is_auto_version = !form.is_auto_version" />
                                    <Label for="auto_version">Auto versioning</Label>
                                </div>
                            </div>
                            <div class="mt-6">
                                <Label for="file">Upload file:</Label>
                                <Input id="file" type="file" @keyup.enter="storeLoader"  @input="form.file = $event.target.files[0]" placeholder="UploadFile"  />

                                <Progress v-if="form.progress" :value="form.progress.percentage" max="100" class="w-3/5 mt-2 bg-green-500" />
                                <InputError :message="form.errors.file" class="mt-2" />
                            </div>
                            <div class="mt-6">
                                <Label for="stage">Stage:</Label>
                                <SelectLang id="stage" @keyup.enter="storeLoader" v-model="form.stage" :options="Stages" selectLabel="Select a stage" />
                                <InputError :message="form.errors.stage" class="mt-2" />
                            </div>
                            <div class="mt-6">
                                <Label for="lang">Language:</Label>
                                <SelectLang id="lang" @keyup.enter="storeLoader" v-model="form.lang" :options="languages" selectLabel="Programming Language" />
                                <InputError :message="form.errors.lang" class="mt-2" />
                            </div>
                            <div class="mt-6">
                                <Label for="loader_type">Loader type:</Label>
                                <SelectLang id="loader_type" @keyup.enter="storeLoader" v-model="form.loader_type" :options="loaderType" selectLabel="Loader type" />
                                <InputError :message="form.errors.loader_type" class="mt-2" />
                            </div>
                            <div class="mt-6">
                                <div class="ml-5">
                                    <p class="mb-3 font-semibold">Update Note: <span class="text-sm font-normal">(Optional)</span></p>
                                    <div class="mt-3">
                                        <Label for="title">Title:</Label>
                                        <Input id="title" v-model="form.updateNote.title" type="text"
                                            autocomplete="off" placeholder="Update title" class="block w-3/4 mt-1"
                                            @keyup.enter="storeLoader" />
                                        <InputError :message="form.errors.form?.updateNote?.title" class="mt-2" />
                                    </div>
                                    <div class="mt-3">
                                        <Label for="description">Description:</Label>
                                        <Textarea id="description" autocomplete="off" v-model="form.updateNote.description" placeholder="Type your description here." class="block w-3/4 mt-1"
                                            @keyup.enter="storeLoader" />
                                        <InputError :message="form.errors.form?.updateNote?.description" class="mt-2" />
                                    </div>
                                </div>
                            </div>
                            <!-- <div class="mt-6 space-y-2">
                                <Label for="unsupported_at" class="">Unsupported at:</Label> <br>
                                <Popover >
                                    <PopoverTrigger as-child>
                                    <Button
                                        variant="outline"
                                        :class="cn(
                                        'w-[280px] justify-start text-left font-normal',
                                        !value && 'text-muted-foreground',
                                        )"
                                    >
                                        <CalendarIcon class="w-4 h-4 mr-2" />
                                        {{ value ? df.format(value.toDate(getLocalTimeZone())) : "Pick a date" }}
                                    </Button>
                                    </PopoverTrigger>
                                    <PopoverContent class="w-auto p-0">
                                    <Calendar class="mt-2" v-model="form.unsupported_at" id="unsupported_at" initial-focus />
                                    </PopoverContent>
                                </Popover>
                                <InputError :message="form.errors.unsupported_at" class="mt-2" />
                            </div> -->
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

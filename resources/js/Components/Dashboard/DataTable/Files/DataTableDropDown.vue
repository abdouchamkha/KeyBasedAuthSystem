<script setup>
import {
    MoreHorizontal
} from 'lucide-vue-next';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuLabel,
    DropdownMenuSeparator,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import {
    Button
} from '@/components/ui/button';
import SelectLang from '@/Components/Dashboard/SelectLang.vue';
import InputError from '@/Components/InputError.vue';
import Modal from '@/Components/Modal.vue';
import Input from '@/components/ui/input/Input.vue';
import Label from '@/components/ui/label/Label.vue';
import { Textarea } from '@/components/ui/textarea';
import { useForm } from '@inertiajs/vue3';
import { ref } from 'vue';
import { DateFormatter, getLocalTimeZone } from '@internationalized/date';
import { Progress } from '@/components/ui/progress';
import { CalendarIcon } from '@radix-icons/vue';
import { Calendar } from '@/components/ui/calendar';
import { Popover, PopoverContent, PopoverTrigger } from '@/components/ui/popover';
import { cn } from '@/lib/utils';

const props = defineProps({
    loader: Object,
});

function copy(id) {
    navigator.clipboard.writeText(id);
}

const editLoader = ref(false);
const uploadErrors = ref(null);

const form = useForm({
    file: null,
    product: props.loader.id,
});

const openEditLoader = () => {
    editLoader.value = true;
};

const updateLoader = () => {
    form.put(route('files.update', { id: props.loader.id }), {
        preserveScroll: true,
        forceFormData: true, // Ensures the file is sent as FormData
        onSuccess: () => {
            closeModal();
            console.log('File updated successfully');
        },
        onError: (errors) => {
            uploadErrors.value = 'Update errors:', errors;
            console.error('Update errors:', errors);
        },
    });
};
const closeModal = () => {
    editLoader.value = false;
    form.clearErrors();
    form.reset();
};
</script>

<template>
    <DropdownMenu>
        <DropdownMenuTrigger as-child>
            <Button variant="ghost" class="w-8 h-8 p-0">
                <span class="sr-only">Open menu</span>
                <MoreHorizontal class="w-4 h-4" />
            </Button>
        </DropdownMenuTrigger>
        <DropdownMenuContent align="end">
            <DropdownMenuLabel>Actions</DropdownMenuLabel>
            <DropdownMenuItem @click="copy(props.loader.id)">
                Copy Loader ID
            </DropdownMenuItem>
            <DropdownMenuSeparator />
            <DropdownMenuItem @click="openEditLoader">Update file</DropdownMenuItem>
        </DropdownMenuContent>
        <Modal :show="editLoader" @close="closeModal">
            <div class="p-6">
                <h2 class="text-lg font-medium">Update file</h2>
                <div class="mt-6">
                    <Label for="file">Upload file:</Label>
                    <Input
    id="file"
    type="file"
    @change="form.file = $event.target.files[0]"
    placeholder="Upload a new file"
/>

                    <Progress
                        v-if="form.progress"
                        :value="form.progress.percentage"
                        max="100"
                        class="w-3/5 mt-2"
                    />
                    <InputError :message="form.errors.file" class="mt-2" />
                </div>
                <div class="flex justify-end mt-6">
                    <Button @click="closeModal">Cancel</Button>
                    <Button
                        class="ms-3"
                        :class="{ 'opacity-25': form.processing }"
                        :disabled="form.processing"
                        @click="updateLoader"
                    >
                        Upload
                    </Button>
                </div>
            </div>
        </Modal>
    </DropdownMenu>
</template>

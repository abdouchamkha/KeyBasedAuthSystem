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

const versionInput = ref('');
const editLoader = ref(false);

const form = useForm({
    updateNote: { title: '', description: '' },
    stage: '',
    // file: null,
    unsupported_at: null,
});

const openEditLoader = () => {
    editLoader.value = true;
};

const updateLoader = () => {
    form.put(route('loader-updates.update', { id: props.loader.id }), {
        preserveScroll: true,
        onSuccess: () => closeModal(),
    });
};

const closeModal = () => {
    editLoader.value = false;
    form.clearErrors();
    form.reset();
};

const Stages = [
    { value: 'production', label: 'production' },
    { value: 'staging', label: 'staging' },
    { value: 'development', label: 'development' },
];

const df = new DateFormatter('en-US', {
    dateStyle: 'long',
});

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
            <DropdownMenuItem @click="openEditLoader">Edit Loader</DropdownMenuItem>
        </DropdownMenuContent>
        <Modal :show="editLoader" @close="closeModal">
            <div class="p-6">
                <h2 class="text-lg font-medium ">
                    Edit loader
                </h2>
                <!-- <div class="mt-6">
                                <Label for="file">Upload file:</Label>
                                <Input id="file" type="file" @keyup.enter="updateLoader"  @input="form.file = $event.target.files[0]"  />
                                <Progress v-if="form.progress" :value="form.progress.percentage" class="w-3/5 mt-2 bg-green-500" />
                                <InputError :message="form.errors.file" class="mt-2" />
                            </div> -->
                <div class="mt-6">
                    <Label for="stage">Stage:</Label>
                    <SelectLang id="stage" @keyup.enter="updateLoader" v-model="form.stage" :options="Stages"
                        selectLabel="Select a stage" />
                    <InputError :message="form.errors.stage" class="mt-2" />
                </div>
                <div class="mt-6">
                    <div class="ml-5">
                        <p class="mb-3 font-semibold">Update Note: <span class="text-sm font-normal">(Optional)</span>
                        </p>
                        <div class="mt-3">
                            <Label for="title">Title:</Label>
                            <Input id="title" v-model="form.updateNote.title" type="text" autocomplete="off"
                                placeholder="Update title" class="block w-3/4 mt-1" @keyup.enter="updateLoader" />
                            <InputError :message="form.errors.updateNote?.title" class="mt-2" />
                        </div>
                        <div class="mt-3">
                            <Label for="description">Description:</Label>
                            <Textarea id="description" autocomplete="off" v-model="form.updateNote.description"
                                placeholder="Type your description here." class="block w-3/4 mt-1"
                                @keyup.enter="updateLoader" />
                            <InputError :message="form.errors.updateNote?.description" class="mt-2" />
                        </div>
                    </div>
                </div>
                <div class="mt-6 space-y-2">
                    <Label for="unsupported_at" class="">Unsupported at:</Label> <br>
                    <Popover>
                        <PopoverTrigger as-child>
                            <Button
                                variant="outline"
                                :class="cn(
                                    'w-[280px] justify-start text-left font-normal',
                                    !form.unsupported_at && 'text-muted-foreground',
                                )"
                            >
                                <CalendarIcon class="w-4 h-4 mr-2" />
                                {{ form.unsupported_at ? df.format(form.unsupported_at.toDate(getLocalTimeZone())) : 'Pick a date' }}
                            </Button>
                        </PopoverTrigger>
                        <PopoverContent class="w-auto p-0">
                            <Calendar class="mt-2" v-model="form.unsupported_at" id="unsupported_at" initial-focus />
                        </PopoverContent>
                    </Popover>
                    <InputError :message="form.errors.unsupported_at" class="mt-2" />
                </div>
                <div class="flex justify-end mt-6">
                    <Button @click="closeModal">Cancel</Button>
                    <Button class="ms-3" :class="{ 'opacity-25': form.processing }" :disabled="form.processing"
                        @click="updateLoader()">
                        Save
                    </Button>
                </div>
            </div>
        </Modal>
    </DropdownMenu>
</template>

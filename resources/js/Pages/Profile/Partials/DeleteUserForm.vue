<script setup>
    import DangerButton from '@/Components/DangerButton.vue';
    import InputError from '@/Components/InputError.vue';
    import InputLabel from '@/Components/InputLabel.vue';
    import Modal from '@/Components/Modal.vue';
    import SecondaryButton from '@/Components/SecondaryButton.vue';
    import TextInput from '@/Components/TextInput.vue';
    import Button from '@/components/ui/button/Button.vue';
import Input from '@/components/ui/input/Input.vue';
import Label from '@/components/ui/label/Label.vue';
    import {
        useForm
    } from '@inertiajs/vue3';
    import {
        nextTick,
        ref
    } from 'vue';

    const confirmingUserDeletion = ref(false);
    const passwordInput = ref(null);

    const form = useForm({
        password: '',
    });

    const confirmUserDeletion = () => {
        confirmingUserDeletion.value = true;

        nextTick(() => passwordInput.value.focus());
    };

    const deleteUser = () => {
        form.delete(route('profile.destroy'), {
            preserveScroll: true,
            onSuccess: () => closeModal(),
            onError: () => passwordInput.value.focus(),
            onFinish: () => form.reset(),
        });
    };

    const closeModal = () => {
        confirmingUserDeletion.value = false;

        form.clearErrors();
        form.reset();
    };
</script>

<template>
    <section class="space-y-6">
        <header>
            <h2 class="text-lg font-medium ">
                Delete Account
            </h2>

            <p class="mt-1 text-sm">
                Once your account is deleted, all of its resources and data will
                be permanently deleted. Before deleting your account, please
                download any data or information that you wish to retain.
            </p>
        </header>

        <Button variant="destructive" @click="confirmUserDeletion">Delete Account</Button>

        <Modal :show="confirmingUserDeletion" @close="closeModal">
            <div class="p-6">
                <h2 class="text-lg font-medium ">
                    Are you sure you want to delete your account?
                </h2>

                <p class="mt-1 text-sm ">
                    Once your account is deleted, all of its resources and data
                    will be permanently deleted. Please enter your password to
                    confirm you would like to permanently delete your account.
                </p>

                <div class="mt-6">
                    <Label for="password" class="sr-only">Password</Label>
                    <Input id="password" ref="passwordInput" v-model="form.password" type="password"
                        class="block w-3/4 mt-1" placeholder="Password" @keyup.enter="deleteUser" />
                    <InputError :message="form.errors.password" class="mt-2" />
                </div>

                <div class="flex justify-end mt-6">
                    <Button variant="secondary" @click="closeModal">
                        Cancel
                    </Button>

                    <Button variant="destructive" class="ms-3" :class="{ 'opacity-25': form.processing }" :disabled="form.processing"
                        @click="deleteUser">
                        Delete Account
                    </Button>
                </div>
            </div>
        </Modal>
    </section>
</template>

<script setup>
    // import Checkbox from '@/Components/Checkbox.vue';
    import GuestLayout from '@/Layouts/GuestLayout.vue';
    import InputError from '@/Components/InputError.vue';
    // import InputLabel from '@/Components/InputLabel.vue';
    // import PrimaryButton from '@/Components/PrimaryButton.vue';
    // import TextInput from '@/Components/TextInput.vue';
    import {
        Head,
        Link,
        useForm
    } from '@inertiajs/vue3';
    import {
        Button
    } from '@/components/ui/button'
    import {
        Card,
        CardContent,
        CardDescription,
        CardFooter,
        CardHeader,
        CardTitle
    } from '@/components/ui/card'
    import {
        Input
    } from '@/components/ui/input'
    import {
        Label
    } from '@/components/ui/label'
import Checkbox from '@/components/ui/checkbox/Checkbox.vue';

    defineProps({
        canResetPassword: {
            type: Boolean,
        },
        status: {
            type: String,
        },
    });

    const form = useForm({
        email: 'admin@bit-auth.com',
        password: '',
        remember: true,
    });

    const submit = () => {
        form.post(route('login'), {
            onFinish: () => form.reset('password'),
        });
    };
</script>

<template>
    <GuestLayout>

        <Head title="Log in" />

        <div v-if="status" class="mb-4 text-sm font-medium text-green-600">
            {{ status }}
        </div>
        <form @submit.prevent="submit">
            <Card class="border-none shadow-none">
            <CardHeader>
                <CardTitle class="text-2xl">
                    Login
                </CardTitle>
                <CardDescription>
                    Enter your email below to login to your account.
                </CardDescription>
            </CardHeader>
            <CardContent class="grid gap-4">
                <div class="grid gap-2">
                    <Label for="email">Email</Label>
                    <Input v-model="form.email" autocomplete="off"  id="email" type="email" placeholder="admin@bit-auth.com"  />
                    <InputError class="mt-2" :message="form.errors.email" />
                </div>
                <div class="grid gap-2">
                    <div class="flex justify-between">
                        <Label for="password">Password</Label>
                        <Link
                            v-if="canResetPassword"
                            :href="route('password.request')"
                            class="text-sm text-gray-600 underline rounded-md hover:text-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:text-gray-400 dark:hover:text-gray-100 dark:focus:ring-offset-gray-800"
                        >
                            Forgot your password?
                        </Link>
                    </div>
                    <Input v-model="form.password" id="password" type="password" autocomplete="off"  />
                    <InputError class="mt-2" :message="form.errors.password" />
                </div>
                <div class="flex items-center space-x-2">
                    <Checkbox v-model:checked="form.remember" id="remember" />
                    <Label
                    for="terms"
                    class="text-sm font-medium leading-none peer-disabled:cursor-not-allowed peer-disabled:opacity-70"
                    >
                    Remember me
                    </Label>
                </div>
            </CardContent>
            <CardFooter>

                <Button class=""
                    :class="{ 'opacity-25': form.processing }"
                    :disabled="form.processing">
                    Sign in
                </Button>

            </CardFooter>
            </Card>
        </form>
    </GuestLayout>
</template>
<!--
<template>
    <GuestLayout>
        <Head title="Log in" />

        <div v-if="status" class="mb-4 text-sm font-medium text-green-600">
            {{ status }}
        </div>

        <form @submit.prevent="submit">
            <div>
                <InputLabel for="email" value="Email" />

                <TextInput
                    id="email"
                    type="email"
                    class="block w-full mt-1"
                    v-model="form.email"
                    required
                    autofocus
                    autocomplete="username"
                />

                <InputError class="mt-2" :message="form.errors.email" />
            </div>

            <div class="mt-4">
                <InputLabel for="password" value="Password" />

                <TextInput
                    id="password"
                    type="password"
                    class="block w-full mt-1"
                    v-model="form.password"
                    required
                    autocomplete="current-password"
                />

                <InputError class="mt-2" :message="form.errors.password" />
            </div>

            <div class="block mt-4">
                <label class="flex items-center">
                    <Checkbox name="remember" v-model:checked="form.remember" />
                    <span class="text-sm text-gray-600 ms-2 dark:text-gray-400"
                        >Remember me</span
                    >
                </label>
            </div>

            <div class="flex items-center justify-end mt-4">
                <Link
                    v-if="canResetPassword"
                    :href="route('password.request')"
                    class="text-sm text-gray-600 underline rounded-md hover:text-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:text-gray-400 dark:hover:text-gray-100 dark:focus:ring-offset-gray-800"
                >
                    Forgot your password?
                </Link>

                <PrimaryButton
                    class="ms-4"
                    :class="{ 'opacity-25': form.processing }"
                    :disabled="form.processing"
                >
                    Log in
                </PrimaryButton>
            </div>
        </form>
    </GuestLayout>
</template> -->

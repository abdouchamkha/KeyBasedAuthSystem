<script setup>
    import {
        Bell,
        CircleUser,
        Home,
        LineChart,
        Menu,
        Package,
        Package2,
        Search,
        ShoppingCart,
        Users,
        Wrench
    } from 'lucide-vue-next'

    import {
        Badge
    } from '@/components/ui/badge'
    import {
        Button
    } from '@/components/ui/button'
    import {
        Card,
        CardContent,
        CardDescription,
        CardHeader,
        CardTitle
    } from '@/components/ui/card'
    import {
        DropdownMenu,
        DropdownMenuContent,
        DropdownMenuItem,
        DropdownMenuLabel,
        DropdownMenuSeparator,
        DropdownMenuTrigger
    } from '@/components/ui/dropdown-menu'
    import {
        Input
    } from '@/components/ui/input'
    import {
        Sheet,
        SheetContent,
        SheetTrigger
    } from '@/components/ui/sheet'
</script>
<template>
    <Sheet>
        <SheetTrigger as-child>
            <Button variant="outline" size="icon" class="shrink-0 md:hidden">
                <Menu class="w-5 h-5" />
                <span class="sr-only">Toggle navigation menu</span>
            </Button>
        </SheetTrigger>
        <SheetContent side="left" class="flex flex-col">
            <nav class="grid gap-2 text-lg font-medium">
                <a href="/" class="flex items-center gap-2 text-lg font-semibold">
                    <!-- <Package2 class="w-6 h-6" /> -->
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6" viewBox="0 0 24 24">
                        <path fill="currentColor"
                            d="M12 23C6.443 21.765 2 16.522 2 11V5l10-4l10 4v6c0 5.524-4.443 10.765-10 12M4 6v5a10.58 10.58 0 0 0 8 10a10.58 10.58 0 0 0 8-10V6l-8-3Z" />
                        <circle cx="12" cy="8.5" r="2.5" fill="currentColor" />
                        <path fill="currentColor"
                            d="M7 15a5.78 5.78 0 0 0 5 3a5.78 5.78 0 0 0 5-3c-.025-1.896-3.342-3-5-3c-1.667 0-4.975 1.104-5 3" />
                    </svg>
                    <span class="break-words">Admin panel</span>
                </a>

                <a href="/"
                    class="mx-[-0.65rem] flex items-center gap-4 rounded-xl px-3 py-2 text-muted-foreground hover:text-foreground">
                    <Home class="w-5 h-5" />
                    Dashboard
                </a>

                <a href="#"
                    class="mx-[-0.65rem] flex items-center gap-4 rounded-xl bg-muted px-3 py-2 text-foreground hover:text-foreground">
                    <ShoppingCart class="w-5 h-5" />
                    Orders
                    <!-- <Badge class="flex items-center justify-center w-6 h-6 ml-auto rounded-full shrink-0">6</Badge> -->
                </a>

                <a href="#"
                    class="mx-[-0.65rem] flex items-center gap-4 rounded-xl px-3 py-2 text-muted-foreground hover:text-foreground">
                    <Users class="w-5 h-5" />
                    Users
                </a>

                <a href="#"
                    class="mx-[-0.65rem] flex items-center gap-4 rounded-xl px-3 py-2 text-muted-foreground hover:text-foreground">
                    <Package class="w-5 h-5" />
                    Products
                </a>
                <a href="#"
                    class="mx-[-0.65rem] flex items-center gap-4 rounded-xl px-3 py-2 text-muted-foreground hover:text-foreground">
                    <Package class="w-5 h-5" />
                    Files
                </a>

                <a href="#"
                    class="mx-[-0.65rem] flex items-center gap-4 rounded-xl px-3 py-2 text-muted-foreground hover:text-foreground">
                    <Wrench class="w-5 h-5" />
                    Loader updates
                </a>

                <a href="#"
                    class="mx-[-0.65rem] flex items-center gap-4 rounded-xl px-3 py-2 text-muted-foreground hover:text-foreground">
                    <LineChart class="w-5 h-5" />
                    Analytics
                </a>
            </nav>

            <div class="mt-auto">
                <Card>
                    <CardHeader>
                        <CardTitle>Upgrade to Pro</CardTitle>
                        <CardDescription>
                            Unlock all features and get unlimited access to our
                            support team.
                        </CardDescription>
                    </CardHeader>
                    <CardContent>
                        <Button size="sm" class="w-full">
                            Upgrade
                        </Button>
                    </CardContent>
                </Card>
            </div>
        </SheetContent>
    </Sheet>
</template>


<style lang="scss" scoped>

</style>

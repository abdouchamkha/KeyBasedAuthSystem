<script setup>
import { Bell, CircleUser, Home, LineChart, Menu, Package, Package2, Search, ShoppingCart, Users } from 'lucide-vue-next'

import { Badge } from '@/components/ui/badge'
import { Button } from '@/components/ui/button'
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card'
import { DropdownMenu, DropdownMenuContent, DropdownMenuItem, DropdownMenuLabel, DropdownMenuSeparator, DropdownMenuTrigger } from '@/components/ui/dropdown-menu'
import { Input } from '@/components/ui/input'
import { Sheet, SheetContent, SheetTrigger } from '@/components/ui/sheet'
import ResponsiveSideBar from './ResponsiveSideBar.vue'
import { Link } from '@inertiajs/vue3'
</script>
<template>
    <header class="flex h-14 items-center gap-4 border-b bg-muted/40 px-4 lg:h-[60px] lg:px-6">
                <ResponsiveSideBar />
                <div class="flex-1 w-full">
                    <form>
                        <div class="relative">
                            <Search class="absolute left-2.5 top-2.5 h-4 w-4 text-muted-foreground" />
                            <Input type="search" placeholder="Search products..."
                                class="w-full pl-8 shadow-none appearance-none bg-background md:w-2/3 lg:w-1/3" />
                        </div>
                    </form>
                </div>
                <DropdownMenu>
                    <DropdownMenuTrigger as-child>
                        <Button variant="secondary" size="icon" class="rounded-full">
                            <CircleUser class="w-5 h-5" />
                            <span class="sr-only">Toggle user menu</span>
                        </Button>
                    </DropdownMenuTrigger>
                    <DropdownMenuContent align="end">
                        <DropdownMenuLabel>My Account</DropdownMenuLabel>
                        <DropdownMenuSeparator />
                        <Link :href="route('profile.edit')"><DropdownMenuItem>Settings</DropdownMenuItem></Link>
                        <DropdownMenuItem>Support</DropdownMenuItem>
                        <DropdownMenuSeparator />
                        <Link :href="route('logout')" method="post"><DropdownMenuItem>Logout</DropdownMenuItem></Link>
                    </DropdownMenuContent>
                </DropdownMenu>
            </header>
</template>
<style lang="scss" scoped>

</style>

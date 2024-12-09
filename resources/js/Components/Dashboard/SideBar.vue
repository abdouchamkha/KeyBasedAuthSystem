<script setup>
import { Bell, Boxes, CircleUser, Home, LineChart, Menu, Package, Package2, Search, ShoppingCart, Users, Wrench } from 'lucide-vue-next'

import { Badge } from '@/components/ui/badge'
import { Button } from '@/components/ui/button'
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card'
import { DropdownMenu, DropdownMenuContent, DropdownMenuItem, DropdownMenuLabel, DropdownMenuSeparator, DropdownMenuTrigger } from '@/components/ui/dropdown-menu'
import { Input } from '@/components/ui/input'
import { Sheet, SheetContent, SheetTrigger } from '@/components/ui/sheet'
import { Link, usePage } from '@inertiajs/vue3'

// Reactive reference to the current page URL or path
const page = usePage()
</script>

<template>
  <div class="hidden border-r bg-muted/40 md:block">
    <div class="flex flex-col h-full max-h-screen gap-2">
      <div class="flex h-14 items-center border-b px-4 lg:h-[60px] lg:px-6">
        <Link :href="route('dashboard')" class="flex items-center gap-2 font-semibold">
          <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6" viewBox="0 0 24 24">
            <path fill="currentColor" d="M12 23C6.443 21.765 2 16.522 2 11V5l10-4l10 4v6c0 5.524-4.443 10.765-10 12M4 6v5a10.58 10.58 0 0 0 8 10a10.58 10.58 0 0 0 8-10V6l-8-3Z"/>
            <circle cx="12" cy="8.5" r="2.5" fill="currentColor"/>
            <path fill="currentColor" d="M7 15a5.78 5.78 0 0 0 5 3a5.78 5.78 0 0 0 5-3c-.025-1.896-3.342-3-5-3c-1.667 0-4.975 1.104-5 3"/>
          </svg>
          <span class="break-words">Admin panel</span>
        </Link>
        <Button variant="outline" size="icon" class="w-8 h-8 ml-auto">
          <Bell class="w-4 h-4"/>
          <span class="sr-only">Toggle notifications</span>
        </Button>
      </div>
      <div class="flex-1">
        <nav class="grid items-start px-2 text-sm font-medium lg:px-4">
          <Link
            :href="route('dashboard')"
            class="flex items-center gap-3 px-3 py-2 transition-all rounded-lg hover:text-primary"
            :class="page.component === 'Dashboard' ? 'bg-muted text-primary' : 'text-muted-foreground'"
          >
            <Home class="w-4 h-4"/>
            Dashboard
          </Link>
          <Link
            :href="route('orders')"
            class="flex items-center gap-3 px-3 py-2 transition-all rounded-lg hover:text-primary"
            :class="page.component === 'Orders' ? 'bg-muted text-primary' : 'text-muted-foreground'"
          >
            <ShoppingCart class="w-4 h-4"/>
            Orders
          </Link>
          <Link
            :href="route('users')"
            class="flex items-center gap-3 px-3 py-2 transition-all rounded-lg hover:text-primary"
            :class="page.component === 'Users' ? 'bg-muted text-primary' : 'text-muted-foreground'"
          >
            <Users class="w-4 h-4"/>
            Users
          </Link>
          <Link
            :href="route('products')"
            class="flex items-center gap-3 px-3 py-2 transition-all rounded-lg hover:text-primary"
            :class="page.component === 'Products' ? 'bg-muted text-primary' : 'text-muted-foreground'"
          >
            <Boxes class="w-4 h-4"/>
            Products
          </Link>
          <Link
            :href="route('files.index')"
            class="flex items-center gap-3 px-3 py-2 transition-all rounded-lg hover:text-primary"
            :class="page.component === 'Files' ? 'bg-muted text-primary' : 'text-muted-foreground'"
          >
            <Boxes class="w-4 h-4"/>
            Files
          </Link>
          <Link
            :href="route('loader-updates.index')"
            class="flex items-center gap-3 px-3 py-2 transition-all rounded-lg hover:text-primary"
            :class="page.component === 'LoaderUpdates' ? 'bg-muted text-primary' : 'text-muted-foreground'"
          >
            <Wrench class="w-4 h-4"/>
            Loader updates
          </Link>
        </nav>
      </div>
      <div class="p-4 mt-auto">
        <Card>
          <CardHeader class="p-2 pt-0 md:p-4">
            <CardTitle>Upgrade to Pro</CardTitle>
            <CardDescription>
              Unlock all features and get unlimited access to our support team.
            </CardDescription>
          </CardHeader>
          <CardContent class="p-2 pt-0 md:p-4 md:pt-0">
            <Button size="sm" class="w-full">
              Upgrade
            </Button>
          </CardContent>
        </Card>
      </div>
    </div>
  </div>
</template>

<style lang="scss" scoped>
</style>

<script setup>

import SideBar from '@/Components/Dashboard/SideBar.vue'
import ResponsiveSideBar from '@/Components/Dashboard/ResponsiveSideBar.vue'
import Header from '@/Components/Dashboard/Header.vue'

</script>
<template>
    <div class="grid min-h-screen w-full md:grid-cols-[220px_1fr] lg:grid-cols-[280px_1fr]">
        <SideBar/>
        <div class="flex flex-col">
            <Header/>
            <main class="flex flex-col flex-1 gap-4 p-4 lg:gap-6 lg:p-6">
                <div class="flex items-center" v-if="$slots.header">
                        <slot name="header"/>
                </div>
                <slot/>
            </main>
        </div>
    </div>
</template>

<script setup>
import {
    Select,
    SelectContent,
    SelectGroup,
    SelectItem,
    SelectLabel,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { defineProps } from 'vue';

// Define the props to accept an array of options and a label
const props = defineProps({
    options: {
        type: Array,
        required: true,
    },
    selectLabel: {
        type: String,
        default: 'Select an option',
    }
});
</script>

<template>
    <Select>
        <SelectTrigger class="w-[180px]">
            <SelectValue placeholder="Choose an option" />
        </SelectTrigger>
        <SelectContent>
            <SelectGroup>
                <SelectLabel>{{ selectLabel }}</SelectLabel>
                <!-- Loop through options passed as props to render each SelectItem -->
                <SelectItem
                    v-for="(option, index) in options"
                    :key="index"
                    :value="option.value"
                >
                    {{ option.label }}
                </SelectItem>
            </SelectGroup>
        </SelectContent>
    </Select>
</template>

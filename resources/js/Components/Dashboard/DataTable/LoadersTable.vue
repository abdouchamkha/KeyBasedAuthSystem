<script setup>
import {  ref } from 'vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';

import {
  Table,
  TableBody,
  TableCell,
  TableHead,
  TableHeader,
  TableRow,
} from '@/components/ui/table';
import { CaretSortIcon } from '@radix-icons/vue';
import DataTableDropDown from './DataTableDropDown.vue';

const props = defineProps({
  loaders: Object,
  filters: Object,
});

const data = props.loaders.data;
const columns = [
    { accessorKey: 'id', header: 'id' },
    { accessorKey: 'lang', header: 'Language' },
    { accessorKey: 'stage', header: 'App Stage' },
    { accessorKey: 'loader_type', header: 'Loader Type' },
  { accessorKey: 'version', header: 'Version' },
  { accessorKey: 'hash', header: 'Hash' },
  { accessorKey: 'path', header: 'Path' },
  { accessorKey: 'unsupported_at', header: 'Unsupported At' },
  { accessorKey: 'tags', header: 'Tags' },
  { accessorKey: 'update_note', header: 'Update Note' },
  { id: 'actions', header: 'Actions' },
];

const search = ref(props.filters?.search || '');
const sort = ref(props.filters?.sort || '');
const direction = ref(props.filters?.direction || 'asc');

function updateFilters() {
  Inertia.get(this.$page.url, { search: search.value }, { preserveState: true, replace: true });
}

function sortBy(column) {
  if (sort.value === column) {
    direction.value = direction.value === 'asc' ? 'desc' : 'asc';
  } else {
    sort.value = column;
    direction.value = 'asc';
  }
  Inertia.get(this.$page.url, { sort: sort.value, direction: direction.value }, { preserveState: true, replace: true });
}

function isSortedBy(column) {
  return sort.value === column;
}

function changePage(page) {
  Inertia.get(this.$page.url, { page }, { preserveState: true, preserveScroll: true });
}
</script>
<template>
  <div class="w-full">
    <div class="flex items-center py-4">
      <Input
        class="max-w-sm"
        placeholder="Search..."
        v-model="search"
        @input="updateFilters"
      />
    </div>

    <div class="border rounded-md">
      <Table>
        <TableHeader>
          <TableRow>
            <TableHead
              v-for="column in columns"
              :key="column.accessorKey || column.id"
              @click="sortBy(column.accessorKey)"
              class="cursor-pointer select-none"
            >
              {{ column.header }}
              <CaretSortIcon
                v-if="isSortedBy(column.accessorKey)"
                :class="direction === 'asc' ? 'rotate-180' : ''"
                class="inline-block w-4 h-4 ml-1"
              />
            </TableHead>
          </TableRow>
        </TableHeader>

        <TableBody>
          <template v-if="data.length">
            <TableRow v-for="loader in data" :key="loader.id">
              <TableCell>{{ loader.id }}</TableCell>
              <TableCell>{{ loader.lang }}</TableCell>
              <TableCell><Badge :variant="loader.stage == 'production' ? 'secondary':''">{{ loader.stage }}</Badge></TableCell>
              <TableCell>{{ loader.loader_type }}</TableCell>
              <TableCell>{{ loader.version }}</TableCell>
              <TableCell>{{ loader.hash }}</TableCell>
              <TableCell>{{ loader.path }}</TableCell>
              <TableCell>{{ loader.unsupported_at ?? 'not set' }}</TableCell>
              <TableCell>{{ loader.tags ? JSON.stringify(loader.tags) : 'not set' }}</TableCell>
              <TableCell>{{ loader.update_note ? JSON.stringify(loader.update_note) : 'not set'}}</TableCell>
              <TableCell>
                <DataTableDropDown :loader="loader" />
              </TableCell>
            </TableRow>
          </template>

          <TableRow v-else>
            <TableCell :colspan="columns.length" class="h-24 text-center">
              No results.
            </TableCell>
          </TableRow>
        </TableBody>
      </Table>
    </div>

    <div class="flex items-center justify-between py-4">
      <div class="text-sm text-muted-foreground">
        Showing {{ props.loaders.from }} to {{ props.loaders.to }} of {{ props.loaders.total }} results
      </div>
      <div class="space-x-2">
        <Button
          variant="outline"
          size="sm"
          :disabled="!props.loaders.prev_page_url"
          @click="changePage(props.loaders.current_page - 1)"
        >
          Previous
        </Button>
        <Button
          variant="outline"
          size="sm"
          :disabled="!props.loaders.next_page_url"
          @click="changePage(props.loaders.current_page + 1)"
        >
          Next
        </Button>
      </div>
    </div>
  </div>
</template>

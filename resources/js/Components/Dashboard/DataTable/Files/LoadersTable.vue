<script setup>
import { ref, computed } from 'vue';
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

// Define props
const props = defineProps({
    loaders: {
    type: Object,
    required: true,
  },
  filters: {
    type: Object,
    default: () => ({}),
  },
});


// Ensure data is properly extracted
const data = computed(() => props.loaders?.files?.data || []);

const columns = [
  { accessorKey: 'id', header: 'ID' },
  { accessorKey: 'name', header: 'File Name' },
  { accessorKey: 'uploaded_by', header: 'Uploaded By' },
  { accessorKey: 'size', header: 'File Size' },
  { accessorKey: 'tags', header: 'Tags' },
  { id: 'actions', header: 'Actions' },
];

const search = ref(props.loaders.filters?.search || '');
const sort = ref(props.loaders.filters?.sort || '');
const direction = ref(props.loaders.filters?.direction || 'asc');

// Functions to handle sorting and pagination
function updateFilters() {
  Inertia.get(route(this.$page.url), { search: search.value }, { preserveState: true, replace: true });
}

function sortBy(column) {
  if (sort.value === column) {
    direction.value = direction.value === 'asc' ? 'desc' : 'asc';
  } else {
    sort.value = column;
    direction.value = 'asc';
  }
  Inertia.get(route(this.$page.url), { sort: sort.value, direction: direction.value }, { preserveState: true, replace: true });
}

function isSortedBy(column) {
  return sort.value === column;
}

function changePage(page) {
  Inertia.get(route(this.$page.url), { page }, { preserveState: true, replace: true });
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
              @click="column.accessorKey && sortBy(column.accessorKey)"
              class="cursor-pointer select-none"
            >
              {{ column.header }}
              <CaretSortIcon
                v-if="column.accessorKey && isSortedBy(column.accessorKey)"
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
              <TableCell>{{ loader.name }}</TableCell>
              <TableCell>{{ loader.uploaded_by || 'Not Set' }}</TableCell>
              <TableCell>{{ loader.size || 'Unknown' }}</TableCell>
              <TableCell>{{ loader.tags ? JSON.stringify(loader.tags) : 'Not Set' }}</TableCell>
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
        Showing {{ props.files?.from || 0 }} to {{ props.files?.to || 0 }} of {{ props.files?.total || 0 }} results
      </div>
      <div class="space-x-2">
        <Button
          variant="outline"
          size="sm"
          :disabled="!props.files?.prev_page_url"
          @click="changePage(props.files?.current_page - 1)"
        >
          Previous
        </Button>
        <Button
          variant="outline"
          size="sm"
          :disabled="!props.files?.next_page_url"
          @click="changePage(props.files?.current_page + 1)"
        >
          Next
        </Button>
      </div>
    </div>
  </div>
</template>

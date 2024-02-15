<template>
    <button @click="onShareClick"
            class="inline-flex items-center px-4 py-2 text-sm font-medium text-gray-900 bg-white border border-gray-200 rounded-lg hover:bg-gray-100 hover:text-blue-700 focus:z-10 focus:ring-2 focus:ring-blue-700 focus:text-blue-700 dark:bg-gray-700 dark:border-gray-600 dark:text-white dark:hover:text-white dark:hover:bg-gray-600 dark:focus:ring-blue-500 dark:focus:text-white ml-3">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4 mr-2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M7.217 10.907a2.25 2.25 0 1 0 0 2.186m0-2.186c.18.324.283.696.283 1.093s-.103.77-.283 1.093m0-2.186 9.566-5.314m-9.566 7.5 9.566 5.314m0 0a2.25 2.25 0 1 0 3.935 2.186 2.25 2.25 0 0 0-3.935-2.186Zm0-12.814a2.25 2.25 0 1 0 3.933-2.185 2.25 2.25 0 0 0-3.933 2.185Z" />
            </svg>
        Share
    </button>
    <ShareFileModal v-model="toggleModal" :all-selected="props.all" :selected-ids="props.sharedIds"/>
    <!-- <ConfirmationDialog 
        :show="showConfirmatonDialog" 
        :message="'Are you sure want to delete the selected files?'"
        @cancel="onDeleteCancel"
        @confirm="onDeleteConfirm"
    >
    </ConfirmationDialog> -->
</template>

<script setup>
    import { ref } from 'vue';
    import ConfirmationDialog from '../ConfirmationDialog.vue';
    import ShareFileModal from '@/Components/app/ShareFileModal.vue';
    import { useForm, usePage } from '@inertiajs/vue3';
    import { showErrorDialog, showSuccessNotification } from '@/event-bus';

    const toggleModal = ref(false);
    const page = usePage();
    const props = defineProps({
        all:{
            type: Boolean,
            required: false,
            default: false
        },
        sharedIds: {
            type: Array,
            required: false
        }
    })

    const emit = defineEmits(['delete']);
    const onShareClick = ()=>{
        if (!props.all && !props.sharedIds.length) {
            showErrorDialog('Please select at least one file to share');
            return;
        }
        toggleModal.value = true;
    }
</script>

<style scoped></style>
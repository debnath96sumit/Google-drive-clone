<template>
    <button @click="download"
            class="inline-flex items-center px-4 py-2 text-sm font-medium text-gray-900 bg-white border border-gray-200 rounded-lg hover:bg-gray-100 hover:text-blue-700 focus:z-10 focus:ring-2 focus:ring-blue-700 focus:text-blue-700 dark:bg-gray-700 dark:border-gray-600 dark:text-white dark:hover:text-white dark:hover:bg-gray-600 dark:focus:ring-blue-500 dark:focus:text-white">
                <svg  xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4 mr-2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5M16.5 12 12 16.5m0 0L7.5 12m4.5 4.5V3" />
                </svg>

        Download
    </button>

</template>

<script setup>
    import { useForm, usePage } from '@inertiajs/vue3';
    import PrimaryButton from '../PrimaryButton.vue';
    import httpGet from '@/Helper/Http-helper';
    
    const page = usePage();
    
    const props = defineProps({
        all:{
            type: Boolean,
            required: false,
            default: false
        },
        ids: {
            type: Array,
            required: false
        },
        sharedWithMe: false,
        sharedByMe: false,
    })
    function download() {
        if (!props.all && props.ids.length == 0) {
            return;
        }

        const params = new URLSearchParams();
        if (page.props.folder?.id) {
            params.append('parent_id', page.props.folder.id);
        }

        if (props.all) {
            params.append('all', props.all ? 1 : 0);
        }else{
            for(let id of props.ids){
                params.append('ids[]', id);
            }
        }
        
        let url = 'file.download';
        if (props.sharedWithMe) {
            url = 'file.downloadSharedWithMeFiles';
        } else if(props.sharedByMe){
            url = 'file.downloadSharedByMeFiles';
        }
        httpGet(route(url) + '?' + params.toString())
            .then(res =>{
                // console.log(res);
                if(!res.url) return;

                let a = document.createElement('a');
                a.href = res.url;
                a.download = res.file_name;
                a.click();
            })
    }
</script>

<style scoped></style>
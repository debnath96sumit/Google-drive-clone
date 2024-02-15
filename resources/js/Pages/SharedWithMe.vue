<template>
    <AuthenticatedLayout>
        <nav class="flex item-center justify-end p-1 mb-3">
            <div class="mr-2">
                <DownloadFilesButton :all="allSelected" :ids="selectedIds" :shared-with-me="true" class="mx-2"/>
            </div>
        </nav>

        <div class="flex-1 overflow-auto">
            <table class="min-w-full">
                <thead class="bg-gray-100 border-b">
                    <tr>
                        <th class="text-sm font-medium text-grey-900 px-6 py-4 text-left w-[30px] max-w-[30px] pr-0">
                            <Checkbox @change="onSelectAllChange" v-model:checked="allSelected"/>
                        </th>
                        <th class="text-sm font-medium text-grey-900 px-6 py-4 text-left">
                            Name
                        </th>
                        <th class="text-sm font-medium text-grey-900 px-6 py-4 text-left">
                            Path
                        </th>
                    </tr>
                </thead>
                <tbody>
                    <tr 
                        v-for="file in allFiles.data" 
                        :key="file.id" 
                        class="border-b transition duration-300 ease-in-out hover:bg-blue-100 cursor-pointer"
                        :class="(selected[file.id] || allSelected) ? 'bg-blue-50' : 'bg-white'"
                        @click="toggleSelectFile(file)"
                    >
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium  text-gray-900  w-[30px] max-w-[30px] pr-0">
                            <Checkbox @change="$event =>onSelectCheckboxChange(file)" v-model="selected[file.id]" :checked="selected[file.id] || allSelected"/>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium  text-gray-900 flex items-center">
                            <FileIcons :file="file"/>
                            {{ file.name }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium  text-gray-900">{{ file.path }}</td>
                    </tr>
                </tbody>
            </table>
            <div v-if="!props.files.data.length" class="py-8 text-center text-lg text-gray-900">
                There is no data in the folder
            </div>
            <div ref="loadMoreIntersect"></div>
        </div>
    </AuthenticatedLayout>
</template>
<script setup>
    import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
    import { router, Link } from '@inertiajs/vue3';
    import {HomeIcon} from '@heroicons/vue/20/solid'
    import DownloadFilesButton from '@/Components/app/DownloadFilesButton.vue';
    import FileIcons from '@/Components/app/FileIcons.vue';
    import Checkbox from '@/Components/Checkbox.vue';
    import { computed, onMounted, onUpdated } from 'vue';
    import { ref } from 'vue';
    import httpGet from "@/Helper/Http-helper";
    const loadMoreIntersect = ref(null);
    const allSelected = ref(false);
    const selected = ref({});
    const props = defineProps({
        files: Object,
        folder: Object,
        ancestors: Object,
    })

    const allFiles = ref({
        data: props.files.data,
        next: props.files.links.next
    })
    const loadMoreContent = ()=>{
        // console.log(allFiles.value);
        if (allFiles.value.next === null) {
            return;
        }
        httpGet(allFiles.value.next)
            .then(res =>{
                allFiles.value.data = [...allFiles.value.data, ...res.data];
                allFiles.value.next = res.links.next;
            })
    }

    const onSelectAllChange = ()=>{
        allFiles.value.data.forEach(f => {
            selected.value[f.id] = allSelected.value;
        })
    }
    const toggleSelectFile = (file)=>{
        selected.value[file.id] = !selected.value[file.id];
        onSelectCheckboxChange(file);
    }

    const selectedIds = computed(()=> Object.entries(selected.value).filter(a => a[1]).map(a => a[0]));
    const onSelectCheckboxChange = (file)=>{
        if (!selected.value[file.id]) {
            allSelected.value = false;
        }else{
            let isCheckAll = true;

            for (let file of allFiles.value.data) {
                if (!selected.value[file.id]) {
                    isCheckAll = false;
                    break;
                }
            }
            allSelected.value = isCheckAll;
        }
    }

    onUpdated(() => {
        allFiles.value = {
            data: props.files.data,
            next: props.files.links.next
        }
    })
    onMounted(()=>{
        const observer = new IntersectionObserver((entries)=>entries.forEach(entry => entry.isIntersecting  && loadMoreContent()), {
            rootMargin: '-250px 0px 0px 0px'
        } )

        observer.observe(loadMoreIntersect.value);
    })
</script>

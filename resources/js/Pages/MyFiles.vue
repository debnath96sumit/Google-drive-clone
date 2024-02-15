<template>
    <AuthenticatedLayout>
        <nav class="flex item-center justify-between p-1 mb-3">
            <ol class="inline-flexitems-center space-x-1 md:space-x-3">
                <li v-for="anc in props.ancestors.data" :key="anc.id" class="inline-flex items-center">
                    <Link v-if="!anc.parent_id" :href="route('myFiles')"
                          class="inline-flex items-center text-sm font-medium text-gray-700 hover:text-indigo-600 dark:text-gray-400 dark:hover:text-indigo">
                        <HomeIcon style="width: 1.25rem; height: 1.5rem;"/>
                        My Files
                    </Link>
                    <div v-else class="flex items-center">
                        <svg aria-hidden="true" class="w-6 h-6 text-gray-400" fill="currentColor" viewBox="0 0 20 20"
                             xmlns="http://www.w3.org/2000/svg">
                            <path fill-rule="evenodd"
                                  d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z"
                                  clip-rule="evenodd"></path>
                        </svg>
                        <Link :href="route('myFiles', {folder: anc.path})"
                              class="ml-1 text-sm font-medium text-gray-700 hover:text-indigo-600 md:ml-2 dark:text-gray-400 dark:hover:text-indigo">
                            {{ anc.name }}
                        </Link>
                    </div>
                </li>
            </ol>
            <div class="flex">
                <label for="" class="flex items-center mr-3">
                    <Checkbox @change="showOnlyFavourites" v-model:checked="onlyFavourite" class="mr-2"/>
                    Only Favourites
                </label>
                <DownloadFilesButton :all="allSelected" :ids="selectedIds" class="mx-2"/>
                <DeleteFilesButton :delete-all="allSelected" :delete-ids="selectedIds" @delete="onDelete"/>
                <ShareFilesButton :all="allSelected" :shared-ids="selectedIds"/>
            </div>
        </nav>

        <div class="flex-1 overflow-auto">
            <table class="min-w-full">
                <thead class="bg-gray-100 border-b">
                    <tr>
                        <th class="text-sm font-medium text-grey-900 px-6 py-4 text-left w-[30px] max-w-[30px] pr-0">
                            <Checkbox @change="onSelectAllChange" v-model:checked="allSelected"/>
                        </th>
                        <th class="text-sm font-medium text-grey-900 px-6 py-4 text-left"></th>
                        <th class="text-sm font-medium text-grey-900 px-6 py-4 text-left">
                            Name
                        </th>
                        <th v-if="search" class="text-sm font-medium text-grey-900 px-6 py-4 text-left">
                            Path
                        </th>
                        <th class="text-sm font-medium text-grey-900 px-6 py-4 text-left">
                            Owner
                        </th>
                        <th class="text-sm font-medium text-grey-900 px-6 py-4 text-left">
                            Last Modified
                        </th>
                        <th class="text-sm font-medium text-grey-900 px-6 py-4 text-left">
                            Size
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
                        @dblclick="openFolder(file)"
                    >
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium  text-gray-900  w-[30px] max-w-[30px] pr-0">
                            <Checkbox @change="$event =>onSelectCheckboxChange(file)" v-model="selected[file.id]" :checked="selected[file.id] || allSelected"/>
                        </td>
                        <td  class="px-6 py-4 whitespace-nowrap text-sm font-medium  text-yellow-900  w-[15px] max-w-[15px]">
                            <div @click="toggleFavourites(file)">
                                <svg  v-if="file.is_favourite" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-6 h-6">
                                    <path fill-rule="evenodd" d="M10.788 3.21c.448-1.077 1.976-1.077 2.424 0l2.082 5.006 5.404.434c1.164.093 1.636 1.545.749 2.305l-4.117 3.527 1.257 5.273c.271 1.136-.964 2.033-1.96 1.425L12 18.354 7.373 21.18c-.996.608-2.231-.29-1.96-1.425l1.257-5.273-4.117-3.527c-.887-.76-.415-2.212.749-2.305l5.404-.434 2.082-5.005Z" clip-rule="evenodd" />
                                </svg>
                                <svg v-else xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M11.48 3.499a.562.562 0 0 1 1.04 0l2.125 5.111a.563.563 0 0 0 .475.345l5.518.442c.499.04.701.663.321.988l-4.204 3.602a.563.563 0 0 0-.182.557l1.285 5.385a.562.562 0 0 1-.84.61l-4.725-2.885a.562.562 0 0 0-.586 0L6.982 20.54a.562.562 0 0 1-.84-.61l1.285-5.386a.562.562 0 0 0-.182-.557l-4.204-3.602a.562.562 0 0 1 .321-.988l5.518-.442a.563.563 0 0 0 .475-.345L11.48 3.5Z" />
                                </svg>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium  text-gray-900 flex items-center">
                            <FileIcons :file="file"/>
                            {{ file.name }}
                        </td>
                        <td v-if="search" class="px-6 py-4 whitespace-nowrap text-sm font-medium  text-gray-900">{{ file.path }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium  text-gray-900">{{ file.owner }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium  text-gray-900">{{ file.updated_at }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium  text-gray-900">{{ file.size }}</td>
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
    import FileIcons from '@/Components/app/FileIcons.vue';
    import DeleteFilesButton from '@/Components/app/DeleteFilesButton.vue';
    import DownloadFilesButton from '@/Components/app/DownloadFilesButton.vue';
    import ShareFilesButton from '@/Components/app/ShareFilesButton.vue';
    import Checkbox from '@/Components/Checkbox.vue';
    import httpGet from "@/Helper/Http-helper";
    import axios from 'axios';
    import { router, Link } from '@inertiajs/vue3';
    import {HomeIcon} from '@heroicons/vue/20/solid'
    import { computed, onMounted, onUpdated } from 'vue';
    import { ref } from 'vue';
    import { emitter, showSuccessNotification } from "@/event-bus";


    const loadMoreIntersect = ref(null);
    const allSelected = ref(false);
    const onlyFavourite = ref(false);
    const selected = ref({});
    const search = ref('');

    let params = null;
    const props = defineProps({
        files: Object,
        folder: Object,
        ancestors: Object,
    })

    const allFiles = ref({
        data: props.files.data,
        next: props.files.links.next
    })
    const openFolder = (file)=>{
        if (!file.is_folder) {
            return
        }else{
            router.visit(route('myFiles', {folder: file.path}));
        }
    }
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

    const onDelete = ()=>{
        allSelected.value = false;
        selected.value = {};

    }

    const toggleFavourites = (file)=>{
        axios.post(route('file.favourites', file.id))
            .then(response => {
                if (response.data.success) {
                    file.is_favourite = !file.is_favourite;
                    let msg = file.is_favourite ? 'File added to favourites' : 'File removed from favourites';
                    showSuccessNotification(msg);
                }
            })
            .catch(error => {
                console.error('Error:', error);
            });

    }
    const showOnlyFavourites = ()=>{
        // console.log(onlyFavourite.value);
        if (onlyFavourite.value) {
            params.set('favourites', 1);
        }else{
            params.delete('favourites');
        }

        router.get(window.location.pathname+'?'+params.toString());
    }
    onUpdated(() => {
        allFiles.value = {
            data: props.files.data,
            next: props.files.links.next
        }
    })
    onMounted(()=>{
        params = new URLSearchParams(window.location.search);
        onlyFavourite.value = params.get('favourites') === '1';
        search.value = params.get('search');
        emitter.on('ON_SEARCH', (value)=>{
            search.value = value;
        })
        const observer = new IntersectionObserver((entries)=>entries.forEach(entry => entry.isIntersecting  && loadMoreContent()), {
            rootMargin: '-250px 0px 0px 0px'
        } )

        observer.observe(loadMoreIntersect.value);
    })
</script>

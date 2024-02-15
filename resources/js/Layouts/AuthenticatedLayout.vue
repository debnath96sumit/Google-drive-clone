<template>
    <div class="h-screen flex w-full bg-gray-50 gap-4">
        <Navigation/>

        <main  
            class="flex flex-col flex-1 px-4 overflow-hidden"
            @drop.prevent="handleDrop"
            @dragover.prevent="onDragOver"
            @dragleave.prevent="onDragLeave"
            :class=" dragOver ? 'dropzone' : ''"
        >
            <template v-if="dragOver" class="text-gray-500 text-center py-8 text-sm">
                Drop files here to upload
            </template>
            <template v-else>
                <div class="flex items-center justify-between w-full">
                    <SearchForm/>
                    <UserSettingsDropDown/>
                </div>
                <div class="flex-1 flex flex-col overflow-hidden">
                    <slot/>
                </div>
            </template>
        </main> 
    </div>
    <ErrorDialog/>
    <FormProgress :form="fileUploadForm"/>
    <Notification/>
</template>
<script setup>
    import { onMounted, ref } from 'vue';
    import Navigation from "../Components/app/Navigation.vue";
    import SearchForm from "../Components/app/SearchForm.vue";
    import FormProgress from "../Components/app/FormProgress.vue";
    import Notification from "@/Components/Notification.vue";
    import UserSettingsDropDown from "../Components/app/UserSettingsDropDown.vue";
    import { emitter, FILE_UPLOAD_STARTED, showErrorDialog, showSuccessNotification } from '@/event-bus';
    import { useForm, usePage } from '@inertiajs/vue3';
    import ErrorDialog from '@/Components/app/ErrorDialog.vue';
    const dragOver = ref(false);

    const fileUploadForm = useForm({
        files: [],
        relative_paths: [],
        parent_id: null,
    })
    const uploadFiles = (files)=>{
        fileUploadForm.files = files;
        fileUploadForm.relative_paths = [...files].map(f => f.webkitRelativePath);
        fileUploadForm.parent_id = usePage().props.folder.id;
        fileUploadForm.post(route('file.store'), {
            onSuccess: ()=>{
                showSuccessNotification(`${files.length} files have been uploaded`);
            },
            onError: errors =>{
                let message = '';
                if (Object.keys(errors).length > 0 ) {
                    message = errors[Object.keys(errors)[0]];
                }else{
                    message = 'Error while uploading file. Please try later!'
                }
                showErrorDialog(message);
                
            },
            onFinish: ()=>{
                fileUploadForm.clearErrors();
                fileUploadForm.reset();
            }
        });
    }

    const onDragOver = ()=>{
        dragOver.value = true
    }   

    const onDragLeave = ()=>{
        dragOver.value = false
    }

    
    const handleDrop = (ev)=>{
        dragOver.value = false;
        const files = ev.dataTransfer.files
        if (!files.length) {
            return;
        }
        uploadFiles(files);

    }
    onMounted(()=>{
        emitter.on(FILE_UPLOAD_STARTED, uploadFiles);
    })
</script>

<style scoped>
    .dropzone {
        width: 100%;
        height: 100%;
        color: #8d8d8d;
        border: 2px dashed gray;
        display: flex;
        justify-content: center;
        align-items: center;
    }
</style>
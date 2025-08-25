<script setup>
import { Listing, DropdownItem, Header, Button } from "@statamic/cms/ui";
import { requireElevatedSession } from "@statamic/cms"
import { useBackupStore } from "../store";
import { ref } from "vue";
import { useResumable } from "../resumable";

const props = defineProps(['chunkSize']);

const backupStore = useBackupStore();

const listing = ref(null);

const dropZone = ref(null);
const browseTarget = ref(null);

const { files } = useResumable({ chunkSize: props.chunkSize ?? 2 * 1024 * 1024, dropZone, browseTarget, onFileUploaded: (file) => {
  listing.value.refresh();
}});

backupStore.startPolling();



const restoreFrom = async (id) => {

    try {
        await requireElevatedSession();

        const { data } = await window.Statamic.$app.config.globalProperties.$axios.post(cp_url(`api/backups/restore/${id}`));

        Statamic.$toast.info(__(data.message));
    } catch (e) {
        console.error(e);

        if (error.response.data.message) {
            Statamic.$toast.error(message);
        } else {
            Statamic.$toast.error(__('statamic-backup::backup.restore.failed'));
        }
    }
}

const queueBackup = async () => {
    try {
        backupStore.setStatus('backup_in_progress');

        const { data } = await window.Statamic.$app.config.globalProperties.$axios.post(cp_url("api/backups"));

        Statamic.$toast.info(__(data.message));
        listing.value.refresh();
    } catch (e) {
        console.error(e);

        if (error.response.data.message) {
            Statamic.$toast.error(message);
        } else {
            Statamic.$toast.error(__('statamic-backup::backup.restore.failed'));
        }
    }
}

const deleteBackup = async (id) => {
    try {
        await requireElevatedSession();

        const { data } = await window.Statamic.$app.config.globalProperties.$axios.delete(cp_url(`api/backups/${id}`));

        Statamic.$toast.info(__(data.message));
        listing.value.refresh();
    } catch (e) {
        console.error(e);

        if (error.response.data.message) {
            Statamic.$toast.error(message);
        } else {
            Statamic.$toast.error(__('statamic-backup::backup.destroy.failed'));
        }
    }
}

</script>

<template>
    <Header :icon="database" :title="__('statamic-backup::backup.title')">
        <Button variant="subtle" ref="browseTarget">{{ __("statamic-backup::backup.upload.label") }}</Button>
        <Button variant="primary" v-on:click="queueBackup"
            :disabled="!backupStore.abilities.backup.isPossible">{{ __("statamic-backup::backup.create") }}</Button>
    </Header>

    <p v-for="file in files" :key="file.file.uniqueIdentifier" class="mb-2">
        <span>{{ file.file.fileName }}</span>
        <span v-if="file.status === 'uploading'"> - {{ Math.round(file.progress * 100) }}%</span>
        <span v-if="file.status === 'retrying'"> - {{ __('statamic-backup::backup.upload.retrying') }}</span>
        <span v-if="file.status === 'error'" class="text-red-600"> - {{ __('statamic-backup::backup.upload.error') }}</span>
    </p>

    <Listing ref="listing" :allowSearch="false" :allowCustomizingColumns="false" :url="cp_url('api/backups')">
        <template #prepended-row-actions="{ row }">
            <DropdownItem v-if="backupStore.abilities.download.isPermitted"
                :text="__('statamic-backup::backup.download.label')"
                :href="`${cp_url('api/backups/download')}/${row.id}`" />
            <DropdownItem v-if="backupStore.abilities.restore.isPermitted"
                :disabled="!backupStore.abilities.restore.isPossible" @click="restoreFrom(row.id)"
                :text="__('statamic-backup::backup.restore.label')" />
            <DropdownItem v-if="backupStore.abilities.destroy.isPermitted" variant="destructive"
                @click="deleteBackup(row.id)" :text="__('statamic-backup::backup.destroy.label')" />
        </template>
    </Listing>
</template>

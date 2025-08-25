<script setup>
import { Listing, DropdownItem, Header, Button } from "@statamic/cms/ui";
import { requireElevatedSession } from "@statamic/cms"
import { useBackupStore } from "../store";

const backupStore = useBackupStore();

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
    } catch (e) {
        console.error(e);

        if (error.response.data.message) {
            Statamic.$toast.error(message);
        } else {
            Statamic.$toast.error(__('statamic-backup::backup.restore.failed'));
        }
    }
}

</script>
<template>
    <Header :icon="database" :title="__('statamic-backup::backup.title')">
        <Button icon="save" variant="primary" v-on:click="queueBackup" :disabled="!backupStore.abilities.backup.isPossible">{{ __("statamic-backup::backup.create") }}</Button>
    </Header>

    <Listing :allowSearch="false" :allowCustomizingColumns="false" :url="cp_url('api/backups')">
        <template #prepended-row-actions="{ row }">
            <DropdownItem v-if="backupStore.abilities.download.isPermitted"
                :text="__('statamic-backup::backup.download.label')"
                :href="`${cp_url('api/backups/download')}/${row.id}`" />
            <DropdownItem v-if="backupStore.abilities.restore.isPermitted"
                :disabled="!backupStore.abilities.restore.isPossible" @click="restoreFrom(row.id)"
                :text="__('statamic-backup::backup.restore.label')" />
            <DropdownItem v-if="backupStore.abilities.destroy.isPermitted" :dangerous="true"
                @click="() => console.log(row.id, row.name)" :text="__('statamic-backup::backup.destroy.label')" />
        </template>
    </Listing>
</template>

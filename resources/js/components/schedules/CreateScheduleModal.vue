<template>
    <BaseModal title="Новый график" :width="560" @close="$emit('close')">
        <form id="create-schedule" class="create-schedule" @submit.prevent="submit">
            <FormField label="Название точки" :error="errors.name?.[0]">
                <input v-model.trim="form.name" class="input" type="text" placeholder="Например, ТЦ Метрополис" />
            </FormField>

            <FormField label="Адрес" :error="errors.address?.[0]">
                <input v-model.trim="form.address" class="input" type="text" placeholder="Улица, дом" />
            </FormField>

            <FormField label="Месяц" :error="errors.month?.[0]">
                <input v-model="form.month" class="input" type="month" />
            </FormField>

            <div class="create-schedule__employees">
                <div class="create-schedule__employees-head">
                    <span class="create-schedule__label">Сотрудники · {{ form.employee_ids.length }}</span>
                    <button class="create-schedule__select-all" type="button" @click="toggleAll">
                        {{ isAllSelected ? 'Снять всех' : 'Выбрать всех' }}
                    </button>
                </div>

                <p v-if="isLoadingEmployees" class="create-schedule__hint">Загружаем сотрудников…</p>
                <ul v-else class="create-schedule__list">
                    <li v-for="employee in employees" :key="employee.id">
                        <label class="create-schedule__employee">
                            <input v-model="form.employee_ids" class="create-schedule__checkbox" type="checkbox" :value="employee.id" />
                            <span>{{ employee.full_name }}</span>
                        </label>
                    </li>
                </ul>
                <span v-if="errors.employee_ids" class="create-schedule__error">{{ errors.employee_ids[0] }}</span>
            </div>
        </form>

        <template #footer>
            <BaseButton variant="secondary" @click="$emit('close')">Отмена</BaseButton>
            <BaseButton variant="accent" type="submit" form="create-schedule" :loading="isSubmitting">Создать график</BaseButton>
        </template>
    </BaseModal>
</template>

<script setup lang="ts">
import { computed, onMounted, reactive, ref } from 'vue';
import BaseButton from '@/components/ui/BaseButton.vue';
import BaseModal from '@/components/ui/BaseModal.vue';
import FormField from '@/components/ui/FormField.vue';
import { fetchEmployees } from '@/api/dictionaries';
import { createSchedule } from '@/api/schedules';
import { getErrorMessage, getValidationErrors } from '@/api/http';
import { useToast } from '@/composables/useToast';
import { currentMonthValue } from '@/utils/date';
import type { CreateSchedulePayload, Employee, Schedule, ValidationErrors } from '@/types';

const emit = defineEmits<{ close: []; created: [schedule: Schedule] }>();

const toast = useToast();

const employees = ref<Employee[]>([]);
const isLoadingEmployees = ref(true);
const isSubmitting = ref(false);
const errors = ref<ValidationErrors>({});

const form = reactive<CreateSchedulePayload>({
    name: '',
    address: '',
    month: currentMonthValue(),
    employee_ids: [],
});

const isAllSelected = computed(() => employees.value.length > 0 && form.employee_ids.length === employees.value.length);

function toggleAll(): void {
    form.employee_ids = isAllSelected.value ? [] : employees.value.map((employee) => employee.id);
}

async function submit(): Promise<void> {
    isSubmitting.value = true;
    errors.value = {};

    try {
        const schedule = await createSchedule(form);
        toast.success('График создан');
        emit('created', schedule);
    } catch (error) {
        errors.value = getValidationErrors(error);

        if (!Object.keys(errors.value).length) {
            toast.error(getErrorMessage(error));
        }
    } finally {
        isSubmitting.value = false;
    }
}

onMounted(async () => {
    try {
        employees.value = await fetchEmployees();
        form.employee_ids = employees.value.map((employee) => employee.id);
    } catch (error) {
        toast.error(getErrorMessage(error, 'Не удалось загрузить сотрудников'));
    } finally {
        isLoadingEmployees.value = false;
    }
});
</script>

<style lang="scss">
.create-schedule {
    display: flex;
    flex-direction: column;
    gap: 16px;

    &__employees-head {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 8px;
    }

    &__label {
        font-size: 12px;
        color: $color-text-secondary;
    }

    &__select-all {
        @include reset-button;
        font-size: 13px;
        font-weight: 500;
        color: $color-accent;

        &:hover {
            color: $color-accent-hover;
        }
    }

    &__list {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 2px 12px;
        max-height: 220px;
        padding: 8px;
        overflow-y: auto;
        border-radius: $radius-md;
        background: $color-surface-muted;
        @include scrollbar;
    }

    &__employee {
        display: flex;
        align-items: center;
        gap: 8px;
        padding: 6px;
        border-radius: $radius-xs;
        cursor: pointer;

        &:hover {
            background: $color-surface-hover;
        }
    }

    &__checkbox {
        width: 16px;
        height: 16px;
        margin: 0;
        accent-color: $color-accent;
    }

    &__hint {
        color: $color-text-secondary;
    }

    &__error {
        display: block;
        margin-top: 6px;
        font-size: 12px;
        color: $color-danger;
    }
}
</style>

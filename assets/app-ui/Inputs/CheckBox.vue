<script setup lang="ts">
type Props = {
	modelValue?: boolean;
	label?: string;
	error?: string;
	disabled?: boolean;
	name?: string;
};

const props = defineProps<Props>();
const emit = defineEmits<{
	'update:modelValue': [Props['modelValue']];
}>();

const inputId =
	props.name || `checkbox-${Math.random().toString(36).substring(2, 9)}`;

const handleUpdateModelValue = (event: Event) => {
	const target = event.target as HTMLInputElement;
	emit('update:modelValue', Boolean(target.checked));
};
</script>

<template>
    <div class="space-y-2">
        <div class="flex items-center">
            <input
                :id="inputId"
                :name="name"
                :checked="Boolean(modelValue)"
                type="checkbox"
                :disabled="disabled"
                :class="[
                    'h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded transition-colors',
                    error 
                        ? 'border-red-300 focus:ring-red-500' 
                        : 'border-gray-300',
                    disabled && 'bg-gray-50 cursor-not-allowed'
                ]"
                @change="handleUpdateModelValue"
            />
            <label v-if="label" :for="inputId" class="ml-2 block text-sm text-gray-700">
                {{ label }}
            </label>
        </div>
        <p v-if="error" class="text-sm text-red-600">{{ error }}</p>
    </div>
</template>
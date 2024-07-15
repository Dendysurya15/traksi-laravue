import { ref } from "vue";

// Define your reactive variables
export const sharedActionType = ref<string>("");
export const sharedActionData = ref<any>(null);

// Function to update shared state
export function updateSharedState(actionType: string, data: any) {
    sharedActionType.value = actionType;
    sharedActionData.value = data;
}

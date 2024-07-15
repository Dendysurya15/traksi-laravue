import { ref } from "vue";

// Define a reactive boolean value
export const isActionTriggered = ref(false);

// Function to set the action state to true or false
export function setActionTriggered(value: boolean) {
    isActionTriggered.value = value;
}

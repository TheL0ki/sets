// Availability Calendar JavaScript
document.addEventListener('DOMContentLoaded', function() {
    initializeAvailabilityCalendar();
});

function initializeAvailabilityCalendar() {
    // Handle checkbox changes to update visual state
    document.querySelectorAll('.availability-checkbox').forEach(checkbox => {
        checkbox.addEventListener('change', function() {
            updateVisualState(this);
        });
        
        // Also handle clicks on the visual div
        const visualDiv = checkbox.nextElementSibling;
        if (visualDiv) {
            visualDiv.addEventListener('click', function(e) {
                e.preventDefault();
                checkbox.checked = !checkbox.checked;
                updateVisualState(checkbox);
            });
        }
    });
}

function updateVisualState(checkbox) {
    const cell = checkbox.closest('td');
    const visualDiv = cell.querySelector('div');
    
    if (checkbox.checked) {
        visualDiv.className = 'w-full h-8 rounded transition-colors duration-200 bg-green-500 hover:bg-green-600 cursor-pointer';
    } else {
        visualDiv.className = 'w-full h-8 rounded transition-colors duration-200 bg-gray-100 dark:bg-gray-600 hover:bg-gray-200 dark:hover:bg-gray-500 cursor-pointer';
    }
}

// Select all checkboxes
function selectAll() {
    document.querySelectorAll('.availability-checkbox').forEach(checkbox => {
        checkbox.checked = true;
        updateVisualState(checkbox);
    });
}

// Clear all checkboxes
function clearAll() {
    document.querySelectorAll('.availability-checkbox').forEach(checkbox => {
        checkbox.checked = false;
        updateVisualState(checkbox);
    });
}

// Make functions globally available
window.selectAll = selectAll;
window.clearAll = clearAll; 
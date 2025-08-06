// Availability Calendar JavaScript
document.addEventListener('DOMContentLoaded', function() {
    initializeAvailabilityCalendar();
    initializeMobileNavigation();
});

function initializeAvailabilityCalendar() {
    // Handle checkbox changes to update visual state
    document.querySelectorAll('.availability-checkbox').forEach(checkbox => {
        checkbox.addEventListener('change', function() {
            updateVisualState(this);
            syncToMobile(this);
        });
        
        // Also handle clicks on the visual div
        const visualDiv = checkbox.nextElementSibling;
        if (visualDiv) {
            visualDiv.addEventListener('click', function(e) {
                e.preventDefault();
                checkbox.checked = !checkbox.checked;
                updateVisualState(checkbox);
                syncToMobile(checkbox);
            });
        }
    });

    // Handle mobile checkbox changes
    document.querySelectorAll('.mobile-availability-checkbox').forEach(checkbox => {
        checkbox.addEventListener('change', function() {
            updateMobileVisualState(this);
            syncToDesktop(this);
        });
        
        // Also handle clicks on the visual div
        const visualDiv = checkbox.nextElementSibling;
        if (visualDiv) {
            visualDiv.addEventListener('click', function(e) {
                e.preventDefault();
                checkbox.checked = !checkbox.checked;
                updateMobileVisualState(checkbox);
                syncToDesktop(checkbox);
            });
        }
    });
}

function syncToMobile(desktopCheckbox) {
    const timeSlot = desktopCheckbox.getAttribute('data-time');
    const day = desktopCheckbox.getAttribute('data-day');
    
    // Find corresponding mobile checkbox
    const mobileCheckbox = document.querySelector(`.mobile-availability-checkbox[data-time="${timeSlot}"][data-day="${day}"]`);
    if (mobileCheckbox) {
        mobileCheckbox.checked = desktopCheckbox.checked;
        mobileCheckbox.value = desktopCheckbox.value;
        updateMobileVisualState(mobileCheckbox);
    }
}

function syncToDesktop(mobileCheckbox) {
    const timeSlot = mobileCheckbox.getAttribute('data-time');
    const day = mobileCheckbox.getAttribute('data-day');
    
    // Find corresponding desktop checkbox
    const desktopCheckbox = document.querySelector(`.availability-checkbox[data-time="${timeSlot}"][data-day="${day}"]`);
    if (desktopCheckbox) {
        desktopCheckbox.checked = mobileCheckbox.checked;
        desktopCheckbox.value = mobileCheckbox.value;
        updateVisualState(desktopCheckbox);
    }
}

function initializeMobileNavigation() {
    const prevButton = document.getElementById('prev-day');
    const nextButton = document.getElementById('next-day');
    const currentDayDisplay = document.getElementById('current-day-display');
    
    if (!prevButton || !nextButton || !currentDayDisplay) return;

    let currentDate = new Date();
    let currentDayIndex = 0;
    
    // Get the week days data from the desktop table
    const desktopTable = document.querySelector('table');
    if (!desktopTable) return;
    
    const weekDays = [];
    const headerCells = desktopTable.querySelectorAll('thead th:not(:first-child)');
    headerCells.forEach((cell, index) => {
        const dayName = cell.querySelector('.font-semibold')?.textContent || '';
        const dayNumber = cell.querySelector('.text-sm')?.textContent || '';
        const isToday = cell.querySelector('.text-blue-600, .text-blue-400') !== null;
        weekDays.push({
            dayName: dayName,
            dayNumber: dayNumber,
            isToday: isToday,
            index: index
        });
        
        // Find the current day (today)
        if (isToday) {
            currentDayIndex = index;
        }
    });

    function formatDate(date) {
        const dayNames = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
        const monthNames = ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'];
        
        const dayName = dayNames[date.getDay()];
        const dayNumber = date.getDate();
        const monthName = monthNames[date.getMonth()];
        const year = date.getFullYear();
        
        return `<div class="text-center">
                    <div class="text-lg font-semibold">${dayName}</div>
                    <div class="text-sm text-gray-600 dark:text-gray-400">${monthName} ${dayNumber}, ${year}</div>
                </div>`;
    }

    function updateDayDisplay() {
        currentDayDisplay.innerHTML = formatDate(currentDate);
        
        // Hide all mobile day views
        document.querySelectorAll('.mobile-day-view').forEach(view => {
            view.classList.add('hidden');
        });
        
        // Show current day view (use the day of week index)
        const dayOfWeekIndex = currentDate.getDay();
        const currentDayView = document.querySelector(`.mobile-day-view[data-day-index="${dayOfWeekIndex}"]`);
        if (currentDayView) {
            currentDayView.classList.remove('hidden');
        }
        
        // Update mobile checkboxes to reflect current day
        document.querySelectorAll('.mobile-availability-checkbox').forEach((checkbox, index) => {
            checkbox.setAttribute('data-day-index', dayOfWeekIndex);
            
            // Update the checkbox value to reflect the current date
            const timeSlot = checkbox.getAttribute('data-time');
            const timeString = timeSlot.replace(':', '-');
            const dateString = currentDate.toISOString().split('T')[0];
            checkbox.value = `${dateString}-${timeString}`;
            
            // Get the corresponding desktop checkbox for this day and time
            const desktopRow = desktopTable.querySelector(`tbody tr:nth-child(${index + 1})`);
            if (desktopRow) {
                const desktopCell = desktopRow.querySelector(`td:nth-child(${dayOfWeekIndex + 2})`);
                const desktopCheckbox = desktopCell?.querySelector('.availability-checkbox');
                if (desktopCheckbox) {
                    checkbox.checked = desktopCheckbox.checked;
                    updateMobileVisualState(checkbox);
                }
            }
        });
    }

    prevButton.addEventListener('click', function() {
        currentDate.setDate(currentDate.getDate() - 1);
        updateDayDisplay();
    });

    nextButton.addEventListener('click', function() {
        currentDate.setDate(currentDate.getDate() + 1);
        updateDayDisplay();
    });

    // Initialize with current day
    updateDayDisplay();
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

function updateMobileVisualState(checkbox) {
    const visualDiv = checkbox.nextElementSibling;
    
    if (checkbox.checked) {
        visualDiv.className = 'w-12 h-8 rounded transition-colors duration-200 bg-green-500 hover:bg-green-600 cursor-pointer';
    } else {
        visualDiv.className = 'w-12 h-8 rounded transition-colors duration-200 bg-gray-100 dark:bg-gray-600 hover:bg-gray-200 dark:hover:bg-gray-500 cursor-pointer';
    }
}

// Select all checkboxes (both desktop and mobile)
function selectAll() {
    document.querySelectorAll('.availability-checkbox, .mobile-availability-checkbox').forEach(checkbox => {
        checkbox.checked = true;
        if (checkbox.classList.contains('mobile-availability-checkbox')) {
            updateMobileVisualState(checkbox);
        } else {
            updateVisualState(checkbox);
        }
    });
}

// Clear all checkboxes (both desktop and mobile)
function clearAll() {
    document.querySelectorAll('.availability-checkbox, .mobile-availability-checkbox').forEach(checkbox => {
        checkbox.checked = false;
        if (checkbox.classList.contains('mobile-availability-checkbox')) {
            updateMobileVisualState(checkbox);
        } else {
            updateVisualState(checkbox);
        }
    });
}

// Make functions globally available
window.selectAll = selectAll;
window.clearAll = clearAll; 
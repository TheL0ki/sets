<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Profile Settings') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <!-- Profile Summary -->
            @include('profile.partials.profile-summary')

            <!-- Personal Information Section -->
            @include('profile.partials.personal-info-section')

            <!-- Matchmaking Preferences Section -->
            @include('profile.partials.matchmaking-section')

            <!-- Notification Preferences Section -->
            @include('profile.partials.notification-section')

            <!-- Password Management Section -->
            <div class="p-4 sm:p-8 bg-white dark:bg-gray-800 shadow sm:rounded-lg">
                <div class="max-w-xl">
                    @include('profile.partials.update-password-form')
                </div>
            </div>

            <!-- Account Deletion Section -->
            <div class="p-4 sm:p-8 bg-white dark:bg-gray-800 shadow sm:rounded-lg">
                <div class="max-w-xl">
                    @include('profile.partials.delete-user-form')
                </div>
            </div>
        </div>
    </div>

    <!-- JavaScript for expandable sections and AJAX forms -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Initialize expandable sections
            initializeExpandableSections();
            
            // Initialize AJAX forms
            initializeAjaxForms();
        });

        function initializeExpandableSections() {
            const sections = document.querySelectorAll('.expandable-section');
            
            sections.forEach(section => {
                const header = section.querySelector('.section-header');
                const content = section.querySelector('.section-content');
                const icon = section.querySelector('.expand-icon');
                
                if (header && content) {
                    header.addEventListener('click', function() {
                        const isExpanded = section.classList.contains('expanded');
                        
                        if (isExpanded) {
                            section.classList.remove('expanded');
                            content.style.maxHeight = '0px';
                            icon.classList.remove('rotate-180');
                        } else {
                            section.classList.add('expanded');
                            content.style.maxHeight = content.scrollHeight + 'px';
                            icon.classList.add('rotate-180');
                        }
                    });
                }
            });
        }

        function initializeAjaxForms() {
            const forms = document.querySelectorAll('.ajax-form');
            
            forms.forEach(form => {
                form.addEventListener('submit', function(e) {
                    e.preventDefault();
                    
                    const formData = new FormData(form);
                    const submitButton = form.querySelector('button[type="submit"]');
                    const originalText = submitButton.textContent;
                    
                    // Show loading state
                    submitButton.disabled = true;
                    submitButton.textContent = 'Saving...';
                    
                    fetch(form.action, {
                        method: 'POST',
                        body: formData,
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                        }
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            showSuccessMessage(data.message || 'Settings saved successfully!');
                        } else {
                            showErrorMessage(data.message || 'An error occurred while saving.');
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        showErrorMessage('An error occurred while saving.');
                    })
                    .finally(() => {
                        // Reset button state
                        submitButton.disabled = false;
                        submitButton.textContent = originalText;
                    });
                });
            });
        }

        function showSuccessMessage(message) {
            const alert = document.createElement('div');
            alert.className = 'fixed top-4 right-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded z-50';
            alert.textContent = message;
            document.body.appendChild(alert);
            
            setTimeout(() => {
                alert.remove();
            }, 3000);
        }

        function showErrorMessage(message) {
            const alert = document.createElement('div');
            alert.className = 'fixed top-4 right-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded z-50';
            alert.textContent = message;
            document.body.appendChild(alert);
            
            setTimeout(() => {
                alert.remove();
            }, 3000);
        }
    </script>
</x-app-layout>

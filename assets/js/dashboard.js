// TPMS Student Dashboard Scripts

document.addEventListener('DOMContentLoaded', function() {
    // 1. Sidebar Toggle
    const sidebarToggle = document.getElementById('sidebar-toggle');
    const sidebar = document.querySelector('.sidebar');
    
    if (sidebarToggle && sidebar) {
        sidebarToggle.addEventListener('click', function() {
            sidebar.classList.toggle('collapsed');
            
            // Save state in localStorage
            const isCollapsed = sidebar.classList.contains('collapsed');
            localStorage.setItem('sidebar-collapsed', isCollapsed);
        });
        
        // Restore sidebar state
        const sidebarState = localStorage.getItem('sidebar-collapsed');
        if (sidebarState === 'true') {
            sidebar.classList.add('collapsed');
        }
    }

    // 2. Dark Mode Toggle
    const themeToggle = document.getElementById('theme-toggle');
    if (themeToggle) {
        themeToggle.addEventListener('click', function() {
            const currentTheme = document.documentElement.getAttribute('data-theme');
            const newTheme = currentTheme === 'dark' ? 'light' : 'dark';
            
            document.documentElement.setAttribute('data-theme', newTheme);
            localStorage.setItem('tpms-theme', newTheme);
            
            // Toggle icon
            const icon = themeToggle.querySelector('i');
            if (icon) {
                if (newTheme === 'dark') {
                    icon.className = 'fas fa-sun';
                } else {
                    icon.className = 'fas fa-moon';
                }
            }
            
            // Fire event for charts to redraw if needed
            window.dispatchEvent(new Event('theme-changed'));
        });
        
        // Restore theme
        const savedTheme = localStorage.getItem('tpms-theme') || 'light';
        document.documentElement.setAttribute('data-theme', savedTheme);
        const icon = themeToggle.querySelector('i');
        if (icon) {
            icon.className = savedTheme === 'dark' ? 'fas fa-sun' : 'fas fa-moon';
        }
    }

    // 3. Counter Animation
    const counters = document.querySelectorAll('.counter');
    if (counters.length > 0) {
        counters.forEach(counter => {
            const target = +counter.getAttribute('data-target');
            if (target === 0) {
                counter.innerText = '0';
                return;
            }
            const speed = 200; // lower is faster
            const updateCount = () => {
                const count = +counter.innerText;
                const inc = target / speed;
                
                if (count < target) {
                    counter.innerText = Math.ceil(count + inc);
                    setTimeout(updateCount, 1);
                } else {
                    counter.innerText = target;
                }
            };
            updateCount();
        });
    }

    // 4. Toast Notifications Setup
    window.showToast = function(message, type = 'success') {
        let container = document.querySelector('.tpms-toast-container');
        if (!container) {
            container = document.createElement('div');
            container.className = 'tpms-toast-container';
            document.body.appendChild(container);
        }
        
        const toast = document.createElement('div');
        toast.className = `tpms-toast show`;
        
        let iconClass = 'fa-check-circle text-success';
        if (type === 'danger') iconClass = 'fa-times-circle text-danger';
        if (type === 'warning') iconClass = 'fa-exclamation-triangle text-warning';
        if (type === 'info') iconClass = 'fa-info-circle text-primary';
        
        toast.innerHTML = `
            <i class="fas ${iconClass} me-3 fs-5"></i>
            <div class="flex-grow-1">${message}</div>
            <button type="button" class="btn-close ms-2" onclick="this.parentElement.remove()"></button>
        `;
        
        container.appendChild(toast);
        
        // Auto-remove after 4 seconds
        setTimeout(() => {
            toast.classList.remove('show');
            setTimeout(() => {
                toast.remove();
            }, 300);
        }, 4000);
    };

    // 5. AJAX Application Submissions
    window.applyJob = function(jobId, buttonElement) {
        if (buttonElement.disabled) return;
        buttonElement.disabled = true;
        buttonElement.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Applying...';
        
        const formData = new FormData();
        formData.append('job_id', jobId);
        
        fetch('api/apply_job.php', {
            method: 'POST',
            body: formData
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                showToast(data.message, 'success');
                buttonElement.className = 'btn btn-outline-secondary btn-sm disabled';
                buttonElement.innerHTML = 'Applied';
                buttonElement.disabled = true;
                
                // If on applications or jobs page, refresh table stats or structure after 1 sec
                setTimeout(() => {
                    location.reload();
                }, 1000);
            } else {
                showToast(data.message, 'danger');
                buttonElement.disabled = false;
                buttonElement.innerHTML = 'Apply Now';
            }
        })
        .catch(err => {
            console.error(err);
            showToast('Something went wrong. Please try again.', 'danger');
            buttonElement.disabled = false;
            buttonElement.innerHTML = 'Apply Now';
        });
    };

    // 6. AJAX Training Enrollments
    window.registerTraining = function(trainingId, buttonElement) {
        if (buttonElement.disabled) return;
        buttonElement.disabled = true;
        buttonElement.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Enrolling...';
        
        const formData = new FormData();
        formData.append('training_id', trainingId);
        
        fetch('api/register_training.php', {
            method: 'POST',
            body: formData
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                showToast(data.message, 'success');
                buttonElement.className = 'btn btn-success btn-sm disabled';
                buttonElement.innerHTML = '<i class="fas fa-check me-1"></i> Registered';
                buttonElement.disabled = true;
                
                setTimeout(() => {
                    location.reload();
                }, 1000);
            } else {
                showToast(data.message, 'danger');
                buttonElement.disabled = false;
                buttonElement.innerHTML = 'Register';
            }
        })
        .catch(err => {
            console.error(err);
            showToast('Enrollment failed. Try again.', 'danger');
            buttonElement.disabled = false;
            buttonElement.innerHTML = 'Register';
        });
    };

    // 7. Interactive Calendar Engine
    window.initMiniCalendar = function(events) {
        const date = new Date();
        let currentYear = date.getFullYear();
        let currentMonth = date.getMonth();
        
        const monthNames = ["January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December"];
        
        const render = () => {
            const calendarTitle = document.getElementById('calendar-title');
            if (calendarTitle) {
                calendarTitle.innerText = `${monthNames[currentMonth]} ${currentYear}`;
            }
            renderCalendarGrid(currentYear, currentMonth, events);
        };
        
        const prevBtn = document.getElementById('calendar-prev');
        const nextBtn = document.getElementById('calendar-next');
        
        if (prevBtn && nextBtn) {
            prevBtn.addEventListener('click', () => {
                currentMonth--;
                if (currentMonth < 0) {
                    currentMonth = 11;
                    currentYear--;
                }
                render();
            });
            
            nextBtn.addEventListener('click', () => {
                currentMonth++;
                if (currentMonth > 11) {
                    currentMonth = 0;
                    currentYear++;
                }
                render();
            });
        }
        
        render();
    };

    function renderCalendarGrid(year, month, events) {
        const calendarGrid = document.querySelector('.calendar-grid');
        if (!calendarGrid) return;
        
        // Remove old days except headers
        const dayNames = Array.from(calendarGrid.querySelectorAll('.calendar-day-name'));
        calendarGrid.innerHTML = '';
        dayNames.forEach(d => calendarGrid.appendChild(d));
        
        const firstDayIndex = new Date(year, month, 1).getDay();
        const lastDay = new Date(year, month + 1, 0).getDate();
        const prevLastDay = new Date(year, month, 0).getDate();
        
        // Previous month padding days
        for (let x = firstDayIndex; x > 0; x--) {
            const dayDiv = document.createElement('div');
            dayDiv.className = 'calendar-day other-month';
            dayDiv.innerText = prevLastDay - x + 1;
            calendarGrid.appendChild(dayDiv);
        }
        
        const today = new Date();
        
        // Current month days
        for (let i = 1; i <= lastDay; i++) {
            const dayDiv = document.createElement('div');
            dayDiv.className = 'calendar-day';
            dayDiv.innerText = i;
            
            const dateString = `${year}-${String(month + 1).padStart(2, '0')}-${String(i).padStart(2, '0')}`;
            
            // Today match
            if (today.getFullYear() === year && today.getMonth() === month && today.getDate() === i) {
                dayDiv.classList.add('today');
            }
            
            // Events match
            const dayEvents = events.filter(e => e.event_date === dateString);
            if (dayEvents.length > 0) {
                const dotsContainer = document.createElement('div');
                dotsContainer.className = 'calendar-day-dots';
                
                let tooltips = [];
                dayEvents.forEach(e => {
                    const dot = document.createElement('div');
                    dot.className = `calendar-dot calendar-dot-${e.type}`;
                    dotsContainer.appendChild(dot);
                    tooltips.push(e.title);
                });
                dayDiv.appendChild(dotsContainer);
                
                dayDiv.setAttribute('title', tooltips.join(' | '));
                dayDiv.setAttribute('data-bs-toggle', 'tooltip');
            }
            
            calendarGrid.appendChild(dayDiv);
        }
        
        // Initialize tooltips if Bootstrap is loaded
        if (window.bootstrap) {
            const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
            tooltipTriggerList.map(function (tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl);
            });
        }
    }

    // 8. Global search implementation (Client-side)
    const globalSearchInput = document.getElementById('global-search');
    if (globalSearchInput) {
        globalSearchInput.addEventListener('keyup', function(e) {
            const query = e.target.value.toLowerCase().trim();
            
            // Search implementation depending on active sections on current page
            // E.g., filters rows in jobs table, university cards, training table
            
            // Jobs table filtering
            const jobRows = document.querySelectorAll('.job-row');
            if (jobRows.length > 0) {
                jobRows.forEach(row => {
                    const company = row.getAttribute('data-company').toLowerCase();
                    const role = row.getAttribute('data-role').toLowerCase();
                    const location = row.getAttribute('data-location').toLowerCase();
                    if (company.includes(query) || role.includes(query) || location.includes(query)) {
                        row.style.display = '';
                    } else {
                        row.style.display = 'none';
                    }
                });
            }

            // Training rows filtering
            const trainingRows = document.querySelectorAll('.training-row');
            if (trainingRows.length > 0) {
                trainingRows.forEach(row => {
                    const name = row.getAttribute('data-name').toLowerCase();
                    const trainer = row.getAttribute('data-trainer').toLowerCase();
                    if (name.includes(query) || trainer.includes(query)) {
                        row.style.display = '';
                    } else {
                        row.style.display = 'none';
                    }
                });
            }

            // University card filtering
            const universityCards = document.querySelectorAll('.university-card');
            if (universityCards.length > 0) {
                universityCards.forEach(card => {
                    const university = card.getAttribute('data-university').toLowerCase();
                    const course = card.getAttribute('data-course').toLowerCase();
                    const country = card.getAttribute('data-country').toLowerCase();
                    if (university.includes(query) || course.includes(query) || country.includes(query)) {
                        card.style.display = '';
                    } else {
                        card.style.display = 'none';
                    }
                });
            }
        });
    }

    // 9. Export applications history helper
    const exportPdfBtn = document.getElementById('export-pdf-btn');
    if (exportPdfBtn) {
        exportPdfBtn.addEventListener('click', function() {
            // Trigger browser print interface formatted by print CSS queries
            window.print();
        });
    }
});

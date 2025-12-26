document.addEventListener('DOMContentLoaded', () => {
    const mainContent = document.getElementById('main-content');
    
    // Navigation Event Listeners
    document.getElementById('nav-home').addEventListener('click', (e) => { e.preventDefault(); loadDashboard(); });
    document.getElementById('nav-new-contact').addEventListener('click', (e) => { e.preventDefault(); loadNewContactForm(); });
    document.getElementById('nav-users').addEventListener('click', (e) => { e.preventDefault(); loadUsers(); });

    // Initial Load
    loadDashboard();

    // --- DASHBOARD FUNCTIONS ---
    function loadDashboard(filter = 'All') {
        fetch(`api/contacts.php?filter=${filter}`)
            .then(response => response.text())
            .then(html => {
                mainContent.innerHTML = html;
                attachDashboardEvents();
            });
    }

    function attachDashboardEvents() {
        // Filter buttons
        document.querySelectorAll('.filter-btn').forEach(btn => {
            btn.addEventListener('click', (e) => {
                e.preventDefault();
                loadDashboard(e.target.dataset.filter);
            });
        });
        
        // View Contact buttons
        document.querySelectorAll('.view-contact-btn').forEach(btn => {
            btn.addEventListener('click', (e) => {
                e.preventDefault();
                loadContactDetails(e.target.dataset.id);
            });
        });

        // Add Contact Button (Top right)
        const addBtn = document.getElementById('dashboard-add-btn');
        if(addBtn) addBtn.addEventListener('click', loadNewContactForm);
    }

    // --- CONTACT DETAILS ---
    function loadContactDetails(id) {
        fetch(`api/contacts.php?id=${id}`)
            .then(response => response.text())
            .then(html => {
                mainContent.innerHTML = html;
                attachContactDetailEvents(id);
                loadNotes(id);
            });
    }

    function attachContactDetailEvents(id) {
        const assignBtn = document.getElementById('assign-me-btn');
        const switchBtn = document.getElementById('switch-type-btn');
        const noteBtn = document.getElementById('add-note-btn');

        if(assignBtn) {
            assignBtn.addEventListener('click', () => {
                fetch('api/contacts.php', {
                    method: 'POST',
                    headers: {'Content-Type': 'application/json'},
                    body: JSON.stringify({ action: 'assign_to_me', contact_id: id })
                }).then(() => loadContactDetails(id));
            });
        }

        if(switchBtn) {
            switchBtn.addEventListener('click', () => {
                fetch('api/contacts.php', {
                    method: 'POST',
                    headers: {'Content-Type': 'application/json'},
                    body: JSON.stringify({ action: 'switch_type', contact_id: id })
                }).then(() => loadContactDetails(id));
            });
        }
        
        if(noteBtn) {
            noteBtn.addEventListener('click', (e) => {
                e.preventDefault();
                const comment = document.getElementById('note-comment').value;
                if(!comment) return;
                
                const formData = new FormData();
                formData.append('contact_id', id);
                formData.append('comment', comment);

                fetch('api/notes.php', { method: 'POST', body: formData })
                    .then(res => res.json())
                    .then(data => {
                        if(data.success) {
                            loadNotes(id); // Reload notes
                            document.getElementById('note-comment').value = ''; // Clear box
                            alert("Note added!");
                        }
                    });
            });
        }
    }

    function loadNotes(contactId) {
        fetch(`api/notes.php?contact_id=${contactId}`)
            .then(res => res.text())
            .then(html => {
                document.getElementById('notes-area').innerHTML = html;
            });
    }

    // --- FORMS (NEW USER / NEW CONTACT) ---
    
    function loadNewContactForm() {
        fetch('api/contacts.php?context=create_form')
            .then(res => res.text())
            .then(html => {
                mainContent.innerHTML = html;
                document.getElementById('new-contact-form').addEventListener('submit', handleFormSubmit);
            });
    }

    function loadUsers() {
        fetch('api/users.php?context=list')
            .then(res => res.text())
            .then(html => {
                mainContent.innerHTML = html;
                const addUserBtn = document.getElementById('add-user-btn'); // Link to form
                if(addUserBtn) addUserBtn.addEventListener('click', loadNewUserForm);
            });
    }

    function loadNewUserForm() {
        fetch('api/users.php?context=create_form')
            .then(res => res.text())
            .then(html => {
                mainContent.innerHTML = html;
                document.getElementById('new-user-form').addEventListener('submit', handleFormSubmit);
            });
    }

    function handleFormSubmit(e) {
        e.preventDefault();
        const form = e.target;
        const formData = new FormData(form);
        const url = form.action;

        fetch(url, { method: 'POST', body: formData })
            .then(res => res.json())
            .then(data => {
                if(data.success) {
                    alert('Saved successfully!');
                    form.reset();
                } else {
                    alert('Error: ' + data.message);
                }
            })
            .catch(err => alert('An error occurred.'));
    }
});
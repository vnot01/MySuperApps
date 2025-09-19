// RVM Cards Management

function updateRvmCards(rvms) {
    const container = document.getElementById('rvm-cards-container');
    if (!container) return;
    
    container.innerHTML = '';
    
    // Calculate pagination
    const totalItems = rvms.length;
    const totalPages = Math.ceil(totalItems / itemsPerPage);
    const startIndex = (currentPage - 1) * itemsPerPage;
    const endIndex = Math.min(startIndex + itemsPerPage, totalItems);
    const currentPageItems = rvms.slice(startIndex, endIndex);
    
    // Update pagination info
    updatePaginationInfo(startIndex + 1, endIndex, totalItems);
    updatePaginationControls(currentPage, totalPages);
    
    // Render current page items
    currentPageItems.forEach((rvm, index) => {
        const card = createRvmCard(rvm);
        card.style.animation = `fadeInUp 0.5s ${index * 0.05}s ease-out forwards`;
        container.appendChild(card);
    });
}

function createRvmCard(rvm) {
    const col = document.createElement('div');
    col.className = 'col-md-6 col-lg-4';
    col.style.opacity = 0; // for animation
    
    const statusInfo = {
        active: { text: 'text-success', icon: 'fas fa-check-circle' },
        inactive: { text: 'text-secondary', icon: 'fas fa-pause-circle' },
        maintenance: { text: 'text-warning', icon: 'fas fa-tools' },
        full: { text: 'text-danger', icon: 'fas fa-exclamation-triangle' },
        error: { text: 'text-danger', icon: 'fas fa-times-circle' },
        unknown: { text: 'text-muted', icon: 'fas fa-question-circle' }
    }[rvm.calculated_status] || { text: 'text-muted', icon: 'fas fa-question-circle' };
    
    col.innerHTML = `
        <div class="card rvm-card ${rvm.calculated_status} border-0 shadow-sm h-100" data-rvm-id="${rvm.id}">
            <div class="card-body p-4 d-flex flex-column">
                <div class="d-flex justify-content-between align-items-start mb-3">
                    <div class="d-flex align-items-center">
                        <div class="status-dot ${rvm.calculated_status} me-3"></div>
                        <div>
                            <h6 class="card-title mb-1 fw-bold">${rvm.name}</h6>
                            <small class="text-muted">${rvm.location}</small>
                        </div>
                    </div>
                    <div class="dropdown">
                        <button class="btn btn-sm btn-text-secondary btn-icon rounded-pill" type="button" data-bs-toggle="dropdown">
                            <i class="fas fa-ellipsis-v"></i>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><a class="dropdown-item" href="#" onclick="openRemoteAccess(${rvm.id}, '${rvm.name}')"><i class="fas fa-desktop me-2"></i>Remote Access</a></li>
                            <li><a class="dropdown-item" href="#" onclick="openStatusModal(${rvm.id}, '${rvm.name}')"><i class="fas fa-edit me-2"></i>Update Status</a></li>
                        </ul>
                    </div>
                </div>
                <div class="mt-auto">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="text-capitalize ${statusInfo.text}"><i class="${statusInfo.icon} me-1"></i>${rvm.calculated_status}</span>
                        <span class="fw-bold">${rvm.capacity}% Full</span>
                    </div>
                    <div class="progress" style="height: 6px;">
                        <div class="progress-bar ${rvm.capacity > 80 ? 'bg-danger' : rvm.capacity > 60 ? 'bg-warning' : 'bg-success'}" style="width: ${rvm.capacity}%"></div>
                    </div>
                    <small class="text-muted d-block mt-2"><i class="fas fa-clock me-1"></i>${rvm.last_seen}</small>
                </div>
            </div>
        </div>`;
    return col;
}

// --- Pagination Functions ---

function updatePaginationInfo(start, end, total) {
    const infoElement = document.getElementById('pagination-info');
    if (infoElement) {
        infoElement.textContent = `Showing ${start}-${end} of ${total} RVMs`;
    }
}

function updatePaginationControls(currentPage, totalPages) {
    // Update page buttons
    for (let i = 1; i <= totalPages; i++) {
        const pageElement = document.getElementById(`page-${i}`);
        if (pageElement) {
            if (i === currentPage) {
                pageElement.classList.add('active');
            } else {
                pageElement.classList.remove('active');
            }
        }
    }
    
    // Update prev/next buttons
    const prevElement = document.getElementById('prev-page');
    const nextElement = document.getElementById('next-page');
    
    if (prevElement) {
        if (currentPage === 1) {
            prevElement.classList.add('disabled');
        } else {
            prevElement.classList.remove('disabled');
        }
    }
    
    if (nextElement) {
        if (currentPage === totalPages) {
            nextElement.classList.add('disabled');
        } else {
            nextElement.classList.remove('disabled');
        }
    }
}

function goToPage(page) {
    if (page < 1) return;
    
    const totalItems = monitoringData?.rvms?.length || 0;
    const totalPages = Math.ceil(totalItems / itemsPerPage);
    
    if (page > totalPages) return;
    
    currentPage = page;
    
    // Re-render cards with new page
    if (monitoringData?.rvms) {
        updateRvmCards(monitoringData.rvms);
    }
}

function changePage(direction) {
    const totalItems = monitoringData?.rvms?.length || 0;
    const totalPages = Math.ceil(totalItems / itemsPerPage);
    const newPage = currentPage + direction;
    
    if (newPage >= 1 && newPage <= totalPages) {
        goToPage(newPage);
    }
}

// --- RVM Action Functions ---

function openRemoteAccess(rvmId, rvmName) {
    // Show remote access modal or redirect
    alert(`🔧 Remote Access for ${rvmName} (ID: ${rvmId})\n\nThis feature will be implemented soon!`);
}

function openStatusModal(rvmId, rvmName) {
    // Create modal HTML with dropdown
    const modalHtml = `
        <div class="modal fade" id="updateStatusModal" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">
                            <i class="fas fa-edit me-2"></i>
                            Update Status - ${rvmName}
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Select New Status:</label>
                            <select class="form-select" id="statusSelect">
                                <option value="">Choose status...</option>
                                <option value="active">
                                    <i class="fas fa-check-circle text-success"></i> Active
                                </option>
                                <option value="inactive">
                                    <i class="fas fa-pause-circle text-secondary"></i> Inactive
                                </option>
                                <option value="maintenance">
                                    <i class="fas fa-tools text-warning"></i> Maintenance
                                </option>
                                <option value="error">
                                    <i class="fas fa-exclamation-triangle text-danger"></i> Error
                                </option>
                                <option value="full">
                                    <i class="fas fa-exclamation-circle text-danger"></i> Full
                                </option>
                            </select>
                        </div>
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle me-2"></i>
                            <strong>RVM ID:</strong> ${rvmId}<br>
                            <strong>Current Status:</strong> <span id="currentStatus">Loading...</span>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="button" class="btn btn-primary" id="updateStatusBtn" disabled>
                            <i class="fas fa-save me-1"></i>Update Status
                        </button>
                    </div>
                </div>
            </div>
        </div>
    `;
    
    // Remove existing modal if any
    const existingModal = document.getElementById('updateStatusModal');
    if (existingModal) {
        existingModal.remove();
    }
    
    // Add modal to body
    document.body.insertAdjacentHTML('beforeend', modalHtml);
    
    // Show modal
    const modal = new bootstrap.Modal(document.getElementById('updateStatusModal'));
    modal.show();
    
    // Get current status and populate dropdown
    fetch(`/admin/rvm/${rvmId}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const currentStatus = data.data.status;
                document.getElementById('currentStatus').textContent = currentStatus.charAt(0).toUpperCase() + currentStatus.slice(1);
                
                // Set current status as selected
                const statusSelect = document.getElementById('statusSelect');
                statusSelect.value = currentStatus;
            }
        })
        .catch(error => {
            console.error('Error fetching current status:', error);
            document.getElementById('currentStatus').textContent = 'Unknown';
        });
    
    // Handle dropdown change
    document.getElementById('statusSelect').addEventListener('change', function() {
        const updateBtn = document.getElementById('updateStatusBtn');
        updateBtn.disabled = !this.value;
    });
    
    // Handle update button click
    document.getElementById('updateStatusBtn').addEventListener('click', function() {
        const newStatus = document.getElementById('statusSelect').value;
        
        if (!newStatus) {
            alert('Please select a status');
            return;
        }
        
        // Disable button and show loading
        this.disabled = true;
        this.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>Updating...';
        
        // Update status via API
        fetch(`/admin/rvm/${rvmId}`, {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({ status: newStatus })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Show success message
                alert(`✅ Status updated successfully!\n\n${rvmName} is now ${newStatus.charAt(0).toUpperCase() + newStatus.slice(1)}`);
                
                // Close modal
                modal.hide();
                
                // Refresh the dashboard
                location.reload();
            } else {
                alert(`❌ Error updating status: ${data.message}`);
                // Re-enable button
                this.disabled = false;
                this.innerHTML = '<i class="fas fa-save me-1"></i>Update Status';
            }
        })
        .catch(error => {
            alert(`❌ Error: ${error.message}`);
            // Re-enable button
            this.disabled = false;
            this.innerHTML = '<i class="fas fa-save me-1"></i>Update Status';
        });
    });
    
    // Clean up modal after hiding
    document.getElementById('updateStatusModal').addEventListener('hidden.bs.modal', function() {
        this.remove();
    });
}

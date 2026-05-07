<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h6 class="fw-bold mb-0">Client Details</h6>
        <small class="text-muted">View client information and associated invoices</small>
    </div>
    <div>
        <a href="/bytez-erp/clients/edit/<?= $client['id'] ?>" class="btn btn-outline-primary btn-sm me-2">
            <i class="fas fa-edit me-1"></i> Edit Client
        </a>
        <a href="/bytez-erp/clients/index" class="btn btn-secondary btn-sm">
            <i class="fas fa-arrow-left me-1"></i> Back to Clients
        </a>
    </div>
</div>

<div class="row">
    <!-- Client Information -->
    <div class="col-lg-4">
        <div class="card">
            <div class="card-header">
                <h6 class="mb-0">
                    <div class="d-flex align-items-center gap-2">
                        <div
                            style="width:35px;height:35px;background:#4f46e5;border-radius:8px;display:flex;align-items:center;justify-content:center;color:white;font-weight:700;font-size:0.8rem;">
                            <?= strtoupper(substr($client['company_name'], 0, 2)) ?>
                        </div>
                        <span>Client Information</span>
                    </div>
                </h6>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <label class="form-label fw-bold">Company Name</label>
                    <p class="mb-0"><?= htmlspecialchars($client['company_name']) ?></p>
                </div>

                <?php if (!empty($client['industry'])): ?>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Industry</label>
                        <p class="mb-0"><?= htmlspecialchars($client['industry']) ?></p>
                    </div>
                <?php endif; ?>

                <?php if (!empty($client['contact_name'])): ?>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Contact Person</label>
                        <p class="mb-0"><?= htmlspecialchars($client['contact_name']) ?></p>
                    </div>
                <?php endif; ?>

                <?php if (!empty($client['email'])): ?>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Email</label>
                        <p class="mb-0">
                            <a href="mailto:<?= htmlspecialchars($client['email']) ?>" class="text-decoration-none">
                                <?= htmlspecialchars($client['email']) ?>
                            </a>
                        </p>
                    </div>
                <?php endif; ?>

                <?php if (!empty($client['phone'])): ?>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Phone</label>
                        <p class="mb-0">
                            <a href="tel:<?= htmlspecialchars($client['phone']) ?>" class="text-decoration-none">
                                <?= htmlspecialchars($client['phone']) ?>
                            </a>
                        </p>
                    </div>
                <?php endif; ?>

                <?php if (!empty($client['address'])): ?>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Address</label>
                        <p class="mb-0"><?= nl2br(htmlspecialchars($client['address'])) ?></p>
                    </div>
                <?php endif; ?>

                <div class="mb-3">
                    <label class="form-label fw-bold">Status</label>
                    <p class="mb-0">
                        <span class="badge bg-<?= $client['status'] === 'active' ? 'success' : 'danger' ?>">
                            <?= ucfirst($client['status']) ?>
                        </span>
                    </p>
                </div>

                <div class="mb-0">
                    <label class="form-label fw-bold">Created</label>
                    <p class="mb-0 text-muted small">
                        <?= date('M j, Y', strtotime($client['created_at'])) ?>
                        <?php if (!empty($client['created_by_name'])): ?>
                            by <?= htmlspecialchars($client['created_by_name']) ?>
                        <?php endif; ?>
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- Invoices Section -->
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <div>
                    <h6 class="mb-0">Invoices (<span id="invoiceCount"><?= count($invoices) ?></span>)</h6>
                    <small class="text-muted" id="lastUpdated">Last updated:
                        <?= date('H:i:s') ?> | Auto-refresh: 30s</small>
                </div>
                <div class="d-flex gap-2 align-items-center">
                    <button type="button" class="btn btn-outline-secondary btn-sm" id="manualRefreshBtn"
                        title="Refresh Now">
                        <i class="fas fa-sync-alt"></i>
                    </button>
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" id="autoRefreshToggle" checked>
                        <label class="form-check-label small" for="autoRefreshToggle">
                            Auto-refresh
                        </label>
                    </div>
                    <a href="http://localhost/laravel-invoice-billing-system/public/invoices/create?client_id=<?= $client['id'] ?>"
                        target="_blank" class="btn btn-primary btn-sm">
                        <i class="fas fa-plus me-1"></i> Create Invoice
                    </a>
                </div>
            </div>
            <div class="card-body p-0">
                <div id="invoicesContainer">
                    <?php if (empty($invoices)): ?>
                        <div class="text-center py-5">
                            <i class="fas fa-file-invoice-dollar fa-3x text-muted mb-3"></i>
                            <h6 class="text-muted">No invoices found</h6>
                            <p class="text-muted small mb-3">This client doesn't have any invoices yet.</p>
                            <a href="http://localhost/laravel-invoice-billing-system/public/invoices/create?client_id=<?= $client['id'] ?>"
                                target="_blank" class="btn btn-outline-primary btn-sm">
                                <i class="fas fa-plus me-1"></i> Create First Invoice
                            </a>
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Invoice #</th>
                                        <th>Date</th>
                                        <th>Due Date</th>
                                        <th>Status</th>
                                        <th>Amount</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody id="invoicesTableBody">
                                    <?php foreach ($invoices as $invoice): ?>
                                        <tr>
                                            <td>
                                                <strong><?= htmlspecialchars($invoice['invoice_number']) ?></strong>
                                            </td>
                                            <td>
                                                <?= date('M j, Y', strtotime($invoice['invoice_date'])) ?>
                                            </td>
                                            <td>
                                                <?php if ($invoice['due_date']): ?>
                                                    <?= date('M j, Y', strtotime($invoice['due_date'])) ?>
                                                <?php else: ?>
                                                    <span class="text-muted">-</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <span class="badge bg-<?=
                                                    $invoice['status'] === 'paid' ? 'success' :
                                                    ($invoice['status'] === 'pending' ? 'warning' : 'danger')
                                                    ?>">
                                                    <?= ucfirst($invoice['status']) ?>
                                                </span>
                                            </td>
                                            <td>
                                                <strong>
                                                    <?php
                                                    $currency_symbols = [
                                                        'USD' => '$',
                                                        'PHP' => '₱',
                                                        'PKR' => 'Rs.',
                                                        'EUR' => '€',
                                                        'GBP' => '£',
                                                        'AED' => 'د.إ'
                                                    ];
                                                    $symbol = $currency_symbols[$invoice['currency']] ?? $invoice['currency'];
                                                    echo $symbol . number_format($invoice['total'], 2);
                                                    ?>
                                                </strong>
                                            </td>
                                            <td>
                                                <a href="http://localhost/laravel-invoice-billing-system/public/invoices/<?= $invoice['id'] ?>"
                                                    target="_blank" class="btn btn-outline-primary btn-sm" title="View Invoice">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    let autoRefreshInterval;
    let isAutoRefreshEnabled = true;

    function fetchInvoices(isManualRefresh = false) {
        const clientId = <?= $client['id'] ?>;
        const apiUrl = `http://localhost/laravel-invoice-billing-system/public/api/clients/${clientId}/invoices`;
        const apiToken = 'be9408a6f871f2afa2b741435728fd2cb815e65aa972e8414f2077e24bc50d23';

        // Show loading indicator only for manual refreshes
        const container = document.getElementById('invoicesContainer');
        let originalContent = '';

        if (isManualRefresh) {
            originalContent = container.innerHTML;
            container.innerHTML = `
                <div class="text-center py-5">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <p class="text-muted mt-2">Refreshing invoices...</p>
                </div>
            `;
        }

        return fetch(apiUrl, {
            method: 'GET',
            headers: {
                'Authorization': `Bearer ${apiToken}`,
                'Accept': 'application/json'
            }
        })
            .then(response => response.json())
            .then(data => {
                if (data.success && data.data) {
                    updateInvoicesDisplay(data.data);
                    updateLastUpdated();
                } else {
                    if (isManualRefresh) {
                        // Restore original content if API fails
                        container.innerHTML = originalContent;
                    }
                    console.error('Failed to fetch invoices:', data);
                }
            })
            .catch(error => {
                if (isManualRefresh) {
                    // Restore original content on error
                    container.innerHTML = originalContent;
                }
                const container = document.getElementById('invoicesContainer');
                const countElement = document.getElementById('invoiceCount');

                // Update count
                countElement.textContent = invoices.length;

                if (invoices.length === 0) {
                    container.innerHTML = `
            <div class="text-center py-5">
                <i class="fas fa-file-invoice-dollar fa-3x text-muted mb-3"></i>
                <h6 class="text-muted">No invoices found</h6>
                <p class="text-muted small mb-3">This client doesn't have any invoices yet.</p>
                <a href="http://localhost/laravel-invoice-billing-system/public/invoices/create?client_id=<?= $client['id'] ?>"
                    target="_blank" class="btn btn-outline-primary btn-sm">
                    <i class="fas fa-plus me-1"></i> Create First Invoice
                </a>
            </div>
        `;
                } else {
                    const currencySymbols = {
                        'USD': '$',
                        'PHP': '₱',
                        'PKR': 'Rs.',
                        'EUR': '€',
                        'GBP': '£',
                        'AED': 'د.إ'
                    };

                    const tableRows = invoices.map(invoice => {
                        const symbol = currencySymbols[invoice.currency] || invoice.currency;
                        const statusClass = invoice.status === 'paid' ? 'success' :
                            invoice.status === 'pending' ? 'warning' : 'danger';

                        return `
                <tr>
                    <td><strong>${invoice.invoice_number}</strong></td>
                    <td>${new Date(invoice.invoice_date).toLocaleDateString('en-US', {
                            year: 'numeric',
                            month: 'short',
                            day: 'numeric'
                        })}</td>
                    <td>${invoice.due_date ? new Date(invoice.due_date).toLocaleDateString('en-US', {
                            year: 'numeric',
                            month: 'short',
                            day: 'numeric'
                        }) : '<span class="text-muted">-</span>'}</td>
                    <td>
                        <span class="badge bg-${statusClass}">${invoice.status.charAt(0).toUpperCase() + invoice.status.slice(1)}</span>
                    </td>
                    <td><strong>${symbol}${parseFloat(invoice.total).toFixed(2)}</strong></td>
                    <td>
                        <a href="http://localhost/laravel-invoice-billing-system/public/invoices/${invoice.id}"
                            target="_blank" class="btn btn-outline-primary btn-sm" title="View Invoice">
                            <i class="fas fa-eye"></i>
                        </a>
                    </td>
                </tr>
            `;
                    }).join('');

                    container.innerHTML = `
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Invoice #</th>
                            <th>Date</th>
                            <th>Due Date</th>
                            <th>Status</th>
                            <th>Amount</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>${tableRows}</tbody>
                </table>
            </div>
        `;
                }
            }

function updateRefreshStatus() {
                    const statusElement = document.getElementById('lastUpdated');
                    const timeString = new Date().toLocaleTimeString('en-US', {
                        hour12: false,
                        hour: '2-digit',
                        minute: '2-digit',
                        second: '2-digit'
                    });

                    if (isAutoRefreshEnabled) {
                        statusElement.innerHTML = `Last updated: ${timeString} | Auto-refresh: 30s`;
                    } else {
                        statusElement.innerHTML = `Last updated: ${timeString} | Auto-refresh: OFF`;
                    }
                }

    function startAutoRefresh() {
                    if (autoRefreshInterval) {
                        clearInterval(autoRefreshInterval);
                    }
                    autoRefreshInterval = setInterval(() => {
                        if (isAutoRefreshEnabled) {
                            fetchInvoices();
                        }
                    }, 30000); // Refresh every 30 seconds
                }

    function stopAutoRefresh() {
                    if (autoRefreshInterval) {
                        clearInterval(autoRefreshInterval);
                        autoRefreshInterval = null;
                    }
                }

    // Toggle auto-refresh
    document.getElementById('autoRefreshToggle').addEventListener('change', function () {
                    isAutoRefreshEnabled = this.checked;
                    updateRefreshStatus();
                    if (isAutoRefreshEnabled) {
                        startAutoRefresh();
                    } else {
                        stopAutoRefresh();
                    }
                });

        // Manual refresh button
        document.getElementById('manualRefreshBtn').addEventListener('click', function () {
            const icon = this.querySelector('i');
            icon.classList.add('fa-spin');
            this.disabled = true;

            fetchInvoices(true).finally(() => {
                icon.classList.remove('fa-spin');
                this.disabled = false;
            });
        });

        // Initialize auto-refresh on page load
        document.addEventListener('DOMContentLoaded', function () {
            startAutoRefresh();
        });
</script>
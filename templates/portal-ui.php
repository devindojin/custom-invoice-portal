<?php 
# Main Standalone Dashboard (The "Shell")
# The main staff dashboard
// Direct Link: Staff accesses yoursite.com/staff-portal?key=staff_secure_2026.
// Order List: Uses WooCommerce wc_get_orders to show all placed orders.

// Persistence: When staff fills the modal, data is saved as Order Meta.

// Edit/History: Because the data is saved to the order, clicking "Manage"
//  on an old order will pre-fill the form with the previously entered details.
// get_header();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Staff Invoice Portal</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <style>
        body { background: #f8f9fa; }
        .sidebar { height: 100vh; width: 240px; position: fixed; background: #212529; color: #fff; padding: 20px; }
        .content { margin-left: 260px; padding: 40px; }
        .order-card { cursor: pointer; transition: 0.2s; }
        .order-card:hover { background: #e9ecef; }
        #footer { display: none; }
    </style>
    <?php wp_head(); ?>
</head>
<body>

<div class="sidebar">
    <h3>Staff Portal</h3>
    <hr>
    <button class="btn btn-dark w-100 mb-2 text-start" id="btn-all-orders">📋 All Orders</button>
    <button class="btn btn-dark w-100 text-start" id="btn-history">✅ Invoice History</button>
</div>

<div class="content">

    <div class="card shadow-sm border-0 mb-4">
        <div class="card-header bg-white">
            <h6 class="mb-0">Filter Invoices</h6>
        </div>
        <div class="card-body justify-content-between">
            <div class="mb-3">
                <label for="searchInput" class="form-label small fw-bold">Filter by Order No:</label>
                <input type="text" id="searchInput" class="form-control" placeholder="e.g. 12345">
            </div>

            <div class="mb-3">
                <label for="dateInput" class="form-label small fw-bold">Filter by Date:</label>
                <input type="date" id="dateInput" class="form-control">
            </div>

            <div class="d-grid gap-2">
                <button class="btn btn-primary" onclick="loadOrders(false)">
                    <i class="dashicons dashicons-filter"></i> Apply Filter
                </button>
                <button class="btn btn-outline-secondary btn-sm" id="sip-clear-filters">
                    <i class="dashicons dashicons-no"></i> Clear Filters
                </button>
            </div>
        </div>
    </div>

    <table class="table bg-white shadow-sm rounded" id="sip-orders-table">
        <thead class="table-light">
         <tr>
            <th>Order ID</th> 
            <th>Date</th>
            <th>Total</th> 
            <th>Customer</th> 
            <th>Action</th> 
          </tr>
        </thead>
        
        <tbody id="orderList"></tbody>
    </table>
</div>

<div class="modal fade" id="invoiceModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg"> <div class="modal-content shadow">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title">Process Invoice #<span id="m_oid"></span></h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-bold">Customer Name</label>
                        <input type="text" id="c_name" class="form-control" placeholder="Enter Full Name">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-bold">Phone Number</label>
                        <input type="text" id="c_phone" class="form-control" placeholder="e.g. +971...">
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-bold">TRN (Tax Registration Number)</label>
                    <input type="text" id="c_trn" class="form-control" placeholder="15-digit TRN (Optional)">
                </div>

                <div class="mb-3">
                    <label class="form-label fw-bold">Billing Address</label>
                    <textarea id="c_address" class="form-control" rows="3" placeholder="Enter full delivery or billing address..."></textarea>
                </div>
            </div>

            <div class="modal-footer bg-light">
                
                <button type="button" class="btn btn-outline-danger me-auto" onclick="resetModalFields()">
                    <i class="dashicons dashicons-trash"></i> Reset
                </button>

                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-success px-4" onclick="saveAndGenerate(event)">
                    Save & Generate PDF
                </button>
            </div>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<?php get_footer(); ?>
</body>
</html>
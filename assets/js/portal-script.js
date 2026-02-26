(function($) {
    "use strict";

    // Global variables to hold state
    let ordersDataTable = null;
    let loadedOrders = [];
    window.currentOrderId = null;

    /**
     * Load Orders via AJAX
     * @param {boolean} isHistory - If true, filters for processed orders only
     */
    window.loadOrders = function(isHistory = false) {
        // 1. DESTROY: Remove the DataTable "brain" from the table
        if ($.fn.DataTable.isDataTable('#sip-orders-table')) {
            $('#sip-orders-table').DataTable().destroy();
        }

        // 2. CLEAN: Empty the table so old columns don't cause count errors
        $('#orderList').empty().html('<tr><td colspan="5" class="text-center">Loading...</td></tr>');

        

        $.post(sip_settings.ajax_url, {
            action: 'sip_load_orders',
            search: $('#searchInput').val(),
            date: $('#dateInput').val(),
            history: isHistory
        }, function(res) {
            if (res.success) {
                loadedOrders = res.data; // Store for openInvoiceByIndex
                let html = '';

                if (res.data.length === 0) {
                    html = '<tr><td colspan="5" class="text-center">No orders found.</td></tr>';
                } else {

                  
                    res.data.forEach((o, index) => {
                        // Extract name safely from the meta object flattened in PHP
                        const custName = o.meta && o.meta.name ? o.meta.name : '---';
                        
                        
                        html += `<tr>
                                    <td>${o.display_number}</td>
                                    <td>${o.date}</td>
                                    <td>Dhs. ${o.total}</td>
                                    <td>${custName}</td>
                                    <td>
                                        <button class="btn btn-sm btn-primary" onclick="openInvoiceByIndex(${index})">
                                            Manage
                                        </button>
                                    </td>
                                </tr>`;
                    });
                }

                $('#orderList').html(html);

                // 2. Initialize DataTable
                ordersDataTable = $('#sip-orders-table').DataTable({
                    "pageLength": 10,
                    "order": [[0, "desc"]], // Newest Order IDs first
                    "responsive": true,
                    "retrieve": true,// Extra safety to prevent re-init errors
                    "language": {
                        "search": "_INPUT_",
                        "searchPlaceholder": "Search in results..."
                    }
                });
            }
        });
    };

    // Button Listeners
    $('#sip-filter-btn').on('click', function() { loadOrders(false); });
    
    $('#btn-all-orders').on('click', function() {
        loadOrders(false);
        $(this).addClass('active').siblings().removeClass('active');
    });
    $('#btn-history').on('click', function() {
        loadOrders(true);
        $(this).addClass('active').siblings().removeClass('active');
    }); 
    
    /**
     * Opens the Invoice Modal using the array index 
     * This is the safest way to handle addresses with quotes or newlines
     */
    window.openInvoiceByIndex = function(index) {
        const o = loadedOrders[index];
        if (!o) return;

        window.currentOrderId = o.id;
        
        // Fill Modal Fields
        //$('#m_oid').text(o.id);
        $('#m_oid').text(o.display_number);
        $('#c_name').val(o.meta.name || '');
        $('#c_phone').val(o.meta.phone || '');
        $('#c_trn').val(o.meta.trn || '');
        $('#c_address').val(o.meta.address || '');
        

        // Show Modal using Bootstrap 5 Instance
        const modalEl = document.getElementById('invoiceModal');
        const modal = new bootstrap.Modal(modalEl);
        modal.show();
    };

    /**
     * Save customer data and trigger PDF generation
     */
    window.saveAndGenerate = function(event) {
        if (event) event.preventDefault();

        if (!window.currentOrderId) {
            alert("Error: No Order ID selected.");
            return;
        }

        const saveButton = $('.btn-success');
        saveButton.prop('disabled', true).text('Saving...');

        $.post(sip_settings.ajax_url, {
            action: 'sip_save_meta',
            order_id: window.currentOrderId,
            name:    $('#c_name').val(),
            phone:   $('#c_phone').val(),
            trn:     $('#c_trn').val(),
            address: $('#c_address').val()
        }, function(res) {
            saveButton.prop('disabled', false).text('Save & Generate PDF');

            if (res.success) {
                // Close Modal
                const modalEl = document.getElementById('invoiceModal');
                const modal = bootstrap.Modal.getInstance(modalEl);
                if (modal) modal.hide();

                // Open PDF in new tab
                const pdfUrl = sip_settings.site_url + '/?download_sip_pdf=' + window.currentOrderId;
                const pdfWindow = window.open(pdfUrl, '_blank');
                
                if (!pdfWindow) {
                    alert('Popup blocked! Please allow popups to view the PDF.');
                }
            } else {
                alert('Error: ' + (res.data || 'Failed to save.'));
            }
        });
    };

    /**
     * Reset Modal Fields
     */
    window.resetModalFields = function() {
        if (confirm("Are you sure you want to clear these fields?")) {
            $('#c_name, #c_phone, #c_trn, #c_address').val('');
        }
    };

    // Initialize on document ready
    $(document).ready(function() {
        window.loadOrders(false);

        // Sidebar Navigation Events
        $('#btn-all-orders').on('click', function() {
            window.loadOrders(false);
            $(this).addClass('active').siblings().removeClass('active');
        });

        $('#btn-history').on('click', function() {
            window.loadOrders(true);
            $(this).addClass('active').siblings().removeClass('active');
        });

        $('#sip-clear-filters').on('click', function() {
            // Clear the input values
            $('#searchInput').val('');
            $('#dateInput').val('');

            // Reload the table (false means not history mode)
            window.loadOrders(false);

            // Update sidebar active states
            $('#btn-all-orders').addClass('active').siblings().removeClass('active');
        });

        
    });

})(jQuery);
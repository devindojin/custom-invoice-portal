<style>
    /* load Open Sans for PDF if mPDF supports external imports */
    @import url('https://fonts.googleapis.com/css2?family=Open+Sans:wght@300;400;600;700&display=swap');

    .invoice-box { max-width: 800px; margin: auto; padding: 30px;   font-family: "Open Sans", sans-serif; color: #000;line-height:1.5; }
    .header { display: flex; justify-content: space-between; margin-bottom: 20px; }
    .logo { color: #d4a373; font-size: 28px; text-transform: uppercase; font-weight: bold;float:left }
    .invoice-details { text-align: right;float:right }
    .customer-info { margin-bottom: 40px;font-weight:300 }
    .items-table { width: 100%; line-height: inherit; text-align: left; border-collapse: collapse; }
    .items-table th { border-top: 5px solid #dadada;padding:10px 0 }
    .items-table td { padding:10px 0 }
    .totals { margin-top: 20px; text-align: right; }
    .footer { margin-top: 50px; text-align: center; font-size: 12px; }
    .barcode {
        color: #000000;
    }
    body{font-family: "Open Sans", sans-serif;font-size:16px}
    .qr-container {
        padding: 10px;
        background-color: #fff;
        display: inline-block;
    }
    p{line-height:1.5;font-weight:300;margin-bottom:15px}
</style>

<div class="invoice-box">
    

    <table class="header" width="100%" cellspacing="0" cellpadding="0">
        <tr>
            <td class="qr-container" style="text-align:left;line-height:1.5;font-weight:300">
                <img src="{{logo_base64}}" width="150" height="auto" alt="Logo"  />
            </td>
            <td class="qr-container" style="text-align:right;line-height:1.5;font-weight:300">
                <p>Tax Invoice #{{order_number}}</p>
                <p>Date: {{order_date}}</p>
            </td>
        </tr>
    </table>

    
    <table cellspacing="0" cellpadding="0" width="100%" style="margin-bottom:20px">
        <tr>
            <td style="line-height:2;font-weight:300">
                <table>
                    <tr>
                        <td><strong>CUSTOMER</strong></td>
                    </tr>
                    <tr>
                        <td height="1"></td>
                    </tr>
                    <tr>
                        <td>{{customer_name}}</td>
                    </tr>
                    <tr>
                        <td>{{customer_phone}}</td>
                    </tr>
                </table>
            </td>
            <td style="text-align:right">
                <table>
                    <tr>
                        <td><strong>PAYMENT METHOD</strong></td>
                    </tr>
                    <tr>
                        <td height="1"></td>
                    </tr>
                    <tr>
                        <td>Payment Link</td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

    <table class="items-table" cellspacing="0" cellpadding="0" style="border-bottom: 5px solid #dadada;">
        <thead>
            <tr>
                <th width="50%" style="text-align:left;padding:20px 0 0">ITEMS</th> 
                <th width="15%" style="text-align:right;padding:20px 0 0">PRICE</th>
                <th width="15%" style="text-align:center;padding:20px 0 0">QTY</th>
                <th width="15%" style="text-align:right;padding:20px 0 0">ITEM TOTAL</th>
            </tr>
        </thead>
        <tbody>
            {{item_loop}} 
            <tr>
                <td height="1"></td> 
                <td height="1"></td> 
                <td height="1"></td> 
                <td height="1"></td> 
            </tr>
        </tbody>
    </table>

    <table width="100%" style="line-height:1.5;font-weight:300" cellspacing="0" cellpadding="0">
        <tr>
            <td width="65%"></td>
            <td width="35%">
                <table width="100%" style="line-height:1.5;font-weight:300" cellspacing="0" cellpadding="0">
                    <tr><td height="20"></td><td></td><td height="20"></td></tr>
                    <tr>
                        <td width="45%" style="text-align:right">Subtotal:</td>
                        <td width="5%"></td>
                        <td width="50%" style="text-align:right">{{subtotal}}</td>
                    </tr>
                    <tr><td height="10"></td><td height="10"></td></tr>
                    <tr>
                        <td width="45%" style="text-align:right">VAT (5%):</td>
                        <td width="5%"></td>
                        <td width="50%" style="text-align:right">{{vat_amount}}</td>
                    </tr>
                    <tr><td height="10"></td><td height="10"></td></tr>
                    <tr>
                        <td width="45%" style="text-align:right"><strong>TOTAL (AED):</strong></td>
                        <td width="5%"></td>
                        <td width="50%" style="text-align:right">{{total_aed}}</td>
                    </tr>
                </table>
            </td>
        </tr>    
    </table>
    

    <div class="footer" style="line-height:1.5;font-weight:300;font-size:16px">
        <p>PAYMENT METHOD: {{payment_method}}</p> 
        <p>{{thank_you_note}}</p> 
        <strong>Lili Floral Boutique</strong><br>
         TRN No.: 104767485600003<br> 
         UAE, Dubai, AlKhawaneej 2 <br> 
         info@lilifloral.ae 
    </div>
</div>
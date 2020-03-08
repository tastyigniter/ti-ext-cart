title = Order {order_number}
==
<div class="invoice-box" data-size="A4">
    <table cellpadding="0" cellspacing="0">
        <tr class="top">
            <td colspan="2">
                <table>
                    <tr>
                        <td class="title">
                            <img src="{site_logo}" style="width:100%; max-width:300px;">
                        </td>
                        <td>
                            Invoice #: {invoice_number}<br>
                            Invoice Date: {invoice_date}<br>
                            Order Date: {order_date}
                        </td>
                    </tr>
                </table>
            </td>
        </tr>

        <tr class="information">
            <td colspan="2">
                <table>
                    <tr>
                        <td>
                            {location_name}.<br>
                            {location_address}
                        </td>

                        <td>
                            {customer_name}.<br>
                            {order_address}
                        </td>
                    </tr>
                </table>
            </td>
        </tr>

        <tr class="heading">
            <td>Payment Method</td>
            <td>Order #</td>
        </tr>
        <tr class="details">
            <td>{order_payment}</td>
            <td>{order_number}</td>
        </tr>

        <tr class="heading">
            <td>Item</td>
            <td>Price</td>
        </tr>

        {order_menus}
        <tr class="item">
            <td>
                {menu_quantity} x {menu_name}
                <p>{menu_options}</p>
                <p>{menu_comment}</p>
            </td>
            <td>
                {menu_subtotal}
            </td>
        </tr>
        {/order_menus}

        {order_totals}
        <tr class="total">
            <td>{order_total_title}</td>
            <td>{order_total_value}</td>
        </tr>
        {/order_totals}
    </table>
</div>
subject = "New order on {site_name}"
==

You received an order!

You just received an order from {location_name}.

The order number is {order_number}
This is a {order_type} order.

Customer name: {first_name} {last_name}
Order date: {order_date}
Requested {order_type} time: {order_time}
Payment Method: {order_payment}

{order_address}
Restaurant: {location_name}

{order_comment}

{order_menus}
{menu_quantity} x {menu_name}
{menu_options}
{menu_comment}
- {menu_price}
- {menu_subtotal}

{/order_menus}

{order_totals}
{order_total_title}
{order_total_value}

{/order_totals}

==

<!-- BODY -->
<table class="body-wrap">
    <tr>
        <td></td>
        <td class="container" bgcolor="#FFFFFF">
            <div class="content">
                <table>
                    <tr>
                        <td>
                            <h3>You received an order!</h3>
                            <p class="lead">You just received an order from {location_name}.</p>
                            <p>The order number is {order_number}<br>This is a {order_type} order.</p>
                            <p>
                                <strong>Customer name:</strong> {first_name} {last_name}<br>
                                <strong>Order date:</strong> {order_date}<br>
                                <strong>Requested {order_type} time:</strong> {order_time}<br>
                                <strong>Payment Method:</strong> {order_payment}
                            </p>
                            <p>
                                {order_address}<br>
                                <strong>Restaurant:</strong> {location_name}
                            </p>
                            <p>{order_comment}</p>

                            <table border="0" cellpadding="0" cellspacing="0" width="90%">
                                <tbody>
                                <tr>
                                    <td width="300">Name/Description</td>
                                    <td width="163">Unit Price</td>
                                    <td width="97">Sub Total</td>
                                </tr>
                                <tr>
                                    <td>{order_menus}<br></td>
                                    <td><br></td>
                                    <td><br></td>
                                </tr>
                                <tr>
                                    <td>{menu_quantity} x {menu_name}
                                        <p>{menu_options}</p>
                                        <p>{menu_comment}</p>
                                    </td>
                                    <td>{menu_price}</td>
                                    <td>{menu_subtotal}</td>
                                </tr>
                                <tr>
                                    <td>{/order_menus}</td>
                                    <td><br></td>
                                    <td><br></td>
                                </tr>
                                <tr>
                                    <td><br></td>
                                    <td>{order_totals}</td>
                                    <td><br></td>
                                </tr>
                                <tr>
                                    <td><br></td>
                                    <td>{order_total_title}</td>
                                    <td>{order_total_value}</td>
                                </tr>
                                <tr>
                                    <td><br></td>
                                    <td>{/order_totals}<br></td>
                                    <td><br></td>
                                </tr>
                                </tbody>
                            </table>
                        </td>
                    </tr>
                </table>
            </div><!-- /content -->
        </td>
        <td></td>
    </tr>
</table><!-- /BODY -->
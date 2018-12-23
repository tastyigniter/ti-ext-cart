subject = "{site_name} order confirmation - {order_number}"
==

Thank you for your order!

Hi, {first_name} {last_name}

Your order has been received and will be with you shortly.

To view your order progress, use the URL below:
{order_view_url}

Your order number is {order_number}
This is a {order_type} order.

Order date: {order_date}
Requested {order_type} time: {order_time}
Payment Method: {order_payment}

{order_address}
Restaurant: {location_name}

{order_comment}

{order_menus}
{menu_quantity} x {menu_name}
{menu_options}
- {menu_price}
- {menu_subtotal}
{menu_comment}

{/order_menus}

{order_totals}
{order_total_title}
{order_total_value}

{/order_totals}

==

<!-- HEADER -->
<table class="head-wrap" bgcolor="#D7D7DE">
    <tr>
        <td></td>
        <td class="header container">
            <div class="content">
                <table bgcolor="#D7D7DE">
                    <tr>
                        <td><img src="{site_logo}"/></td>
                        <td align="right"><h6 class="collapse">{site_name}</h6></td>
                    </tr>
                </table>
            </div>
        </td>
        <td></td>
    </tr>
</table><!-- /HEADER -->
<!-- BODY -->
<table class="body-wrap">
    <tr>
        <td></td>
        <td class="container" bgcolor="#FFFFFF">
            <div class="content">
                <table>
                    <tr>
                        <td>
                            <h3>Thank you for your order!</h3>
                            <p class="lead">Hi, {first_name} {last_name}</p>
                            <p>Your order has been received and will be with you shortly.
                                <a href="{order_view_url}">Click here</a> to view your order progress.
                            </p>
                            <p>Your order number is {order_number}<br>This is a {order_type} order.</p>
                            <p>
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
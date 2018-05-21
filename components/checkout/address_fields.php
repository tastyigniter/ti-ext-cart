<div>
    <div
        class="btn-group btn-group-md col-xs-12"
        data-toggle="buttons"
    >
        <?php if (count($customerAddresses = $order->listCustomerAddresses())) { ?>
            <?php $index = 0;
            foreach ($customerAddresses as $address) { ?>
                <?php
                $isDefaultAddress = ($order->address_id == $address->address_id);
                ?>
                <label
                    class="btn <?= $isDefaultAddress ? 'btn-primary active' : 'btn-default'; ?>"
                    data-btn="btn-primary"
                >
                <span
                    class="edit-address pull-right"
                    data-form="#address-form-<?= $index; ?>"
                ><?= lang('sampoyigi.cart::default.checkout.text_edit'); ?></span>
                    <input
                        type="radio"
                        name="address_id"
                        value="<?= $address->address_id; ?>"
                        <?= $isDefaultAddress ? 'checked="checked"' : ''; ?> />
                    <address class="text-left"><?= $address->formatted; ?></address>
                </label>
                <?php $index++; ?>
            <?php } ?>
        <?php } ?>
    </div>
    <div class="col-xs-12">
        <?= form_error('address_id', '<span class="text-danger">', '</span>'); ?>
    </div>
</div>

<div>
    <input
        type="hidden"
        name="address[address_id]"
        value="<?= set_value('address.address_id', $order->address['address_id']); ?>"
    >
    <div class="row">
        <div class="col-sm-6">
            <div class="form-group">
                <label for=""><?= lang('sampoyigi.cart::default.checkout.label_address_1'); ?></label>
                <input
                    type="text"
                    name="address[address_1]"
                    class="form-control"
                    value="<?= set_value('address[address_1]', $order->address['address_1']); ?>"/>
                <?= form_error('address.address_1', '<span class="text-danger">', '</span>'); ?>
            </div>
        </div>
        <div class="col-sm-6">
            <div class="form-group">
                <label for=""><?= lang('sampoyigi.cart::default.checkout.label_address_2'); ?></label>
                <input
                    type="text"
                    name="address[address_2]"
                    class="form-control"
                    value="<?= set_value('address[address_2]', $order->address['address_2']); ?>"/>
                <?= form_error('address.address_2', '<span class="text-danger">', '</span>'); ?>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-sm-4">
            <div class="form-group">
                <label for=""><?= lang('sampoyigi.cart::default.checkout.label_city'); ?></label>
                <input
                    type="text"
                    name="address[city]"
                    class="form-control"
                    value="<?= set_value('address[city]', $order->address['city']); ?>"/>
                <?= form_error('address.city', '<span class="text-danger">', '</span>'); ?>
            </div>
        </div>
        <div class="col-sm-4">
            <div class="form-group">
                <label for=""><?= lang('sampoyigi.cart::default.checkout.label_state'); ?></label>
                <input
                    type="text"
                    name="address[state]"
                    class="form-control"
                    value="<?= set_value('address[state]', $order->address['state']); ?>"/>
                <?= form_error('address.state', '<span class="text-danger">', '</span>'); ?>
            </div>
        </div>
        <div class="col-sm-4">
            <div class="form-group">
                <label for=""><?= lang('sampoyigi.cart::default.checkout.label_postcode'); ?></label>
                <input
                    type="text"
                    name="address[postcode]"
                    class="form-control"
                    value="<?= set_value('address[postcode]', $order->address['postcode']); ?>"/>
                <?= form_error('address.postcode', '<span class="text-danger">', '</span>'); ?>
            </div>
        </div>
    </div>
    <div class="form-group">
        <label for=""><?= lang('sampoyigi.cart::default.checkout.label_country'); ?></label>
        <select
            name="address[country_id]"
            class="form-control"
        >
            <?php foreach (countries('country_name') as $key => $value) { ?>
                <option
                    value="<?= $key; ?>"
                    <?= ($key == $order->address['country_id']) ? 'selected="selected"' : '' ?>
                ><?= e($value); ?></option>
            <?php } ?>
        </select>
        <?= form_error('address.country_id', '<span class="text-danger">', '</span>'); ?>
    </div>
</div>

<div class="row">
    <div class="col-sm-6">
        <div class="form-group">
            <label for="first-name"><?= lang('igniter.cart::default.checkout.label_first_name'); ?></label>
            <input
                type="text"
                name="first_name"
                id="first-name"
                class="form-control"
                value="<?= set_value('first_name', $order->first_name); ?>"/>
            <?= form_error('first_name', '<span class="text-danger">', '</span>'); ?>
        </div>
    </div>
    <div class="col-sm-6">
        <div class="form-group">
            <label for="last-name"><?= lang('igniter.cart::default.checkout.label_last_name'); ?></label>
            <input
                type="text"
                name="last_name"
                id="last-name"
                class="form-control"
                value="<?= set_value('last_name', $order->last_name); ?>"/>
            <?= form_error('last_name', '<span class="text-danger">', '</span>'); ?>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-sm-6">
        <div class="form-group">
            <label for="email"><?= lang('igniter.cart::default.checkout.label_email'); ?></label>
            <input
                type="text"
                name="email"
                id="email"
                class="form-control"
                value="<?= set_value('email', $order->email); ?>"
                <?= $customer ? 'disabled' : ''; ?> />
            <?= form_error('email', '<span class="text-danger">', '</span>'); ?>
        </div>
    </div>
    <div class="col-sm-6">
        <div class="form-group">
            <label for="telephone"><?= lang('igniter.cart::default.checkout.label_telephone'); ?></label>
            <input
                type="text"
                name="telephone"
                id="telephone"
                class="form-control"
                value="<?= set_value('telephone', $order->telephone); ?>"/>
            <?= form_error('telephone', '<span class="text-danger">', '</span>'); ?>
        </div>
    </div>
</div>
